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
            'psychomotorTimeline'       => $psychomotorTimeline['all'] ?? $psychomotorTimeline,
            'affectiveTimeline'         => $psychomotorTimeline['affective'] ?? [],
            'psychomotorDomainTimeline' => $psychomotorTimeline['psychomotor'] ?? [],
            'psychomotorStageLabel'     => $psychomotorTimeline['stage_label'] ?? '',
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

        $profile = PsychomotorService::getTraitsForClass($student->class_id);
        $affectiveList = $profile['affective'] ?? [];
        $psychomotorList = $profile['psychomotor'] ?? [];

        $getRating = function (int $termNum, string $label, string $slug) use ($allScores) {
            $ps = $allScores->get($termNum);
            if (!$ps || empty($ps->traits)) {
                return '—';
            }
            $traits = $ps->traits;
            return $traits[$label] ?? $traits[$slug] ?? $traits[strtolower($label)] ?? '—';
        };

        $affective = [];
        foreach ($affectiveList as $label) {
            $slug = \Illuminate\Support\Str::slug($label, '_');
            $affective[$slug] = [
                'label'  => $label,
                'domain' => 'affective',
                'term_1' => $getRating(1, $label, $slug),
                'term_2' => $getRating(2, $label, $slug),
                'term_3' => $getRating(3, $label, $slug),
            ];
        }

        $psychomotor = [];
        foreach ($psychomotorList as $label) {
            $slug = \Illuminate\Support\Str::slug($label, '_');
            $psychomotor[$slug] = [
                'label'  => $label,
                'domain' => 'psychomotor',
                'term_1' => $getRating(1, $label, $slug),
                'term_2' => $getRating(2, $label, $slug),
                'term_3' => $getRating(3, $label, $slug),
            ];
        }

        return [
            'stage'       => $profile['stage'],
            'stage_label' => $profile['stage_label'],
            'scale'       => $profile['scale'],
            'affective'   => $affective,
            'psychomotor' => $psychomotor,
            'all'         => array_merge($affective, $psychomotor),
        ];
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

        // 4. Determine terms to render in this book (A complete student report book covers Terms 1, 2, and 3)
        if ($isParentOrStudent) {
            $publishedTerms = ResultPublication::publishedTermsForClass($student->class_id, $session);
            $termsToRender = !empty($publishedTerms) ? array_values($publishedTerms) : [1];
            sort($termsToRender);
        } else {
            $termsToRender = $optionsOverrides['terms'] ?? [1, 2, 3];
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
    font-size: 8.5px;
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
   COVER PAGE: REGAL EXECUTIVE DESIGN
   ══════════════════════════════════════════════════════════════════ */
.book-cover-page {
    width: 100%;
}
.cover-outer-frame {
    border: 3.5px solid #1e3a5f;
    padding: 4px;
    background: #ffffff;
}
.cover-inner-frame {
    border: 1px solid #c4975a;
    padding: 14px 18px 10px 18px;
    position: relative;
    background: #ffffff;
}

.cover-header {
    text-align: center;
    margin-bottom: 8px;
}
.cover-logo-wrapper {
    margin-bottom: 4px;
}
.cover-logo-box {
    width: 58px;
    height: 58px;
    margin: 0 auto;
    border: 1.5px solid #c4975a;
    border-radius: 6px;
    background: #ffffff;
    display: table;
}
.cover-logo-img {
    max-width: 50px;
    max-height: 50px;
    width: auto !important;
    height: auto !important;
    display: block;
    margin: 4px auto;
}
.placeholder-crest {
    background: #1e3a5f;
    color: #c4975a;
    font-size: 22px;
    font-weight: 800;
    text-align: center;
    line-height: 58px;
}
.cover-school-name {
    font-size: 19px;
    font-weight: 800;
    color: #1e3a5f;
    text-transform: uppercase;
    letter-spacing: 2px;
    line-height: 1.15;
    margin-bottom: 3px;
}
.cover-school-motto {
    font-size: 8.5px;
    color: #c4975a;
    font-style: italic;
    font-weight: 600;
    margin-bottom: 2px;
}
.cover-school-meta {
    font-size: 7.5px;
    color: #64748b;
}

.cover-fillet {
    display: table;
    width: 100%;
    margin: 8px 0 10px 0;
}
.fillet-line {
    display: table-cell;
    vertical-align: middle;
    border-bottom: 1px solid #c4975a;
    width: 32%;
}
.fillet-tag {
    display: table-cell;
    text-align: center;
    font-size: 7px;
    font-weight: 800;
    color: #1e3a5f;
    letter-spacing: 2px;
    width: 36%;
    padding: 0 6px;
    text-transform: uppercase;
}

.cover-title-area {
    text-align: center;
    margin-bottom: 10px;
}
.cover-doc-type {
    font-size: 7.5px;
    color: #c4975a;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.cover-doc-title {
    font-size: 15px;
    font-weight: 800;
    color: #1e3a5f;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 5px;
}
.cover-session-badge {
    display: inline-block;
    background: #1e3a5f;
    color: #c4975a;
    font-size: 8.5px;
    font-weight: 800;
    padding: 3px 18px;
    border-radius: 3px;
    letter-spacing: 1.5px;
}

/* Student Credential Plaque */
.cover-student-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-top: 3px solid #1e3a5f;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 10px;
}
.cover-student-table {
    width: 100%;
    border-collapse: collapse;
}
.cover-photo-cell {
    width: 86px;
    vertical-align: middle;
    padding-right: 12px;
}
.cover-photo-frame {
    width: 80px;
    height: 100px;
    border: 1.5px solid #c4975a;
    border-radius: 4px;
    background: #ffffff;
    overflow: hidden;
    display: table;
    margin: 0 auto;
}
.cover-photo-img {
    width: 76px;
    height: 96px;
    object-fit: cover;
    display: block;
    margin: 2px auto;
    border-radius: 2px;
}
.cover-photo-placeholder {
    width: 80px;
    height: 100px;
    background: #f1f5f9;
    text-align: center;
    padding-top: 24px;
}
.placeholder-initial {
    font-size: 28px;
    font-weight: 800;
    color: #1e3a5f;
    line-height: 1;
}
.placeholder-label {
    font-size: 6px;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 1px;
    margin-top: 4px;
}
.cover-meta-cell {
    vertical-align: middle;
}
.cover-name-banner {
    border-bottom: 1.5px solid #c4975a;
    padding-bottom: 4px;
    margin-bottom: 6px;
}
.cover-student-name {
    font-size: 14px;
    font-weight: 800;
    color: #1e3a5f;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    line-height: 1.15;
}
.cover-student-class {
    font-size: 8.5px;
    font-weight: 700;
    color: #c4975a;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 1px;
}
.cover-data-grid {
    width: 100%;
    border-collapse: collapse;
}
.cover-data-grid td {
    padding: 2.5px 3px;
    font-size: 8px;
}
.data-key {
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 7px;
    width: 24%;
}
.data-val {
    color: #0f172a;
    width: 26%;
}

