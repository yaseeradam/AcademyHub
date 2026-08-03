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
        // Load current (actual) class & section for display on the report card header.
        $student->load(['schoolClass', 'section']);

        // Preserve the student's actual class_id and section_id for display.
        // These are always shown on the report card exactly as registered.
        $displayClassId   = (int) $student->class_id;
        $displaySectionId = (int) ($student->section_id ?? 0);

        // Scores are recorded against the class the student was in at the time of
        // result entry. If the student has since been moved to another class we
        // must still find those scores and use the original class context for
        // subjects, class averages and positions.
        $scoreClassId = Score::query()
            ->where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->value('class_id');

        // Use the score-entry class ONLY for internal calculations
        // (subjects list, class averages, positions). Do NOT change the
        // display class/section — the student's registered class is always shown.
        $calcClassId = (int) ($scoreClassId ?: $displayClassId);
        $student->class_id = $calcClassId;

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

        $rows = $subjects->map(function (Subject $subject) use ($scores, $subjectClassAvgs, $subjectPositions, $allClassScores) {
            /** @var \App\Models\Score|null $score */
            $score = $scores->get($subject->id);

            $subjClassScores = $allClassScores->where('subject_id', $subject->id);
            $subjHighest = $subjClassScores->max('total');
            $subjLowest  = $subjClassScores->min('total');

            return [
                'subject'        => $subject,
                'ca1'            => $score?->ca1 ?? null,
                'ca2'            => $score?->ca2 ?? null,
                'exam'           => $score?->exam ?? null,
                'total'          => $score?->total ?? null,
                'grade'          => $score?->grade ?? ($score ? Score::gradeForTotal((int) $score->total, max(0, (int) config('academyhub.results_ca1_max', 20)) + max(0, (int) config('academyhub.results_ca2_max', 20)) + max(0, (int) config('academyhub.results_exam_max', 60))) : null),
                'class_avg'      => $subjectClassAvgs->get($subject->id),
                'position'       => $score ? $subjectPositions->get($subject->id) : null,
                'highest'        => $subjHighest !== null ? (int) $subjHighest : null,
                'lowest'         => $subjLowest !== null ? (int) $subjLowest : null,
                'teacher_remark' => $score?->remarks ?? null,
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

        // Cumulative annual summary for 1st, 2nd, 3rd term
        $cumulativeSummary = [];
        for ($t = 1; $t <= 3; $t++) {
            $tScores = Score::where('student_id', $student->id)
                ->where('session', $session)
                ->where('term', $t)
                ->get();
            $tTotal = $tScores->sum('total');
            $tCount = max(1, $tScores->count());
            $cumulativeSummary["term_{$t}"] = [
                'total'   => $tScores->isNotEmpty() ? $tTotal : null,
                'average' => $tScores->isNotEmpty() ? round($tTotal / $tCount, 1) : null,
            ];
        }

        $principalRemarks = $this->generateAIRemarks(
            $student,
            $rows,
            $average,
            $position,
            $totalStudents,
            $timesOpened,
            $timesPresent,
            $timesAbsent,
            'principal'
        ) ?? $this->generatePrincipalRemarks($average, $position, $totalStudents);

        $teacherRemarks = $this->generateAIRemarks(
            $student,
            $rows,
            $average,
            $position,
            $totalStudents,
            $timesOpened,
            $timesPresent,
            $timesAbsent,
            'teacher'
        ) ?? $this->generateTeacherRemarksFallback($average, $position, $totalStudents);

        // Report card display options — read directly from settings.json to avoid
        // stale in-process config cache (config() is populated once at boot).
        $rcOptions = [
            'show_position'                  => isset($optionsOverrides['show_position']) ? (bool) $optionsOverrides['show_position'] : $this->settingBool('rc_show_position', true),
            'show_attendance'                => isset($optionsOverrides['show_attendance']) ? (bool) $optionsOverrides['show_attendance'] : $this->settingBool('rc_show_attendance', true),
            'show_grading_key'               => isset($optionsOverrides['show_grading_key']) ? (bool) $optionsOverrides['show_grading_key'] : $this->settingBool('rc_show_grading_key', true),
            'show_class_average'             => isset($optionsOverrides['show_class_average']) ? (bool) $optionsOverrides['show_class_average'] : $this->settingBool('rc_show_class_average', true),
            'show_watermark'                 => isset($optionsOverrides['show_watermark']) ? (bool) $optionsOverrides['show_watermark'] : $this->settingBool('rc_show_watermark', true),
            'show_next_term_date'            => isset($optionsOverrides['show_next_term_date']) ? (bool) $optionsOverrides['show_next_term_date'] : $this->settingBool('rc_show_next_term_date', true),
            'show_teacher_remarks'           => isset($optionsOverrides['show_teacher_remarks']) ? (bool) $optionsOverrides['show_teacher_remarks'] : $this->settingBool('rc_show_teacher_remarks', true),
            'show_principal_remarks'         => isset($optionsOverrides['show_principal_remarks']) ? (bool) $optionsOverrides['show_principal_remarks'] : $this->settingBool('rc_show_principal_remarks', true),
            'show_psychomotor'               => isset($optionsOverrides['show_psychomotor']) ? (bool) $optionsOverrides['show_psychomotor'] : $this->settingBool('rc_show_psychomotor', false),
            'psychomotor_style'              => isset($optionsOverrides['psychomotor_style']) ? (string) $optionsOverrides['psychomotor_style'] : ($this->settings()['rc_psychomotor_style'] ?? 'progress'),
            'show_school_fees'               => isset($optionsOverrides['show_school_fees']) ? (bool) $optionsOverrides['show_school_fees'] : $this->settingBool('rc_show_school_fees', false),
            'show_signatures'                => isset($optionsOverrides['show_signatures']) ? (bool) $optionsOverrides['show_signatures'] : $this->settingBool('rc_show_signatures', false),
            'show_class_highest_lowest'      => isset($optionsOverrides['show_class_highest_lowest']) ? (bool) $optionsOverrides['show_class_highest_lowest'] : $this->settingBool('rc_show_class_highest_lowest', false),
            'show_subject_teacher_remarks'   => isset($optionsOverrides['show_subject_teacher_remarks']) ? (bool) $optionsOverrides['show_subject_teacher_remarks'] : $this->settingBool('rc_show_subject_teacher_remarks', false),
            'show_qr_code'                   => isset($optionsOverrides['show_qr_code']) ? (bool) $optionsOverrides['show_qr_code'] : $this->settingBool('rc_show_qr_code', true),
            'show_cumulative_summary'        => isset($optionsOverrides['show_cumulative_summary']) ? (bool) $optionsOverrides['show_cumulative_summary'] : $this->settingBool('rc_show_cumulative_summary', false),
            'show_color_badges'              => isset($optionsOverrides['show_color_badges']) ? (bool) $optionsOverrides['show_color_badges'] : $this->settingBool('rc_show_color_badges', true),
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

        // Restore the student's actual registered class and section so the
        // report card header shows the correct class/section name (e.g. "Basic 5 Abyad")
        // rather than the class that was active when scores were entered.
        $student->class_id   = $displayClassId;
        $student->section_id = $displaySectionId ?: null;
        $student->load(['schoolClass', 'section']);

        $rawSettings = $this->settings();
        $principalName = $rawSettings['rc_principal_name'] ?? config('academyhub.rc_principal_name');
        $principalTitle = $rawSettings['rc_principal_title'] ?? config('academyhub.rc_principal_title', 'Principal');

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
            'teacherRemarks' => $teacherRemarks,
            'nextTermDate' => null,
            'rcOptions' => $rcOptions,
            'schoolFees' => $schoolFees,
            'signatureImages' => $signatureImages,
            'cumulativeSummary' => $cumulativeSummary,
            'principalName' => $principalName,
            'principalTitle' => $principalTitle,
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

        return $this->subjectsCache[$classId] = \App\Models\SchoolClass::allSubjectsForClass($classId);
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

    private function generateAIRemarks(
        Student $student,
        Collection $rows,
        float $average,
        int $position,
        int $totalStudents,
        ?int $timesOpened,
        ?int $timesPresent,
        ?int $timesAbsent,
        string $role
    ): ?string {
        $raw = config('services.groq.key');
        if (empty($raw)) {
            return null;
        }

        $keys = array_filter(array_map('trim', explode(',', $raw)));
        if (empty($keys)) {
            return null;
        }
        $apiKey = $keys[array_rand($keys)];

        // Format the subject grades list for the AI context
        $subjectsList = $rows->map(function ($r) {
            $name = $r['subject']?->name ?? 'Unknown Subject';
            $score = $r['total'] ?? 'N/A';
            $grade = $r['grade'] ?? 'N/A';
            return "{$name}: {$score}/100 (Grade {$grade})";
        })->implode(', ');

        $opened = (int) $timesOpened;
        $present = (int) $timesPresent;
        $absent = (int) $timesAbsent;
        $attendanceText = $opened > 0 ? "Present {$present} days out of {$opened} school days opened." : "Attendance not recorded.";

        if ($role === 'teacher') {
            $systemInstruction = "You are a professional class teacher. Write a concise, personalized, and encouraging comment (exactly 1-2 sentences) for a student's terminal report card. Identify their main strength (best subject) and area for improvement (lowest subject) based on their grades. Mention their attendance if they have been absent. Output ONLY the plain comment, no prefixes, no quotation marks.";
            $prompt = "Student: {$student->full_name}\n" .
                      "Grades: {$subjectsList}\n" .
                      "Attendance: {$attendanceText}";
        } else {
            $systemInstruction = "You are a school principal. Write a concise, authoritative, and encouraging remark (exactly 1-2 sentences) for a student's terminal report card. Reference their overall academic performance (average score {$average}%, position {$position} out of {$totalStudents} students). Output ONLY the plain remark, no prefixes, no quotation marks.";
            $prompt = "Student: {$student->full_name}\n" .
                      "Overall Average: {$average}%\n" .
                      "Class Position: {$position} out of {$totalStudents} students";
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => 'llama-3.3-70b-versatile',
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemInstruction],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.6,
                    'max_tokens'  => 150,
                ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? null;
                if ($content) {
                    return trim(strip_tags($content), " \t\n\r\0\x0B\"'");
                }
            }
        } catch (\Throwable) {
            // Fall back to rule-based remarks
        }

        return null;
    }

    private function generateTeacherRemarksFallback(float $average, int $position, int $totalStudents): string
    {
        if ($average >= 70) {
            return 'An exceptionally brilliant term. Has shown outstanding work ethic and active participation in class.';
        }

        if ($average >= 60) {
            return 'A very good performance. Consistent and dedicated. Keep up the high standard.';
        }

        if ($average >= 50) {
            return 'A satisfactory result. Shows good potential, but needs to be more consistent to achieve top grades.';
        }

        if ($average >= 40) {
            return 'Fair performance. Needs to show more dedication and put in extra study hours next term.';
        }

        return 'Poor academic standing. Requires intensive home study and close monitoring to improve.';
    }
}
