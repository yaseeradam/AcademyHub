<?php

namespace App\Support;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\PsychomotorScore;
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
        $classId = (int) $student->class_id;
        $sectionId = (int) ($student->section_id ?? 0);

        // Get all subjects configured or scored for this class
        $subjects = SchoolClass::allSubjectsForClass($classId);
        $subjectIds = $subjects->pluck('id');
        $subjectCount = max(1, (int) $subjectIds->count());

        // Discover active/reached terms for this session
        $availableTermsMap = AcademicTerm::availableTermsForSession($session);
        $maxAvailableTerm = !empty($availableTermsMap) ? max(array_keys($availableTermsMap)) : 3;

        // Fetch all scores for this student across the entire session
        $studentScores = Score::query()
            ->with('subject')
            ->where('student_id', $student->id)
            ->where('session', $session)
            ->whereIn('subject_id', $subjectIds)
            ->get();

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
        $subjectRows = $subjects->map(function (Subject $subject) use ($scoresByTerm, $allClassScores, $student, $maxAvailableTerm) {
            $subjId = $subject->id;

            $termDetails = [];
            $annualTotalsList = [];

            for ($t = 1; $t <= 3; $t++) {
                $termScores = $scoresByTerm->get($t);
                /** @var Score|null $score */
                $score = $termScores ? $termScores->get($subjId) : null;
                $isPending = ($t > $maxAvailableTerm && !$score);

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
            $tScores = $scoresByTerm->get($t);
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

                $sorted = $studentTotals->sortDesc();
                $rank = 1; $last = null;
                foreach ($sorted as $id => $tot) {
                    if ($last !== null && $tot !== $last) {
                        $rank++;
                    }
                    if ((int) $id === (int) $student->id) {
                        $tPosition = $rank;
                        break;
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
                'is_pending'    => ($t > $maxAvailableTerm && !$hasData),
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
            $sortedAnnual = $classStudentAnnualAverages->sortDesc();
            $rank = 1; $last = null;
            foreach ($sortedAnnual as $id => $avg) {
                if ($last !== null && abs($avg - $last) > 0.001) {
                    $rank++;
                }
                if ((int) $id === (int) $student->id) {
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

        // Promotion & Progression Engine
        $promotion = $this->determinePromotionStatus($student, $cumulativeAnnualAverage, $maxAvailableTerm);

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
                    ->whereIn('attendance_sheet_id', $sheetIds)
                    ->where('student_id', $student->id)
                    ->get();

                $present = $marks->whereIn('status', ['Present', 'Late'])->count();
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
    protected function determinePromotionStatus(Student $student, float $cumulativeAverage, int $maxAvailableTerm): array
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

        // Full year promotion determination
        if ($cumulativeAverage >= $passThreshold) {
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
}