/* Folio Index */
.cover-folio-section {
    margin-bottom: 10px;
}
.folio-heading {
    font-size: 7.5px;
    font-weight: 800;
    color: #1e3a5f;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 3px;
    border-bottom: 1px solid #1e3a5f;
    padding-bottom: 2px;
}
.folio-table {
    width: 100%;
    border-collapse: collapse;
}
.folio-table th {
    background: #1e3a5f;
    color: #ffffff;
    font-size: 7px;
    font-weight: 800;
    text-transform: uppercase;
    padding: 3px 5px;
    text-align: left;
}
.folio-table td {
    border: 1px solid #e2e8f0;
    padding: 3.5px 5px;
    font-size: 7.5px;
    vertical-align: middle;
}
.folio-table tr:nth-child(even) td {
    background: #f8fafc;
}
.folio-num {
    font-weight: 800;
    color: #1e3a5f;
}
.folio-desc {
    color: #475569;
}
.folio-tag {
    text-align: center;
    font-weight: 700;
    color: #1e3a5f;
    font-size: 7px;
}

/* Endorsement Section */
.cover-endorsement-section {
    border-top: 1px solid #c4975a;
    padding-top: 8px;
    margin-top: 6px;
}
.cover-endorsement-table {
    width: 100%;
    border-collapse: collapse;
}
.endorse-left {
    width: 44%;
    vertical-align: top;
    padding-right: 6px;
}
.endorse-heading {
    font-size: 7px;
    font-weight: 800;
    color: #1e3a5f;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.endorse-text {
    font-size: 6.8px;
    color: #64748b;
    line-height: 1.25;
    margin-bottom: 4px;
}
.endorse-date {
    font-size: 7px;
    color: #0f172a;
}
.endorse-center {
    width: 22%;
    text-align: center;
    vertical-align: middle;
}
.endorse-qr-box {
    display: inline-block;
    padding: 2px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
}
.endorse-qr-img {
    width: 46px;
    height: 46px;
    display: block;
}
.endorse-qr-text {
    font-size: 6px;
    font-weight: 800;
    color: #1e3a5f;
    letter-spacing: 0.5px;
    margin-top: 2px;
}
.endorse-right {
    width: 34%;
    text-align: center;
    vertical-align: bottom;
    padding-left: 6px;
}
.endorse-sig-img {
    max-height: 24px;
    max-width: 80px;
    object-fit: contain;
    margin-bottom: 2px;
}
.endorse-sig-spacer {
    height: 20px;
}
.endorse-sig-line {
    border-top: 1px solid #1e3a5f;
    padding-top: 2px;
    font-size: 8px;
    font-weight: 800;
    color: #1e3a5f;
}
.endorse-sig-line.has-img {
    margin-top: 1px;
}
.endorse-sig-title {
    font-size: 6.5px;
    color: #64748b;
    font-style: italic;
    margin-top: 1px;
}

