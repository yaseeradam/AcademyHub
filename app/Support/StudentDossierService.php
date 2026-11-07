<?php

namespace App\Support;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\PsychomotorScore;
use App\Models\ResultPublication;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Support\TenantSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class StudentDossierService
{
    protected array $classScoresCache = [];
    protected array $studentCountCache = [];

    /**
     * Build the complete multi-term Academic Dossier / Report Booklet data for a student.
     *
     * @param Student $student
     * @param string $session (e.g. "2026/2027")
     * @param array $optionsOverrides
     * @return array
     */
    public function buildDossier(Student $student, string $session, array $optionsOverrides = []): array
    {
        $student->load(['schoolClass', 'section']);
        $displayClassId = (int) $student->class_id;
        $displaySectionId = (int) ($student->section_id ?? 0);

        // Historical Class Lookup: Check if student was recorded in an earlier class for this session
        $scoreClassId = Score::query()
            ->where('student_id', $student->id)
            ->where('session', $session)
            ->value('class_id');
        $classId = (int) ($scoreClassId ?: $displayClassId);
        $sectionId = $displaySectionId;

        // Get all subjects configured or scored for this class
        $subjects = SchoolClass::allSubjectsForClass($classId);
        $subjectIds = $subjects->pluck('id');
        $subjectCount = max(1, (int) $subjectIds->count());

        // Discover active/reached terms for this session
        $availableTermsMap = AcademicTerm::availableTermsForSession($session);
        $maxAvailableTerm = !empty($availableTermsMap) ? max(array_keys($availableTermsMap)) : 3;

        // Publication filter: if only_published requested (e.g. by parent/student)
        $onlyPublished = (bool) ($optionsOverrides['only_published'] ?? false);
        $publishedTerms = $onlyPublished ? ResultPublication::publishedTermsForClass($classId, $session) : [1, 2, 3];

        // Fetch all scores for this student across the entire session
        $studentScores = Score::query()
            ->with('subject')
            ->where('student_id', $student->id)
            ->where('session', $session)
            ->whereIn('subject_id', $subjectIds)
            ->get();

        // Elective Subject Trimming:
        // If student has scores in the session, exclude class subjects where the student has ZERO scores in all terms
        $scoredSubjIds = $studentScores->pluck('subject_id')->unique();
        if ($scoredSubjIds->isNotEmpty()) {
            $subjects = $subjects->filter(fn(Subject $s) => $scoredSubjIds->contains($s->id))->values();
            $subjectIds = $subjects->pluck('id');
            $subjectCount = max(1, (int) $subjectIds->count());
        }

        $scoresByTerm = $studentScores->groupBy('term')->map(fn($rows) => $rows->keyBy('subject_id'));

        // Determine which terms have recorded scores for this student
        $termsWithData = [];
        for ($t = 1; $t <= 3; $t++) {
            if ($scoresByTerm->has($t) && $scoresByTerm->get($t)->isNotEmpty()) {
                $termsWithData[] = $t;
            }
        }
        if (empty($termsWithData)) {
            $termsWithData = [1];
        }

        // Fetch all class scores for all active students in this class for the session
        $allClassScores = $this->getClassScoresForSession($classId, $session, $subjectIds);

        // Calculate per-subject statistics (T1, T2, T3, and Annual Cumulative)
        $subjectRows = $subjects->map(function (Subject $subject) use ($scoresByTerm, $allClassScores, $student, $maxAvailableTerm, $onlyPublished, $publishedTerms) {
            $subjId = $subject->id;

            $termDetails = [];
            $annualTotalsList = [];

            for ($t = 1; $t <= 3; $t++) {
                $isPublishedTerm = in_array($t, $publishedTerms, true);
                $termScores = ($onlyPublished && !$isPublishedTerm) ? null : $scoresByTerm->get($t);
                /** @var Score|null $score */
                $score = $termScores ? $termScores->get($subjId) : null;
                $isPending = ($t > $maxAvailableTerm && !$score) || ($onlyPublished && !$isPublishedTerm);

                if ($score && $score->total !== null) {
                    $annualTotalsList[] = (float) $score->total;
                }

                // Class average for this subject in term $t
                $tClassScores = $allClassScores->where('subject_id', $subjId)->where('term', $t);
                $tClassAvg = $tClassScores->isNotEmpty() ? round($tClassScores->avg('total'), 1) : null;

                // Student rank in class for this subject in term $t
                $tRank = null;
                if ($score && $tClassScores->isNotEmpty()) {
                    $sorted = $tClassScores->sortByDesc('total')->values();
                    $r = 1; $lastVal = null;
                    foreach ($sorted as $idx => $row) {
                        if ($lastVal !== null && $row->total !== $lastVal) {
                            $r = $idx + 1;
                        }
                        if ((int) $row->student_id === (int) $student->id) {
                            $tRank = $r;
                            break;
                        }
                        $lastVal = $row->total;
                    }
                }

                $termDetails[$t] = [
                    'ca1'        => $score?->ca1 ?? null,
                    'ca2'        => $score?->ca2 ?? null,
                    'exam'       => $score?->exam ?? null,
                    'total'      => $score?->total ?? null,
                    'grade'      => $score?->grade ?? ($score ? Score::gradeForTotal((int) $score->total) : null),
                    'class_avg'  => $tClassAvg,
                    'position'   => $tRank,
                    'remarks'    => $score?->remarks ?? null,
                    'is_pending' => $isPending,
                ];
            }

            $countActive = count($annualTotalsList);
            $annualSum = $countActive > 0 ? array_sum($annualTotalsList) : null;
            $annualAvg = $countActive > 0 ? round($annualSum / $countActive, 1) : null;
            $annualGrade = $annualAvg !== null ? Score::gradeForTotal((int) round($annualAvg)) : null;

            // Class annual average and student annual rank for this subject
            $classSubjAnnual = $allClassScores->where('subject_id', $subjId)->groupBy('student_id')->map(fn($rows) => $rows->avg('total'));
            $classAnnualSubjAvg = $classSubjAnnual->isNotEmpty() ? round($classSubjAnnual->avg(), 1) : null;

            $annualSubjRank = null;
            if ($annualAvg !== null && $classSubjAnnual->isNotEmpty()) {
                $sortedAnnual = $classSubjAnnual->sortDesc()->values();
                $r = 1; $lastVal = null;
                foreach ($sortedAnnual as $idx => $val) {
                    if ($lastVal !== null && $val !== $lastVal) {
                        $r = $idx + 1;
                    }
                    if (abs($val - $annualAvg) < 0.001) {
                        $annualSubjRank = $r;
                        break;
                    }
                    $lastVal = $val;
                }
            }

            return [
                'subject'           => $subject,
                'terms'             => $termDetails,
                'annual_total'      => $annualSum !== null ? (int) round($annualSum) : null,
                'annual_average'    => $annualAvg,
                'annual_grade'      => $annualGrade,
                'annual_rank'       => $annualSubjRank,
                'class_annual_avg'  => $classAnnualSubjAvg,
            ];
        });

        // Term-by-Term Session Summaries
        $termSummaries = [];
        $studentTermAverages = [];
        for ($t = 1; $t <= 3; $t++) {
            $isPublishedTerm = in_array($t, $publishedTerms, true);
            $tScores = ($onlyPublished && !$isPublishedTerm) ? null : $scoresByTerm->get($t);
            $hasData = ($tScores && $tScores->isNotEmpty());
            $tGrandTotal = $hasData ? (int) $tScores->sum('total') : null;
            $tCount = $hasData ? max(1, $tScores->count()) : 1;
            $tAvg = $hasData ? round($tGrandTotal / $tCount, 2) : null;

            if ($tAvg !== null) {
                $studentTermAverages[] = $tAvg;
            }

            // Calculate term overall position in class
            $tClassScores = $allClassScores->where('term', $t);
            $tPosition = null;
            $tClassAverage = null;
            if ($hasData && $tClassScores->isNotEmpty()) {
                $studentTotals = $tClassScores->groupBy('student_id')->map(fn($r) => (int) $r->sum('total'));
                $studentAvgs = $tClassScores->groupBy('student_id')->map(fn($r) => $r->sum('total') / max(1, $r->count()));
                $tClassAverage = round((float) $studentAvgs->avg(), 2);

                $sorted = $studentTotals->sortDesc()->values();
                $rank = 1; $last = null;
                foreach ($sorted as $idx => $tot) {
                    if ($last !== null && $tot !== $last) {
                        $rank = $idx + 1; // Standard competition rank (1, 1, 3)
                    }
                    if ((int) $sorted->keys()[$idx] === (int) $student->id || (int) $studentTotals->search($tot) === (int) $student->id) {
                        $tPosition = $rank;
                    }
                    $last = $tot;
                }
            }

            $termSummaries[$t] = [
                'has_data'      => $hasData,
                'grand_total'   => $tGrandTotal,
                'average'       => $tAvg,
                'position'      => $tPosition,
                'class_average' => $tClassAverage,
                'is_pending'    => ($t > $maxAvailableTerm && !$hasData) || ($onlyPublished && !$isPublishedTerm),
            ];
        }

        // Overall Annual Cumulative Average & Rank
        $termsCount = count($studentTermAverages);
        $cumulativeAnnualAverage = $termsCount > 0 ? round(array_sum($studentTermAverages) / $termsCount, 2) : 0.0;
        $annualGrandTotal = (int) $subjectRows->sum(fn($r) => (int) ($r['annual_total'] ?? 0));

        // Annual Class Rank
        $classStudentAnnualAverages = $allClassScores->groupBy('student_id')->map(function ($scores) {
            $byTerm = $scores->groupBy('term');
            $tAvgs = [];
            foreach ($byTerm as $tRows) {
                $tAvgs[] = $tRows->sum('total') / max(1, $tRows->count());
            }
            return count($tAvgs) > 0 ? array_sum($tAvgs) / count($tAvgs) : 0.0;
        });

        $annualPosition = 1;
        if ($classStudentAnnualAverages->isNotEmpty() && $cumulativeAnnualAverage > 0) {
            $sortedAnnual = $classStudentAnnualAverages->sortDesc()->values();
            $rank = 1; $last = null;
            foreach ($sortedAnnual as $idx => $avg) {
                if ($last !== null && abs($avg - $last) > 0.001) {
                    $rank = $idx + 1; // Standard competition rank (1, 1, 3)
                }
                if (abs($avg - $cumulativeAnnualAverage) < 0.001) {
                    $annualPosition = $rank;
                    break;
                }
                $last = $avg;
            }
        }

        $annualClassAverage = $classStudentAnnualAverages->isNotEmpty()
            ? round((float) $classStudentAnnualAverages->avg(), 2)
            : 0.0;

        // Total Students in Class
        $totalStudents = $this->getStudentCount($classId);

        // Longitudinal Attendance Tracking across Term 1, 2, 3
        $attendanceTimeline = $this->buildAttendanceTimeline($student, $session);

        // Multi-term Psychomotor & Affective Traits Evolution
        $psychomotorTimeline = $this->buildPsychomotorTimeline($student, $session);

        // Promotion & Progression Engine (Checks core subjects: English & Mathematics)
        $promotion = $this->determinePromotionStatus($student, $cumulativeAnnualAverage, $maxAvailableTerm, $subjectRows);

        // School Settings & Branding
        $rawSettings = $this->settings();
        $rcOptions = [
            'show_position'            => isset($optionsOverrides['show_position']) ? (bool) $optionsOverrides['show_position'] : $this->settingBool('rc_show_position', true),
            'show_attendance'          => isset($optionsOverrides['show_attendance']) ? (bool) $optionsOverrides['show_attendance'] : $this->settingBool('rc_show_attendance', true),
            'show_grading_key'         => isset($optionsOverrides['show_grading_key']) ? (bool) $optionsOverrides['show_grading_key'] : $this->settingBool('rc_show_grading_key', true),
            'show_class_average'       => isset($optionsOverrides['show_class_average']) ? (bool) $optionsOverrides['show_class_average'] : $this->settingBool('rc_show_class_average', true),
            'show_watermark'           => isset($optionsOverrides['show_watermark']) ? (bool) $optionsOverrides['show_watermark'] : $this->settingBool('rc_show_watermark', true),
            'show_next_term_date'      => isset($optionsOverrides['show_next_term_date']) ? (bool) $optionsOverrides['show_next_term_date'] : $this->settingBool('rc_show_next_term_date', true),
            'show_teacher_remarks'     => isset($optionsOverrides['show_teacher_remarks']) ? (bool) $optionsOverrides['show_teacher_remarks'] : $this->settingBool('rc_show_teacher_remarks', true),
            'show_principal_remarks'   => isset($optionsOverrides['show_principal_remarks']) ? (bool) $optionsOverrides['show_principal_remarks'] : $this->settingBool('rc_show_principal_remarks', true),
            'show_psychomotor'         => isset($optionsOverrides['show_psychomotor']) ? (bool) $optionsOverrides['show_psychomotor'] : $this->settingBool('rc_show_psychomotor', true),
            'show_school_fees'         => isset($optionsOverrides['show_school_fees']) ? (bool) $optionsOverrides['show_school_fees'] : $this->settingBool('rc_show_school_fees', false),
            'show_signatures'          => isset($optionsOverrides['show_signatures']) ? (bool) $optionsOverrides['show_signatures'] : $this->settingBool('rc_show_signatures', true),
            'show_qr_code'             => isset($optionsOverrides['show_qr_code']) ? (bool) $optionsOverrides['show_qr_code'] : $this->settingBool('rc_show_qr_code', true),
        ];

        // Annual Teacher & Principal Remarks
        $principalRemarks = $this->generateAnnualPrincipalRemarks($cumulativeAnnualAverage, $annualPosition, $totalStudents, $promotion['status']);
        $teacherRemarks = $this->generateAnnualTeacherRemarks($cumulativeAnnualAverage, $annualPosition, $totalStudents);

        // Next Term / Session Resumption Date
        $nextTermDate = $rawSettings['rc_next_term_date'] ?? null;
        if (!$nextTermDate) {
            $nextTerm = AcademicTerm::query()
                ->where('is_active', false)
                ->where('starts_on', '>', now()->toDateString())
                ->orderBy('starts_on')
                ->first();
            $nextTermDate = $nextTerm?->starts_on?->format('d/m/Y');
        }

        // Digital Signatures
        $principalSig = $rawSettings['rc_principal_signature_image'] ?? null;
        $teacherSig = $rawSettings['rc_teacher_signature_image'] ?? null;
        $signatureImages = [
            'principal' => $principalSig ? public_path('uploads/' . str_replace('\\', '/', $principalSig)) : null,
            'teacher'   => $teacherSig   ? public_path('uploads/' . str_replace('\\', '/', $teacherSig))   : null,
        ];

        $verifyUrl = url('/results/verify/' . urlencode($student->admission_number ?? 'ADM001'));
        $qrCodeUri = $this->generateQrCodeDataUri($verifyUrl);

        return [
            'student'                   => $student,
            'session'                   => $session,
            'maxAvailableTerm'          => $maxAvailableTerm,
            'subjectRows'               => $subjectRows,
            'termSummaries'             => $termSummaries,
            'annualGrandTotal'          => $annualGrandTotal,
            'cumulativeAnnualAverage'   => $cumulativeAnnualAverage,
            'annualPosition'            => $annualPosition,
            'annualClassAverage'        => $annualClassAverage,
            'totalStudents'             => $totalStudents,
            'attendanceTimeline'        => $attendanceTimeline,
            'psychomotorTimeline'       => $psychomotorTimeline,
            'promotion'                 => $promotion,
            'principalRemarks'          => $principalRemarks,
            'teacherRemarks'            => $teacherRemarks,
            'schoolName'                => $rawSettings['school_name'] ?? config('academyhub.school_name', config('app.name', 'AcademyHub')),
            'schoolLogo'                => $rawSettings['school_logo'] ?? config('academyhub.school_logo'),
            'schoolAddress'             => $rawSettings['school_address'] ?? config('academyhub.school_address', ''),
            'schoolPhone'               => $rawSettings['school_phone'] ?? config('academyhub.school_phone', ''),
            'schoolMotto'               => $rawSettings['school_motto'] ?? config('academyhub.school_motto', 'Excellence and Integrity in Education'),
            'principalName'             => $rawSettings['rc_principal_name'] ?? config('academyhub.rc_principal_name'),
            'principalTitle'            => $rawSettings['rc_principal_title'] ?? config('academyhub.rc_principal_title', 'Principal'),
            'nextTermDate'              => $nextTermDate,
            'signatureImages'           => $signatureImages,
            'qrCodeUri'                 => $qrCodeUri,
            'rcOptions'                 => $rcOptions,
        ];
    }

    /**
     * Cache and retrieve all class scores for the full academic session.
     */
    protected function getClassScoresForSession(int $classId, string $session, Collection $subjectIds): Collection
    {
        $tenantId = TenantSettings::tenantId() ?? 0;
        $cacheKey = "session-scores-{$tenantId}-{$classId}-{$session}";
        if (isset($this->classScoresCache[$cacheKey])) {
            return $this->classScoresCache[$cacheKey];
        }

        return $this->classScoresCache[$cacheKey] = Score::query()
            ->where('class_id', $classId)
            ->where('session', $session)
            ->whereIn('subject_id', $subjectIds)
            ->get(['subject_id', 'student_id', 'term', 'total']);
    }

    protected function getStudentCount(int $classId): int
    {
        if (isset($this->studentCountCache[$classId])) {
            return $this->studentCountCache[$classId];
        }

        return $this->studentCountCache[$classId] = Student::query()
            ->where('class_id', $classId)
            ->where('status', 'Active')
            ->count();
    }

    /**
     * Build attendance timeline across Term 1, 2, 3 and grand totals.
     */
    protected function buildAttendanceTimeline(Student $student, string $session): array
    {
        $sectionId = (int) ($student->section_id ?? 0);
        $timeline = [];
        $totalOpened = 0;
        $totalPresent = 0;
        $totalAbsent = 0;

        for ($t = 1; $t <= 3; $t++) {
            $sheetIds = AttendanceSheet::query()
                ->where('class_id', $student->class_id)
                ->when($sectionId > 0, fn($q) => $q->where('section_id', $sectionId), fn($q) => $q->whereNull('section_id'))
                ->where('term', $t)
                ->where('session', $session)
                ->pluck('id');

            $opened = $sheetIds->count();
            if ($opened > 0) {
                $marks = AttendanceMark::query()
                    ->whereIn('sheet_id', $sheetIds)
                    ->where('student_id', $student->id)
                    ->get();

                $present = $marks->whereIn('status', ['Present', 'Late', 'Excused'])->count();
                $absent = $marks->where('status', 'Absent')->count();
            } else {
                $present = 0;
                $absent = 0;
            }

            $rate = $opened > 0 ? round(($present / $opened) * 100, 1) : null;

            $timeline[$t] = [
                'opened'   => $opened > 0 ? $opened : null,
                'present'  => $opened > 0 ? $present : null,
                'absent'   => $opened > 0 ? $absent : null,
                'rate'     => $rate,
            ];

            $totalOpened += $opened;
            $totalPresent += $present;
            $totalAbsent += $absent;
        }

        $annualRate = $totalOpened > 0 ? round(($totalPresent / $totalOpened) * 100, 1) : null;

        return [
            'terms'         => $timeline,
            'total_opened'  => $totalOpened > 0 ? $totalOpened : null,
            'total_present' => $totalPresent > 0 ? $totalPresent : null,
            'total_absent'  => $totalAbsent > 0 ? $totalAbsent : null,
            'annual_rate'   => $annualRate,
        ];
    }

    /**
     * Build longitudinal development of psychomotor and affective traits.
     */
    protected function buildPsychomotorTimeline(Student $student, string $session): array
    {
        $allScores = PsychomotorScore::query()
            ->where('student_id', $student->id)
            ->where('session', $session)
            ->get()
            ->keyBy('term');

        $traitLabels = [
            'handwriting'              => 'Handwriting / Penmanship',
            'verbal_fluency'           => 'Verbal Fluency & Communication',
            'games_sports'             => 'Games & Physical Sports',
            'craft_drawing'            => 'Crafts, Creative & Visual Arts',
            'musical_skills'           => 'Musical & Rhythmic Skills',
            'punctuality'              => 'Punctuality & Regularity',
            'neatness'                 => 'Neatness & Personal Hygiene',
            'politeness_courtesy'      => 'Politeness, Courtesy & Respect',
            'honesty'                  => 'Honesty & Integrity',
            'self_control'             => 'Self-Control & Emotional Calmness',
            'attentiveness'            => 'Attentiveness & Focus in Class',
            'relationship_with_others' => 'Peer Relations & Teamwork',
        ];

        $matrix = [];
        foreach ($traitLabels as $key => $label) {
            $matrix[$key] = [
                'label'  => $label,
                'term_1' => $allScores->get(1)?->traits[$key] ?? '—',
                'term_2' => $allScores->get(2)?->traits[$key] ?? '—',
                'term_3' => $allScores->get(3)?->traits[$key] ?? '—',
            ];
        }

        return $matrix;
    }

    /**
     * Determine promotion decision and next class progression.
     */
    protected function determinePromotionStatus(Student $student, float $cumulativeAverage, int $maxAvailableTerm, mixed $subjectRows = null): array
    {
        $class = $student->schoolClass;
        $passThreshold = (float) ($this->settings()['rc_promotion_pass_mark'] ?? 50.0);

        // Find next class by level
        $nextClass = null;
        if ($class && $class->level !== null) {
            $nextClass = SchoolClass::query()
                ->where('level', '>', $class->level)
                ->orderBy('level')
                ->first();
        }

        // If only Term 1 or Term 2 has been reached, progression is In Progress
        if ($maxAvailableTerm < 3) {
            $trackStatus = $cumulativeAverage >= $passThreshold ? 'On Track for Promotion' : 'Needs Academic Improvement';
            return [
                'is_final'      => false,
                'status'        => 'IN PROGRESS',
                'badge'         => 'progress',
                'message'       => "Academic year currently in progress (Term {$maxAvailableTerm}). Student is {$trackStatus}.",
                'next_class'    => $nextClass?->name,
            ];
        }

        // Core subject check (English and Mathematics prerequisite rule)
        $failedCoreSubjects = [];
        if ($subjectRows) {
            foreach ($subjectRows as $row) {
                $sName = $row['subject']->name ?? '';
                $isCore = preg_match('/(english|mathematics|maths)/i', $sName);
                if ($isCore && $row['annual_average'] !== null && $row['annual_average'] < $passThreshold) {
                    $failedCoreSubjects[] = $sName;
                }
            }
        }

        // Full year promotion determination
        if ($cumulativeAverage >= $passThreshold) {
            // Check if student passed overall but failed core prerequisites
            if (!empty($failedCoreSubjects)) {
                $target = $nextClass ? strtoupper($nextClass->name) : 'NEXT LEVEL';
                $deficiency = implode(' & ', $failedCoreSubjects);
                return [
                    'is_final'   => true,
                    'status'     => 'PROMOTED ON TRIAL',
                    'badge'      => 'warning',
                    'message'    => "PROMOTED ON TRIAL TO {$target} (Prerequisite deficiency in {$deficiency})",
                    'next_class' => $nextClass?->name,
                ];
            }

            if ($nextClass) {
                return [
                    'is_final'   => true,
                    'status'     => 'PROMOTED',
                    'badge'      => 'success',
                    'message'    => "PROMOTED TO " . strtoupper($nextClass->name),
                    'next_class' => $nextClass->name,
                ];
            }

            return [
                'is_final'   => true,
                'status'     => 'GRADUATED',
                'badge'      => 'success',
                'message'    => "GRADUATED / COMPLETED PROGRAM",
                'next_class' => 'Graduated',
            ];
        }

        if ($cumulativeAverage >= ($passThreshold - 10.0)) {
            $target = $nextClass ? strtoupper($nextClass->name) : 'NEXT LEVEL';
            return [
                'is_final'   => true,
                'status'     => 'PROMOTED ON TRIAL',
                'badge'      => 'warning',
                'message'    => "PROMOTED ON TRIAL TO {$target}",
                'next_class' => $nextClass?->name,
            ];
        }

        $currentClassName = $class ? strtoupper($class->name) : 'CURRENT CLASS';
        return [
            'is_final'   => true,
            'status'     => 'REPEAT',
            'badge'      => 'danger',
            'message'    => "TO REPEAT {$currentClassName}",
            'next_class' => $class?->name,
        ];
    }

    protected function generateAnnualPrincipalRemarks(float $average, int $position, int $totalStudents, string $status): string
    {
        if ($status === 'PROMOTED' || $status === 'GRADUATED') {
            if ($position === 1) {
                return "Distinguished and exceptional annual performance! First position out of {$totalStudents} students with an outstanding {$average}% cumulative average. Keep shining brightly.";
            }
            if ($average >= 70) {
                return "An impressive academic year characterized by brilliance, high dedication, and commendable character. Highly commended.";
            }
            return "Good academic progress shown throughout the academic session. Promoted to the next class with full confidence in further growth.";
        }

        if ($status === 'PROMOTED ON TRIAL') {
            return "Promoted on trial. Has demonstrated latent capabilities but requires disciplined revision and parental supervision during holidays.";
        }

        if ($status === 'IN PROGRESS') {
            return "Steady progress made this session. Encouraged to maintain consistency in continuous assessment and terminal examinations.";
        }

        return "Sub-threshold annual performance. Repeating the class is recommended to build a solid foundational understanding.";
    }

    protected function generateAnnualTeacherRemarks(float $average, int $position, int $totalStudents): string
    {
        if ($average >= 70) {
            return "A wonderfully diligent, studious, and exemplary pupil. Outstanding year-long achievement across all subjects.";
        }
        if ($average >= 60) {
            return "Very good work this academic session. Consistently engaged, respectful, and hardworking in class.";
        }
        if ($average >= 50) {
            return "Satisfactory academic performance over the year. Capable of higher distinctions with extra personal study hours.";
        }
        if ($average >= 40) {
            return "Fair performance. Greater concentration and strict homework routine will unlock better potential.";
        }
        return "Struggled with core foundational concepts this year. Close remedial guidance is urgently advised.";
    }

    protected function settings(): array
    {
        $path = TenantSettings::settingsPath();

        return File::exists($path)
            ? (json_decode(File::get($path), true) ?? [])
            : [];
    }

    protected function settingBool(string $key, bool $default = false): bool
    {
        $settings = $this->settings();

        if (!array_key_exists($key, $settings)) {
            return $default;
        }

        $value = $settings[$key];

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected function generateQrCodeDataUri(string $data): string
    {
        try {
            return \App\Support\QrCodeGenerator::generatePng($data);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Compile the complete Student Report Book HTML:
     * 1. Royal Academic Cover Page with student photo and bio
     * 2. Full-page Report Cards for each available term formatted in the school's active template (elegant, riverdale, etc.)
     * 3. Multi-Term Cumulative Dossier Ledger & Verdict
     */
    public function compileBookletHtml(Student $student, string $session, array $optionsOverrides = [], bool $isParentOrStudent = false): string
    {
        // 1. Ensure tenant context and settings are loaded
        $tenantId = $student->tenant_id ?? TenantSettings::tenantId();
        if ($tenantId && (!app()->bound('currentTenant') || (app('currentTenant')->id ?? null) !== $tenantId)) {
            $tenant = \App\Models\Tenant::find($tenantId);
            if ($tenant) {
                app()->instance('currentTenant', $tenant);
                TenantSettings::loadToConfig();
            }
        }

        // 2. Build multi-term dossier data
        $dossierData = $this->buildDossier($student, $session, $optionsOverrides);

        // 3. Determine selected report card template
        $template = $optionsOverrides['template']
            ?? TenantSettings::getForTenant('report_card_template', $tenantId, config('academyhub.report_card_template', 'elegant'));

        // 4. Determine terms to render in this book
        $scoresByTerm = Score::query()
            ->where('student_id', $student->id)
            ->where('session', $session)
            ->select('term')
            ->distinct()
            ->pluck('term')
            ->toArray();

        $termsToRender = !empty($scoresByTerm) ? $scoresByTerm : [1];
        sort($termsToRender);

        if ($isParentOrStudent) {
            $publishedTerms = ResultPublication::publishedTermsForClass($student->class_id, $session);
            $termsToRender = array_values(array_intersect($termsToRender, $publishedTerms));
            if (empty($termsToRender)) {
                $termsToRender = !empty($publishedTerms) ? [min($publishedTerms)] : [1];
            }
        }

        $dossierData['termsEnclosed'] = $termsToRender;

        // 5. Render Cover Page
        $coverHtml = view('pdf.partials.book-cover', $dossierData)->render();
        [$coverCss, $coverBody] = $this->extractCssAndBody($coverHtml);

        // 6. Render each term's report card in the configured template
        $rcService = app(ReportCardService::class);
        $rcView = ReportCardService::viewForTemplate($template);
        $termSections = [];
        $templateCss = '';

        foreach ($termsToRender as $termNum) {
            $termData = $rcService->build($student, (int) $termNum, $session, $optionsOverrides);
            $termHtml = view($rcView, $termData)->render();
            [$tCss, $tBody] = $this->extractCssAndBody($termHtml);
            if (empty($templateCss)) {
                $templateCss = $tCss;
            }
            $termSections[] = $tBody;
        }

        // 7. Render Cumulative Dossier Ledger & Verdict
        $ledgerHtml = view('pdf.partials.book-dossier-ledger', $dossierData)->render();
        [$ledgerCss, $ledgerBody] = $this->extractCssAndBody($ledgerHtml);

        // 8. Assemble unified styles and master HTML container
        $bookletCss = $this->getBookletMasterCss($templateCss);

        $html = '<!doctype html>' . "\n"
            . '<html lang="en">' . "\n"
            . '<head>' . "\n"
            . '    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>' . "\n"
            . '    <title>Student Academic Report Book - ' . htmlspecialchars($student->admission_number ?? (string) $student->id) . '</title>' . "\n"
            . '    <style>' . "\n"
            . $bookletCss . "\n"
            . '    </style>' . "\n"
            . '</head>' . "\n"
            . '<body>' . "\n"
            . '    <div class="booklet-section-cover">' . "\n"
            . $coverBody . "\n"
            . '    </div>' . "\n"
            . '    <div class="booklet-page-break"></div>' . "\n";

        foreach ($termSections as $tBody) {
            $html .= '    <div class="booklet-section-term">' . "\n"
                . $tBody . "\n"
                . '    </div>' . "\n"
                . '    <div class="booklet-page-break"></div>' . "\n";
        }

        $html .= '    <div class="booklet-section-ledger">' . "\n"
            . $ledgerBody . "\n"
            . '    </div>' . "\n"
            . '</body>' . "\n"
            . '</html>';

        return $html;
    }

    /**
     * Extract <style> contents and <body> contents from rendered HTML.
     */
    protected function extractCssAndBody(string $html): array
    {
        $css = '';
        if (preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $html, $matches)) {
            $css = implode("\n", $matches[1]);
        }

        $body = $html;
        if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $body = $matches[1];
        }

        return [$css, $body];
    }

    /**
     * Master CSS stylesheet for the entire Student Report Book.
     */
    protected function getBookletMasterCss(string $templateCss = ''): string
    {
        return <<<'CSS'
@page { margin: 4mm 6mm; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 8pt;
    color: #1e293b;
    background: #ffffff;
    line-height: 1.25;
}
.booklet-page-break {
    page-break-after: always;
    clear: both;
}
.page-break {
    page-break-after: always;
    clear: both;
}

/* ══════════════════════════════════════════════════════════════════
   COVER PAGE ROYAL STYLING
   ══════════════════════════════════════════════════════════════════ */
.book-cover-page {
    width: 100%;
}
.cover-outer-frame {
    border: 3.5px double #1e3a8a;
    padding: 4px;
    background: #ffffff;
}
.cover-inner-frame {
    border: 1.5px solid #c59b27;
    padding: 10px 14px 8px 14px;
    position: relative;
    background: #ffffff;
}
.corner-ornament {
    position: absolute;
    width: 14px;
    height: 14px;
    border-color: #c59b27;
}
.corner-top-left { top: 3px; left: 3px; border-top: 2.5px solid #c59b27; border-left: 2.5px solid #c59b27; }
.corner-top-right { top: 3px; right: 3px; border-top: 2.5px solid #c59b27; border-right: 2.5px solid #c59b27; }
.corner-bottom-left { bottom: 3px; left: 3px; border-bottom: 2.5px solid #c59b27; border-left: 2.5px solid #c59b27; }
.corner-bottom-right { bottom: 3px; right: 3px; border-bottom: 2.5px solid #c59b27; border-right: 2.5px solid #c59b27; }

.cover-header {
    text-align: center;
    margin-bottom: 6px;
}
.cover-logo-container {
    margin-bottom: 4px;
}
.cover-logo-box {
    width: 58px;
    height: 58px;
    margin: 0 auto;
    border: 1.5px solid #c59b27;
    border-radius: 6px;
    background: #ffffff;
    display: table;
}
.cover-logo-img {
    max-width: 52px;
    max-height: 52px;
    width: auto !important;
    height: auto !important;
    display: block;
    margin: 3px auto;
}
.placeholder-crest {
    background: #1e3a8a;
    color: #ffffff;
    font-size: 20pt;
    font-weight: 800;
    text-align: center;
    line-height: 58px;
}
.cover-school-name {
    font-size: 17pt;
    font-weight: 900;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    line-height: 1.1;
    margin-bottom: 2px;
}
.cover-school-motto {
    font-size: 8.5pt;
    font-style: italic;
    color: #b45309;
    margin-bottom: 2px;
}
.cover-school-address {
    font-size: 7.5pt;
    color: #475569;
}

.cover-divider-wrapper {
    display: table;
    width: 100%;
    margin: 6px 0 8px 0;
}
.cover-divider-line {
    display: table-cell;
    vertical-align: middle;
    border-bottom: 1.5px solid #c59b27;
    width: 32%;
}
.cover-divider-badge {
    display: table-cell;
    text-align: center;
    font-size: 7pt;
    font-weight: 800;
    color: #1e3a8a;
    letter-spacing: 1.5px;
    width: 36%;
    padding: 0 4px;
}

.cover-title-section {
    text-align: center;
    margin-bottom: 8px;
}
.cover-main-title {
    font-size: 13pt;
    font-weight: 900;
    color: #1e3a8a;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.cover-subtitle {
    font-size: 7.5pt;
    color: #64748b;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}
.cover-session-pill {
    display: inline-block;
    background: #1e3a8a;
    color: #ffffff;
    font-size: 8pt;
    font-weight: 800;
    padding: 3px 14px;
    border-radius: 12px;
    letter-spacing: 1px;
}

/* Student Showcase Card */
.cover-student-card {
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-left: 4px solid #1e3a8a;
    border-radius: 5px;
    padding: 6px 10px;
    margin-bottom: 8px;
}
.cover-student-table {
    width: 100%;
    border-collapse: collapse;
}
.cover-photo-cell {
    width: 82px;
    vertical-align: middle;
    padding-right: 12px;
}
.cover-photo-frame {
    width: 76px;
    height: 94px;
    border: 2px solid #c59b27;
    border-radius: 5px;
    background: #ffffff;
    overflow: hidden;
    display: table;
    margin: 0 auto;
}
.cover-photo-img {
    width: 72px;
    height: 90px;
    object-fit: cover;
    display: block;
    margin: 2px auto;
    border-radius: 3px;
}
.cover-photo-placeholder {
    width: 76px;
    height: 94px;
    background: #e2e8f0;
    text-align: center;
    padding-top: 20px;
}
.placeholder-avatar {
    font-size: 26pt;
    font-weight: 800;
    color: #64748b;
    line-height: 1;
}
.placeholder-sub {
    font-size: 6pt;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 1px;
    margin-top: 4px;
}
.cover-meta-cell {
    vertical-align: middle;
}
.cover-student-name {
    font-size: 13.5pt;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: 0.5px;
    line-height: 1.1;
    margin-bottom: 3px;
}
.cover-student-badge-row {
    margin-bottom: 5px;
}
.cover-status-tag {
    display: inline-block;
    background: #dcfce7;
    color: #15803d;
    font-size: 6.5pt;
    font-weight: 800;
    padding: 1.5px 6px;
    border-radius: 8px;
    border: 0.5px solid #86efac;
    margin-right: 4px;
}
.cover-class-tag {
    display: inline-block;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 6.5pt;
    font-weight: 800;
    padding: 1.5px 6px;
    border-radius: 8px;
    border: 0.5px solid #7dd3fc;
}
.cover-bio-grid {
    width: 100%;
    border-collapse: collapse;
}
.cover-bio-grid td {
    padding: 2px 3px;
    font-size: 7.5pt;
}
.cover-bio-grid .bio-k {
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 6.5pt;
    width: 22%;
}
.cover-bio-grid .bio-v {
    color: #0f172a;
    width: 28%;
}

/* Registry Section */
.cover-registry-section {
    margin-bottom: 8px;
}
.cover-registry-heading {
    font-size: 7pt;
    font-weight: 800;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 3px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 2px;
}
.cover-registry-table {
    width: 100%;
    border-collapse: collapse;
}
.cover-registry-table th {
    background: #f1f5f9;
    color: #0f172a;
    font-size: 6.5pt;
    font-weight: 800;
    text-transform: uppercase;
    border: 1px solid #cbd5e1;
    padding: 3.5px 5px;
    text-align: left;
}
.cover-registry-table td {
    border: 1px solid #e2e8f0;
    padding: 3.5px 5px;
    font-size: 7pt;
    vertical-align: middle;
}
.cover-registry-table tr:nth-child(even) td {
    background: #f8fafc;
}
.reg-title {
    font-weight: 800;
    color: #1e3a8a;
    font-size: 7pt;
}
.reg-desc {
    color: #475569;
    font-size: 6.5pt;
}
.reg-status {
    text-align: center;
}
.status-pill {
    display: inline-block;
    padding: 1.5px 6px;
    border-radius: 8px;
    font-size: 6pt;
    font-weight: 800;
    letter-spacing: 0.3px;
}
.status-enclosed {
    background: #dcfce7;
    color: #15803d;
    border: 0.5px solid #86efac;
}
.status-pending {
    background: #f1f5f9;
    color: #94a3b8;
    border: 0.5px solid #cbd5e1;
}

/* Certification Footer */
.cover-cert-section {
    border-top: 1.5px solid #c59b27;
    padding-top: 6px;
    margin-top: 4px;
}
.cover-cert-table {
    width: 100%;
    border-collapse: collapse;
}
.cover-cert-cell {
    vertical-align: top;
    padding: 0 4px;
}
.cert-left {
    width: 44%;
}
.cert-label {
    font-size: 6.5pt;
    font-weight: 800;
    color: #1e3a8a;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.cert-text {
    font-size: 6.5pt;
    color: #64748b;
    line-height: 1.25;
    margin-bottom: 4px;
}
.cert-issue-date {
    font-size: 6.5pt;
    color: #0f172a;
}
.cert-center {
    width: 22%;
    text-align: center;
    vertical-align: middle;
}
.cover-qr-frame {
    display: inline-block;
    padding: 2px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
}
.cover-qr-img {
    width: 46px;
    height: 46px;
    display: block;
}
.cover-qr-caption {
    font-size: 5.5pt;
    font-weight: 800;
    color: #1e3a8a;
    letter-spacing: 0.3px;
    margin-top: 2px;
}
.cert-right {
    width: 34%;
    text-align: center;
    vertical-align: bottom;
}
.cover-sig-img {
    max-height: 24px;
    max-width: 80px;
    object-fit: contain;
    margin-bottom: 2px;
}
.cover-sig-spacer {
    height: 20px;
}
.cover-sig-line {
    border-top: 1px solid #94a3b8;
    padding-top: 2px;
    font-size: 7.5pt;
    font-weight: 800;
    color: #0f172a;
}
.cover-sig-line.has-img {
    margin-top: 1px;
}
.cover-sig-sub {
    font-size: 6pt;
    color: #64748b;
    font-style: italic;
    margin-bottom: 2px;
}
.cover-seal-stamp {
    font-size: 5.5pt;
    font-weight: 800;
    color: #c59b27;
    letter-spacing: 0.5px;
}

/* ══════════════════════════════════════════════════════════════════
   DOSSIER LEDGER STYLING
   ══════════════════════════════════════════════════════════════════ */
.dossier-page { width: 100%; }
.school-header { border-bottom: 2px solid #1e3a8a; padding-bottom: 6px; margin-bottom: 8px; width: 100%; display: table; }
.logo-cell { display: table-cell; width: 62px; vertical-align: middle; text-align: left; }
.logo-img { max-height: 52px; max-width: 58px; object-fit: contain; }
.logo-placeholder { width: 48px; height: 48px; border-radius: 50%; background: #1e3a8a; color: #ffffff; text-align: center; line-height: 48px; font-size: 15pt; font-weight: 800; }
.title-cell { display: table-cell; vertical-align: middle; text-align: center; padding: 0 8px; }
.school-motto { font-size: 7.5pt; font-style: italic; color: #64748b; margin-bottom: 2px; }
.school-address { font-size: 7pt; color: #475569; }
.qr-header-cell { display: table-cell; width: 55px; vertical-align: middle; text-align: right; }
.qr-header-img { width: 48px; height: 48px; }
.booklet-ribbon { background: #1e3a8a; color: #ffffff; text-align: center; padding: 4px 6px; font-size: 8pt; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; border-radius: 3px; margin-bottom: 8px; }
.student-profile { display: table; width: 100%; margin-bottom: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 8px; }
.photo-cell { display: table-cell; width: 52px; vertical-align: middle; padding-right: 10px; }
.student-photo { width: 46px; height: 46px; border-radius: 50%; object-fit: cover; border: 2px solid #cbd5e1; }
.student-photo-placeholder { width: 46px; height: 46px; border-radius: 50%; background: #e2e8f0; border: 2px solid #cbd5e1; text-align: center; line-height: 44px; font-size: 16pt; font-weight: 800; color: #64748b; }
.bio-cell { display: table-cell; vertical-align: middle; }
.bio-table { width: 100%; border-collapse: collapse; }
.bio-table td { padding: 1.5px 3px; font-size: 7.5pt; }
.bio-label { color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 6pt; width: 15%; }
.bio-val { color: #0f172a; font-weight: 700; width: 35%; }
table.ledger-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; table-layout: fixed; }
table.ledger-table th { background: #f1f5f9; color: #0f172a; padding: 4px 2px; font-size: 6pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2px; border: 1px solid #cbd5e1; text-align: center; }
table.ledger-table th.subj-th { text-align: left; padding-left: 5px; width: 24%; }
table.ledger-table td { padding: 3.5px 2px; border: 1px solid #e2e8f0; text-align: center; font-size: 7pt; color: #334155; }
table.ledger-table td.subj-td { text-align: left; padding-left: 5px; font-weight: 700; color: #0f172a; word-wrap: break-word; }
table.ledger-table tr:nth-child(even) td { background: #fafafa; }
table.ledger-table td.highlight { background: #f8fafc; font-weight: 700; color: #1e3a8a; }
table.ledger-table td.bold { font-weight: 700; color: #0f172a; }
.grade-badge { display: inline-block; padding: 1px 3px; border-radius: 2px; font-weight: 800; font-size: 6.5pt; text-align: center; }
.badge-a { background: #dcfce7; color: #15803d; }
.badge-b { background: #e0f2fe; color: #0369a1; }
.badge-c { background: #fef9c3; color: #a16207; }
.badge-d { background: #ffedd5; color: #c2410c; }
.badge-f { background: #fee2e2; color: #b91c1c; }
.kpi-row { display: table; width: 100%; margin-bottom: 8px; border-spacing: 5px 0; margin-left: -5px; margin-right: -5px; }
.kpi-card { display: table-cell; width: 25%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 6px; text-align: center; }
.kpi-label { font-size: 6pt; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-bottom: 1px; }
.kpi-value { font-size: 11pt; font-weight: 800; color: #1e3a8a; line-height: 1.1; }
.kpi-sub { font-size: 6pt; font-weight: 600; color: #94a3b8; margin-top: 1px; }
.page2-header { display: table; width: 100%; background: #f1f5f9; border-left: 3.5px solid #1e3a8a; padding: 5px 8px; margin-bottom: 8px; }
.page2-header-title { display: table-cell; font-size: 7.5pt; font-weight: 800; color: #1e3a8a; text-transform: uppercase; }
.page2-header-info { display: table-cell; text-align: right; font-size: 7pt; font-weight: 600; color: #475569; }
.two-col-table { display: table; width: 100%; margin-bottom: 8px; }
.col-left { display: table-cell; width: 50%; padding-right: 6px; vertical-align: top; }
.col-right { display: table-cell; width: 50%; padding-left: 6px; vertical-align: top; }
.section-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 8px; }
.section-header { background: #f8fafc; padding: 3.5px 6px; font-size: 7pt; font-weight: 800; text-transform: uppercase; color: #1e3a8a; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.2px; }
.section-body { padding: 5px 6px; }
table.mini-table { width: 100%; border-collapse: collapse; font-size: 6.5pt; }
table.mini-table th { background: #f8fafc; border: 1px solid #cbd5e1; padding: 2.5px 2px; font-weight: 800; text-align: center; color: #475569; font-size: 6pt; }
table.mini-table td { border: 1px solid #e2e8f0; padding: 2.5px 2px; text-align: center; color: #334155; }
table.mini-table td.label-td { text-align: left; padding-left: 3px; font-weight: 600; color: #0f172a; }
.verdict-box { border-radius: 4px; padding: 6px 10px; text-align: center; margin-bottom: 8px; border: 1.5px solid; }
.verdict-success { background: #f0fdf4; border-color: #22c55e; color: #15803d; }
.verdict-warning { background: #fffbeb; border-color: #f59e0b; color: #b45309; }
.verdict-danger { background: #fef2f2; border-color: #ef4444; color: #b91c1c; }
.verdict-progress { background: #eff6ff; border-color: #3b82f6; color: #1d4ed8; }
.verdict-title { font-size: 9.5pt; font-weight: 900; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 1px; }
.verdict-sub { font-size: 7pt; font-weight: 600; }
.remark-row { margin-bottom: 5px; }
.remark-title { font-size: 6.5pt; font-weight: 800; text-transform: uppercase; color: #1e3a8a; margin-bottom: 1px; }
.remark-text { font-size: 7pt; font-style: italic; color: #1e293b; line-height: 1.25; }
.resumption-bar { background: #1e3a8a; color: #ffffff; text-align: center; padding: 4px; font-size: 7.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; border-radius: 3px; margin-bottom: 8px; }
.sigs-table { display: table; width: 100%; margin-top: 6px; }
.sig-cell { display: table-cell; width: 33.33%; text-align: center; padding: 0 8px; vertical-align: bottom; }
.sig-img { max-height: 22px; max-width: 75px; object-fit: contain; margin-bottom: 1px; }
.sig-line { border-top: 1px solid #94a3b8; margin-top: 12px; padding-top: 2px; font-size: 7pt; font-weight: 700; color: #0f172a; }
.sig-line.has-img { margin-top: 1px; }
.sig-sub { font-size: 6pt; color: #64748b; font-style: italic; }
.footer { margin-top: 6px; text-align: center; font-size: 6pt; color: #94a3b8; }
CSS
        . "\n\n/* ═══ Template-Specific Styles ═══ */\n"
        . $templateCss;
    }
}

