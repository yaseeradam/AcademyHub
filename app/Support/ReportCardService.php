<?php

namespace App\Support;

use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Support\TenantSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ReportCardService
{
    protected array $classScoresCache = [];
    protected array $subjectsCache = [];
    protected array $studentCountCache = [];

    /**
     * Resolve the report card Blade view name for a template key.
     *
     * Keeps template-to-view mapping in one place so WhatsApp, web, bulk
     * and preview controllers all render the same view for the same key.
     */
    public static function viewForTemplate(string $template): string
    {
        return match ($template) {
            'elegant' => 'pdf.report-card-elegant',
            'classic' => 'pdf.report-card-classic',
            'heritage' => 'pdf.report-card-heritage',
            'nordic' => 'pdf.report-card-nordic',
            'signature' => 'pdf.report-card-signature',
            'riverdale', 'riverdale-burgundy', 'riverdale-emerald', 'riverdale-purple' => 'pdf.report-card-riverdale',
            'greenwood' => 'pdf.report-card-greenwood',
            'compact', 'standard' => 'pdf.report-card-compact',
            default => 'pdf.report-card-compact',
        };
    }

    protected function getClassScores(int $classId, int $term, string $session, Collection $subjectIds): Collection
    {
        $cacheKey = "{$classId}-{$term}-{$session}";
        if (isset($this->classScoresCache[$cacheKey])) {
            return $this->classScoresCache[$cacheKey];
        }

        return $this->classScoresCache[$cacheKey] = Score::query()
            ->where('class_id', $classId)
            ->where('term', $term)
            ->where('session', $session)
            ->whereIn('subject_id', $subjectIds)
            ->get(['subject_id', 'student_id', 'total']);
    }

    /**
     * Build all data needed for the `pdf.report-card` view.
     *
     * @return array{
     *   student:\App\Models\Student,
     *   term:int,
     *   session:string,
     *   rows:\Illuminate\Support\Collection<int, array{subject:\App\Models\Subject,ca1:int|null,ca2:int|null,exam:int|null,total:int|null,grade:string|null}>,
     *   grandTotal:int,
     *   average:float,
     *   position:int,
     *   classAverage:float
     * }
     */
    public function build(Student $student, int $term, string $session, array $optionsOverrides = []): array
    {
        $student->load(['schoolClass', 'section']);

        // Scores are recorded against the class the student was in at the time of
        // result entry. If the student has since been moved to another class, we
        // must still find those scores and use the original class context for
        // subjects, class averages and positions.
        $scoreClassId = Score::query()
            ->where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->value('class_id');

        // Use the class recorded on the score records (the class the student was
        // in when the results were entered) for all class-scoped lookups.
        $student->class_id = (int) ($scoreClassId ?: $student->class_id);
        $student->load(['schoolClass', 'section']);

        $subjects = $this->subjectsForClass($student->class_id);
        $subjectIds = $subjects->pluck('id');
        $subjectCount = max(1, (int) $subjectIds->count());

        $scores = Score::query()
            ->with('subject')
            ->where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->whereIn('subject_id', $subjectIds)
            ->get()
            ->keyBy('subject_id');

        $psychomotor = \App\Models\PsychomotorScore::where('student_id', $student->id)
            ->where('class_id', $student->class_id)
            ->where('term', $term)
            ->where('session', $session)
            ->first();

        $psychomotorTraits = $psychomotor ? $psychomotor->traits : [];

        // Per-subject class averages and positions
        $allClassScores = $this->getClassScores($student->class_id, $term, $session, $subjectIds);

        $subjectClassAvgs = $allClassScores
            ->groupBy('subject_id')
            ->map(fn ($rows) => round($rows->avg('total'), 1));

        $subjectPositions = $allClassScores
            ->groupBy('subject_id')
            ->map(function ($rows) use ($student) {
                $sorted = $rows->sortByDesc('total')->values();
                $rank = 1; $last = null; $pos = 1;
                foreach ($sorted as $i => $r) {
                    if ($last !== null && $r->total !== $last) { $rank = $i + 1; }
                    if ((int) $r->student_id === (int) $student->id) { $pos = $rank; break; }
                    $last = $r->total;
                }
                return $pos;
            });

        $rows = $subjects->map(function (Subject $subject) use ($scores, $subjectClassAvgs, $subjectPositions) {
            /** @var \App\Models\Score|null $score */
            $score = $scores->get($subject->id);

            return [
                'subject'   => $subject,
                'ca1'       => $score?->ca1 ?? null,
                'ca2'       => $score?->ca2 ?? null,
                'exam'      => $score?->exam ?? null,
                'total'     => $score?->total ?? null,
                'grade'     => $score?->grade ?? ($score ? Score::gradeForTotal((int) $score->total, max(0, (int) config('academyhub.results_ca1_max', 20)) + max(0, (int) config('academyhub.results_ca2_max', 20)) + max(0, (int) config('academyhub.results_exam_max', 60))) : null),
                'class_avg' => $subjectClassAvgs->get($subject->id),
                'position'  => $score ? $subjectPositions->get($subject->id) : null,
            ];
        });

        $grandTotal = (int) $rows->sum(fn ($r) => (int) ($r['total'] ?? 0));
        $average = round($grandTotal / $subjectCount, 2);

        [$position, $classAverage] = $this->positionAndClassAverage(
            studentId: $student->id,
            classId: $student->class_id,
            subjectIds: $subjectIds,
            term: $term,
            session: $session
        );

        [$timesOpened, $timesPresent, $timesAbsent] = $this->attendanceSummary($student, $term, $session);

        if (isset($this->studentCountCache[$student->class_id])) {
            $totalStudents = $this->studentCountCache[$student->class_id];
        } else {
            $totalStudents = Student::query()
                ->where('class_id', $student->class_id)
                ->where('status', 'Active')
                ->count();
            $this->studentCountCache[$student->class_id] = $totalStudents;
        }

        [$highestAverage, $lowestAverage] = $this->highestLowestAverage($student->class_id, $subjectIds, $term, $session);

        $principalRemarks = $this->generatePrincipalRemarks($average, $position, $totalStudents);

        // Report card display options — read directly from settings.json to avoid
        // stale in-process config cache (config() is populated once at boot).
        $rcOptions = [
            'show_position'         => isset($optionsOverrides['show_position']) ? (bool) $optionsOverrides['show_position'] : $this->settingBool('rc_show_position', true),
            'show_attendance'       => isset($optionsOverrides['show_attendance']) ? (bool) $optionsOverrides['show_attendance'] : $this->settingBool('rc_show_attendance', true),
            'show_grading_key'      => isset($optionsOverrides['show_grading_key']) ? (bool) $optionsOverrides['show_grading_key'] : $this->settingBool('rc_show_grading_key', true),
            'show_class_average'    => isset($optionsOverrides['show_class_average']) ? (bool) $optionsOverrides['show_class_average'] : $this->settingBool('rc_show_class_average', true),
            'show_watermark'        => isset($optionsOverrides['show_watermark']) ? (bool) $optionsOverrides['show_watermark'] : $this->settingBool('rc_show_watermark', true),
            'show_next_term_date'   => isset($optionsOverrides['show_next_term_date']) ? (bool) $optionsOverrides['show_next_term_date'] : $this->settingBool('rc_show_next_term_date', true),
            'show_teacher_remarks'  => isset($optionsOverrides['show_teacher_remarks']) ? (bool) $optionsOverrides['show_teacher_remarks'] : $this->settingBool('rc_show_teacher_remarks', true),
            'show_principal_remarks'=> isset($optionsOverrides['show_principal_remarks']) ? (bool) $optionsOverrides['show_principal_remarks'] : $this->settingBool('rc_show_principal_remarks', true),
            'show_psychomotor'      => isset($optionsOverrides['show_psychomotor']) ? (bool) $optionsOverrides['show_psychomotor'] : $this->settingBool('rc_show_psychomotor', false),
            'psychomotor_style'     => isset($optionsOverrides['psychomotor_style']) ? (string) $optionsOverrides['psychomotor_style'] : ($this->settings()['rc_psychomotor_style'] ?? 'progress'),
            'show_school_fees'      => isset($optionsOverrides['show_school_fees']) ? (bool) $optionsOverrides['show_school_fees'] : $this->settingBool('rc_show_school_fees', false),
            'show_signatures'       => isset($optionsOverrides['show_signatures']) ? (bool) $optionsOverrides['show_signatures'] : $this->settingBool('rc_show_signatures', false),
        ];

        // School fees data — also read from settings.json directly
        $schoolFees = null;
        if ($rcOptions['show_school_fees']) {
            $rawSettings   = $this->settings();
            $feesByClass   = $rawSettings['rc_school_fees_by_class'] ?? null;
            if (is_string($feesByClass)) {
                $feesByClass = json_decode($feesByClass, true) ?? [];
            }
            $feesByClass = is_array($feesByClass) ? $feesByClass : [];
            $classId     = $student->class_id;
            $feeAmount   = $feesByClass[(string) $classId] ?? null;

            if ($feeAmount !== null) {
                $schoolFees = [
                    'amount'         => (float) $feeAmount,
                    'account_number' => $rawSettings['rc_school_fees_account_number'] ?? config('academyhub.rc_school_fees_account_number'),
                    'bank_name'      => $rawSettings['rc_school_fees_bank_name'] ?? config('academyhub.rc_school_fees_bank_name'),
                    'account_name'   => $rawSettings['rc_school_fees_account_name'] ?? config('academyhub.rc_school_fees_account_name'),
                    'currency'       => $rawSettings['currency_symbol'] ?? config('academyhub.currency_symbol', '₦'),
                ];
            }
        }

        // Signature images — read from settings.json directly
        $signatureImages = null;
        if ($rcOptions['show_signatures']) {
            $s            = $this->settings();
            $principalSig = $s['rc_principal_signature_image'] ?? null;
            $teacherSig   = $s['rc_teacher_signature_image'] ?? null;
            $signatureImages = [
                'principal' => $principalSig ? public_path('uploads/' . str_replace('\\', '/', $principalSig)) : null,
                'teacher'   => $teacherSig   ? public_path('uploads/' . str_replace('\\', '/', $teacherSig))   : null,
            ];
        }

        return [
            'student' => $student,
            'term' => $term,
            'session' => $session,
            'rows' => $rows,
            'grandTotal' => $grandTotal,
            'average' => $average,
            'position' => $position,
            'psychomotorTraits' => $psychomotorTraits,
            'classAverage' => $classAverage,
            'timesOpened' => $timesOpened,
            'timesPresent' => $timesPresent,
            'timesAbsent' => $timesAbsent,
            'totalStudents' => $totalStudents,
            'highestAverage' => $highestAverage,
            'lowestAverage' => $lowestAverage,
            'principalRemarks' => $principalRemarks,
            'teacherRemarks' => null,
            'nextTermDate' => null,
            'rcOptions' => $rcOptions,
            'schoolFees' => $schoolFees,
            'signatureImages' => $signatureImages,
        ];
    }

    /**
     * @return array{0:int|null,1:int|null,2:int|null} times opened, present, absent.
     */
    private function attendanceSummary(Student $student, int $term, string $session): array
    {
        $sectionId = (int) ($student->section_id ?? 0);
        if ($sectionId <= 0) {
            return [null, null, null];
        }

        $sheetsQuery = AttendanceSheet::query()
            ->where('class_id', $student->class_id)
            ->where('section_id', $sectionId)
            ->where('term', $term)
            ->where('session', $session);

        $timesOpened = (int) $sheetsQuery->count();

        $counts = AttendanceMark::query()
            ->join('attendance_sheets', 'attendance_sheets.id', '=', 'attendance_marks.sheet_id')
            ->where('attendance_marks.student_id', $student->id)
            ->where('attendance_sheets.class_id', $student->class_id)
            ->where('attendance_sheets.section_id', $sectionId)
            ->where('attendance_sheets.term', $term)
            ->where('attendance_sheets.session', $session)
            ->selectRaw("SUM(CASE WHEN attendance_marks.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count")
            ->selectRaw("SUM(CASE WHEN attendance_marks.status IN ('Present','Late','Excused') THEN 1 ELSE 0 END) AS present_count")
            ->first();

        $timesAbsent = (int) ($counts?->absent_count ?? 0);
        $timesPresent = (int) ($counts?->present_count ?? 0);

        if ($timesOpened === 0 && ($timesPresent + $timesAbsent) > 0) {
            $timesOpened = $timesPresent + $timesAbsent;
        }

        if ($timesOpened <= 0 && $timesPresent === 0 && $timesAbsent === 0) {
            return [null, null, null];
        }

        return [$timesOpened, $timesPresent, $timesAbsent];
    }

    private function subjectsForClass(int $classId): Collection
    {
        if (isset($this->subjectsCache[$classId])) {
            return $this->subjectsCache[$classId];
        }

        $ids = SubjectAllocation::query()
            ->where('class_id', $classId)
            ->distinct()
            ->pluck('subject_id')
            ->unique()
            ->values();

        $subjects = $ids->isEmpty()
            ? Subject::query()->orderBy('name')->get()
            : Subject::query()->whereIn('id', $ids)->orderBy('name')->get();

        return $this->subjectsCache[$classId] = $subjects->unique('id')->values();
    }

    /**
     * @return array{0:int,1:float} position (1-based) and class average.
     */
    private function positionAndClassAverage(
        int $studentId,
        int $classId,
        Collection $subjectIds,
        int $term,
        string $session
    ): array {
        $scores = $this->getClassScores($classId, $term, $session, $subjectIds);

        if ($scores->isEmpty()) {
            return [1, 0.0];
        }

        $totalsByStudent = $scores
            ->groupBy('student_id')
            ->map(fn ($rows) => (int) $rows->sum('total'));

        $subjectCount = max(1, (int) $subjectIds->count());
        $classAverage = round($totalsByStudent->avg() / $subjectCount, 2);

        $sorted = $totalsByStudent->sortDesc();
        $position = 1;
        $rank = 0;
        $last = null;

        foreach ($sorted as $id => $total) {
            if ($last === null || $total !== $last) {
                $rank++;
                $last = $total;
            }

            if ((int) $id === (int) $studentId) {
                $position = $rank;
                break;
            }
        }

        return [$position, $classAverage];
    }

    /**
     * @return array{0:float,1:float} highest and lowest average.
     */
    private function highestLowestAverage(
        int $classId,
        Collection $subjectIds,
        int $term,
        string $session
    ): array {
        $scores = $this->getClassScores($classId, $term, $session, $subjectIds);

        if ($scores->isEmpty()) {
            return [0.0, 0.0];
        }

        $totalsByStudent = $scores
            ->groupBy('student_id')
            ->map(fn ($rows) => (int) $rows->sum('total'));

        $subjectCount = max(1, (int) $subjectIds->count());
        $averages = $totalsByStudent->map(fn ($total) => round($total / $subjectCount, 2));

        return [
            round($averages->max(), 2),
            round($averages->min(), 2),
        ];
    }

    private function generatePrincipalRemarks(float $average, int $position, int $totalStudents): string
    {
        if ($average >= 70) {
            if ($position === 1) {
                return 'Outstanding performance! Keep up the excellent work and continue to be a role model for others.';
            }
            return 'Excellent performance! Your hard work and dedication are commendable. Keep it up!';
        }

        if ($average >= 60) {
            if ($position <= 3) {
                return 'Very good performance! You are doing well. With more effort, you can achieve even greater heights.';
            }
            return 'Good work! You have shown great potential. Keep pushing yourself to reach excellence.';
        }

        if ($average >= 50) {
            return 'Satisfactory performance. You can do better with more focus and consistent effort. Keep working hard.';
        }

        if ($average >= 40) {
            return 'Fair performance. You need to put in more effort and seek help where necessary. Improvement is expected.';
        }

        return 'Poor performance. Serious attention is required. Please work closely with your teachers and parents to improve.';
    }

    // ─── Settings helpers ────────────────────────────────────────────────────

    /**
     * Read the settings.json file, cached for the lifetime of this request.
     * This bypasses the in-process config() cache that is populated once at
     * application boot and does not reflect mid-request file changes.
     *
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        static $cache = null;

        if ($cache === null) {
            $path = TenantSettings::settingsPath();

            $cache = File::exists($path)
                ? (json_decode(File::get($path), true) ?? [])
                : [];
        }

        return $cache;
    }

    /**
     * Read a boolean setting directly from settings.json.
     */
    private function settingBool(string $key, bool $default = false): bool
    {
        $settings = $this->settings();

        if (!array_key_exists($key, $settings)) {
            return $default;
        }

        $value = $settings[$key];

        // JSON booleans are already native bool, but guard against stored strings/ints.
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