/* ══════════════════════════════════════════════════════════════════
   DOSSIER LEDGER & VERDICT (MATCHING ELEGANT LUXURY PALETTE)
   ══════════════════════════════════════════════════════════════════ */
.dossier-page {
    width: 100%;
}
.dossier-outer-frame {
    border: 3.5px solid #1e3a5f;
    padding: 4px;
    background: #ffffff;
}
.dossier-inner-frame {
    border: 1px solid #c4975a;
    padding: 6px;
}

/* Header */
.header {
    text-align: center;
    padding-bottom: 4px;
    margin-bottom: 6px;
    border-bottom: 2px solid #1e3a5f;
}
.header-table { display: table; width: 100%; }
.header-cell { display: table-cell; vertical-align: middle; }
.logo-cell { width: 62px; text-align: center; vertical-align: middle; }
.logo-box {
    width: 52px;
    height: 52px;
    border: 1.5px solid #c4975a;
    border-radius: 6px;
    background: white;
}
.logo {
    max-width: 46px;
    max-height: 46px;
    width: auto !important;
    height: auto !important;
    display: block;
    margin: 0 auto;
}
.school-name {
    font-size: 18px;
    font-weight: 800;
    color: #1e3a5f;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 2px;
}
.school-motto {
    font-size: 8px;
    color: #c4975a;
    font-style: italic;
    font-weight: 600;
    margin-bottom: 2px;
}
.school-info {
    font-size: 7.5px;
    color: #64748b;
    margin-bottom: 1px;
}
.report-badge {
    display: inline-block;
    margin-top: 4px;
    background: #1e3a5f;
    color: #c4975a;
    padding: 3px 16px;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 3px;
}

/* Meta Bar */
.meta-bar {
    display: table;
    width: 100%;
    margin-bottom: 4px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
}
.meta-cell {
    display: table-cell;
    width: 33.33%;
    padding: 4px;
    text-align: center;
    border-right: 1px solid #e2e8f0;
}
.meta-cell:last-child { border-right: none; }
.meta-label {
    font-size: 7px;
    font-weight: 800;
    color: #c4975a;
    text-transform: uppercase;
    display: block;
}
.meta-value {
    font-size: 9px;
    font-weight: 800;
    color: #1e3a5f;
}

