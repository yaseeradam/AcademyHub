<?php

namespace App\Support;

use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use Illuminate\Support\Collection;

class ReportCardService
{
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
    public function build(Student $student, int $term, string $session): array
    {
        $student->load(['schoolClass', 'section']);

        $subjects = $this->subjectsForClass($student->class_id);
        $subjectIds = $subjects->pluck('id');
        $subjectCount = max(1, (int) $subjectIds->count());

        $scores = Score::query()
            ->with('subject')
            ->where('student_id', $student->id)
            ->where('class_id', $student->class_id)
            ->where('term', $term)
            ->where('session', $session)
            ->whereIn('subject_id', $subjectIds)
            ->get()
            ->keyBy('subject_id');

        $rows = $subjects->map(function (Subject $subject) use ($scores) {
            /** @var \App\Models\Score|null $score */
            $score = $scores->get($subject->id);

            return [
                'subject' => $subject,
                'ca1' => $score?->ca1 ?? null,
                'ca2' => $score?->ca2 ?? null,
                'exam' => $score?->exam ?? null,
                'total' => $score?->total ?? null,
                'grade' => $score?->grade ?? ($score ? Score::gradeForTotal((int) $score->total, max(0, (int) config('myacademy.results_ca1_max', 20)) + max(0, (int) config('myacademy.results_ca2_max', 20)) + max(0, (int) config('myacademy.results_exam_max', 60))) : null),
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

        $totalStudents = Student::query()
            ->where('class_id', $student->class_id)
            ->where('status', 'Active')
            ->count();

        [$highestAverage, $lowestAverage] = $this->highestLowestAverage($student->class_id, $subjectIds, $term, $session);

        $principalRemarks = $this->generatePrincipalRemarks($average, $position, $totalStudents);

        // Report card display options from settings
        $rcOptions = [
            'show_position' => (bool) config('myacademy.rc_show_position', true),
            'show_attendance' => (bool) config('myacademy.rc_show_attendance', true),
            'show_grading_key' => (bool) config('myacademy.rc_show_grading_key', true),
            'show_class_average' => (bool) config('myacademy.rc_show_class_average', true),
            'show_watermark' => (bool) config('myacademy.rc_show_watermark', true),
            'show_next_term_date' => (bool) config('myacademy.rc_show_next_term_date', true),
            'show_teacher_remarks' => (bool) config('myacademy.rc_show_teacher_remarks', true),
            'show_principal_remarks' => (bool) config('myacademy.rc_show_principal_remarks', true),
            'show_psychomotor' => (bool) config('myacademy.rc_show_psychomotor', false),
            'show_school_fees' => (bool) config('myacademy.rc_show_school_fees', false),
            'show_signatures' => (bool) config('myacademy.rc_show_signatures', false),
        ];

        // School fees data
        $schoolFees = null;
        if ($rcOptions['show_school_fees']) {
            $feesByClass = config('myacademy.rc_school_fees_by_class');
            if (is_string($feesByClass)) {
                $feesByClass = json_decode($feesByClass, true) ?? [];
            }
            $feesByClass = is_array($feesByClass) ? $feesByClass : [];
            $classId = $student->class_id;
            $feeAmount = $feesByClass[(string) $classId] ?? null;

            if ($feeAmount !== null) {
                $schoolFees = [
                    'amount' => (float) $feeAmount,
                    'account_number' => config('myacademy.rc_school_fees_account_number'),
                    'bank_name' => config('myacademy.rc_school_fees_bank_name'),
                    'account_name' => config('myacademy.rc_school_fees_account_name'),
                    'currency' => config('myacademy.currency_symbol', '₦'),
                ];
            }
        }

        // Signature images
        $signatureImages = null;
        if ($rcOptions['show_signatures']) {
            $principalSig = config('myacademy.rc_principal_signature_image');
            $teacherSig = config('myacademy.rc_teacher_signature_image');
            $signatureImages = [
                'principal' => $principalSig ? public_path('uploads/' . str_replace('\\', '/', $principalSig)) : null,
                'teacher' => $teacherSig ? public_path('uploads/' . str_replace('\\', '/', $teacherSig)) : null,
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
        $ids = SubjectAllocation::query()
            ->where('class_id', $classId)
            ->pluck('subject_id')
            ->unique();

        if ($ids->isEmpty()) {
            return Subject::query()->orderBy('name')->get();
        }

        return Subject::query()->whereIn('id', $ids)->orderBy('name')->get();
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
        $scores = Score::query()
            ->where('class_id', $classId)
            ->where('term', $term)
            ->where('session', $session)
            ->whereIn('subject_id', $subjectIds)
            ->get(['student_id', 'total']);

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
        $scores = Score::query()
            ->where('class_id', $classId)
            ->where('term', $term)
            ->where('session', $session)
            ->whereIn('subject_id', $subjectIds)
            ->get(['student_id', 'total']);

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
}
