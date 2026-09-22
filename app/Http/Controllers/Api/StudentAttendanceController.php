<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use Illuminate\Http\Request;

class StudentAttendanceController extends Controller
{
    public function attendance(Request $request)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $session = $request->query('session', AcademicTerm::activeSessionName() ?? config('academyhub.current_session', ''));
        $term = (int) $request->query('term', AcademicTerm::activeTermNumber());

        $marks = AttendanceMark::with(['sheet.takenBy'])
            ->where('student_id', $student->id)
            ->whereHas('sheet', function ($query) use ($session, $term) {
                $query->where('session', $session)
                    ->where('term', $term);
            })
            ->get();

        $totalDays = $marks->count();
        $presentDays = $marks->where('status', 'Present')->count();
        $absentDays = $marks->where('status', 'Absent')->count();
        $lateDays = $marks->where('status', 'Late')->count();
        $excusedDays = $marks->where('status', 'Excused')->count();

        $attendanceRate = $totalDays > 0
            ? round((($presentDays + $lateDays + $excusedDays) / $totalDays) * 100, 1)
            : 0;

        $history = $marks->map(function ($mark) {
            return [
                'id' => $mark->id,
                'date' => $mark->sheet?->date?->toDateString(),
                'status' => $mark->status,
                'note' => $mark->note,
                'taken_by' => $mark->sheet?->takenBy?->name ?? 'System',
            ];
        })->sortByDesc('date')->values();

        return response()->json([
            'session' => $session,
            'term' => $term,
            'summary' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'excused_days' => $excusedDays,
                'attendance_rate' => $attendanceRate,
            ],
            'history' => $history,
        ]);
    }
}