/* Tables */
table.scores {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
    border: 1px solid #1e3a5f;
}
table.scores th {
    background: #1e3a5f;
    color: #ffffff;
    padding: 3px 4px;
    font-size: 7.5px;
    font-weight: 800;
    text-transform: uppercase;
    text-align: center;
    border: 1px solid #1e3a5f;
}
table.scores td {
    padding: 2.5px 4px;
    border: 1px solid #e2e8f0;
    text-align: center;
    font-size: 8px;
}
table.scores tr:nth-child(even) td {
    background: #f8fafc;
}
table.scores td.subj {
    text-align: left;
    font-weight: 700;
    color: #1e3a5f;
    padding-left: 6px;
}
table.scores td.bold {
    font-weight: 800;
}
table.scores td.gold-cell {
    background: #fef3c7;
    color: #1e3a5f;
}

/* Stats Bar */
.stats-bar {
    display: table;
    width: 100%;
    margin-bottom: 4px;
}
.stat {
    display: table-cell;
    padding: 1.5px;
}
.stat-inner {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 4px 2px;
    text-align: center;
}
.stat-inner.gold {
    background: #fef3c7;
    border-color: #c4975a;
}
.stat-label {
    font-size: 6.5px;
    font-weight: 800;
    color: #1e3a5f;
    text-transform: uppercase;
    margin-bottom: 1px;
}
.stat-value {
    font-size: 12px;
    font-weight: 800;
    color: #1e3a5f;
}

/* Page 2 Strip */
.page2-header-strip {
    display: table;
    width: 100%;
    background: #1e3a5f;
    color: #ffffff;
    padding: 4px 8px;
    margin-bottom: 6px;
    border-radius: 3px;
}
.strip-title {
    display: table-cell;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 1px;
    color: #c4975a;
}
.strip-info {
    display: table-cell;
    text-align: right;
    font-size: 7.5px;
    font-weight: 700;
}

/* Verdict Decree */
.verdict-decree {
    background: #1e3a5f;
    color: #ffffff;
    border: 1.5px solid #c4975a;
    border-radius: 4px;
    padding: 6px 12px;
    text-align: center;
    margin-bottom: 6px;
}
.verdict-decree-header {
    font-size: 7px;
    font-weight: 800;
    color: #c4975a;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.verdict-decree-title {
    font-size: 11px;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.verdict-decree-sub {
    font-size: 7.5px;
    color: #e2e8f0;
}

/* Sub-card */
.sub-card {
    border: 1px solid #1e3a5f;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 6px;
}
.sub-card-header {
    background: #1e3a5f;
    color: #ffffff;
    font-size: 7px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding: 3px 6px;
}

/* Two col */
.two-col-table {
    display: table;
    width: 100%;
}
.col-left {
    display: table-cell;
    width: 50%;
    padding-right: 4px;
    vertical-align: top;
}
.col-right {
    display: table-cell;
    width: 50%;
    padding-left: 4px;
    vertical-align: top;
}

/* Resumption Stripe */
.resumption-stripe {
    background: #1e3a5f;
    color: #ffffff;
    text-align: center;
    padding: 3.5px;
    font-size: 7.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 3px;
    margin-top: 6px;
}
.resumption-stripe strong {
    color: #c4975a;
}

/* Signatures */
.sigs-table {
    display: table;
    width: 100%;
}
.sig-cell {
    display: table-cell;
    width: 33.33%;
    text-align: center;
    padding: 0 8px;
    vertical-align: bottom;
}
.sig-img {
    max-height: 22px;
    max-width: 75px;
    object-fit: contain;
    margin-bottom: 1px;
}
.sig-line {
    border-top: 1px solid #1e3a5f;
    padding-top: 2px;
    font-size: 7.5px;
    font-weight: 800;
    color: #1e3a5f;
}
.sig-line.has-img {
    margin-top: 1px;
}
.sig-sub {
    font-size: 6.5px;
    color: #64748b;
    font-style: italic;
}
CSS
        . "\n\n/* ═══ Template-Specific Styles ═══ */\n"
        . $templateCss;
    }
}

