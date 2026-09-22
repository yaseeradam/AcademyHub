<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Score;
use App\Models\Student;
use App\Models\SubjectAllocation;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Student::with(['schoolClass', 'section'])->where('status', 'Active')->orderBy('last_name');

        if ($user->role === 'parent') {
            $childIds = $user->students()->pluck('students.id');
            $query->whereIn('id', $childIds);
        } elseif ($user->role === 'student') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'teacher') {
            $classIds = SubjectAllocation::where('teacher_id', $user->id)->distinct()->pluck('class_id');
            $query->whereIn('class_id', $classIds);
        }

        return response()->json($query->paginate(20));
    }

    public function reportCard(Request $request, int $id)
    {
        $user    = $request->user();
        $student = Student::with(['schoolClass', 'section'])->findOrFail($id);

        // Authorization
        if ($user->role === 'parent') {
            abort_unless($user->students()->where('students.id', $id)->exists(), 403);
        } elseif ($user->role === 'student') {
            abort_unless($student->user_id === $user->id, 403);
        } elseif ($user->role === 'teacher') {
            $classIds = SubjectAllocation::where('teacher_id', $user->id)->distinct()->pluck('class_id')->toArray();
            abort_unless(in_array($student->class_id, $classIds), 403);
        }

        $term    = (int) $request->query('term', AcademicTerm::activeTermNumber());
        $session = $request->query('session', AcademicTerm::activeSessionName());

        $published = true;
        if ($user->role === 'parent' || $user->role === 'student') {
            $published = \App\Models\ResultPublication::where('class_id', $student->class_id)
                ->where('term', $term)
                ->where('session', $session)
                ->whereNotNull('published_at')
                ->exists();
        }

        $scores = $published ? Score::where('student_id', $id)
            ->where('term', $term)
            ->where('session', $session)
            ->with('subject:id,name')
            ->get()
            ->map(fn($s) => [
                'subject' => $s->subject?->name ?? 'Unknown',
                'ca1'     => $s->ca1,
                'ca2'     => $s->ca2,
                'exam'    => $s->exam,
                'total'   => $s->total,
                'grade'   => $s->grade,
            ]) : collect();

        return response()->json([
            'data' => [
                'student'      => $student,
                'session'      => $session,
                'term'         => $term,
                'is_published' => $published,
                'subjects'     => $scores,
            ],
        ]);
    }

    public function details(Request $request, int $id)
    {
        $user    = $request->user();
        $student = Student::with(['schoolClass', 'section', 'user'])->findOrFail($id);

        // Authorization
        if ($user->role === 'parent') {
            abort_unless($user->students()->where('students.id', $id)->exists(), 403);
        } elseif ($user->role === 'student') {
            abort_unless($student->user_id === $user->id, 403);
        } elseif ($user->role === 'teacher') {
            $classIds = SubjectAllocation::where('teacher_id', $user->id)->distinct()->pluck('class_id')->toArray();
            abort_unless(in_array($student->class_id, $classIds), 403);
        }

        $term    = (int) $request->query('term', AcademicTerm::activeTermNumber());
        $session = $request->query('session', AcademicTerm::activeSessionName());

        // 1. Report card & subjects
        $published = true;
        if ($user->role === 'parent' || $user->role === 'student') {
            $published = \App\Models\ResultPublication::where('class_id', $student->class_id)
                ->where('term', $term)
                ->where('session', $session)
                ->whereNotNull('published_at')
                ->exists();
        }

        $scores = $published ? Score::where('student_id', $id)
            ->where('term', $term)
            ->where('session', $session)
            ->with('subject:id,name')
            ->get()
            ->map(fn($s) => [
                'subject' => $s->subject?->name ?? 'Unknown',
                'ca1'     => $s->ca1,
                'ca2'     => $s->ca2,
                'exam'    => $s->exam,
                'total'   => $s->total,
                'grade'   => $s->grade,
            ]) : collect();

        // 2. Attendance Stats & History
        $attendanceMarks = \App\Models\AttendanceMark::query()
            ->join('attendance_sheets', 'attendance_sheets.id', '=', 'attendance_marks.sheet_id')
            ->select('attendance_marks.*')
            ->with(['sheet' => fn($q) => $q->with(['schoolClass', 'section'])])
            ->where('attendance_marks.student_id', $id)
            ->orderByDesc('attendance_sheets.date')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'date'   => $m->sheet?->date,
                'status' => $m->status,
                'note'   => $m->note,
            ]);

        $attendanceCounts = \App\Models\AttendanceMark::query()
            ->join('attendance_sheets', 'attendance_sheets.id', '=', 'attendance_marks.sheet_id')
            ->where('attendance_marks.student_id', $id)
            ->selectRaw('attendance_marks.status, COUNT(*) as total')
            ->groupBy('attendance_marks.status')
            ->pluck('total', 'status');

        $presentCount = (int) ($attendanceCounts['Present'] ?? 0);
        $absentCount  = (int) ($attendanceCounts['Absent'] ?? 0);
        $lateCount    = (int) ($attendanceCounts['Late'] ?? 0);
        $totalDays    = $presentCount + $absentCount + $lateCount;
        $rate         = $totalDays > 0 ? round(($presentCount / $totalDays) * 100, 1) : 100;

        // 3. Financial transactions & balance
        $transactions = \App\Models\Transaction::where('student_id', $id)
            ->where('is_void', false)
            ->orderByDesc('date')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'reference'   => $t->reference ?? ('TXN-' . $t->id),
                'type'        => $t->type,
                'amount_paid' => (float) $t->amount_paid,
                'date'        => $t->date,
                'status'      => 'Paid',
            ]);

        $totalPaid = (float) \App\Models\Transaction::where('student_id', $id)
            ->where('type', 'Income')
            ->where('is_void', false)
            ->sum('amount_paid');

        $outstandingBalance = max(0, 45000 - $totalPaid);

        return response()->json([
            'data' => [
                'student'     => $student,
                'report_card' => [
                    'session'      => $session,
                    'term'         => $term,
                    'is_published' => $published,
                    'subjects'     => $scores,
                ],
                'attendance' => [
                    'rate'          => $rate,
                    'present_count' => $presentCount,
                    'absent_count'  => $absentCount,
                    'late_count'    => $lateCount,
                    'total_days'    => $totalDays,
                    'recent_logs'   => $attendanceMarks,
                ],
                'financials' => [
                    'total_paid'          => $totalPaid,
                    'outstanding_balance' => $outstandingBalance,
                    'transactions'        => $transactions,
                ],
            ],
        ]);
    }
}
