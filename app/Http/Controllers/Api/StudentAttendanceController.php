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
            $noteText = (string) ($mark->note ?? '');
            $hasBioNote = !empty($noteText) && !str_contains($noteText, 'No biometric') && (
                str_contains($noteText, 'Biometric') || str_contains($noteText, 'Arrived:') || str_contains($noteText, 'K40')
            );

            $displayNote = $mark->note;
            if (in_array($mark->status, ['Present', 'Late'], true) && str_contains(strtolower($displayNote ?? ''), 'absent')) {
                $displayNote = null;
            }

            return [
                'id' => $mark->id,
                'date' => $mark->sheet?->date?->toDateString(),
                'status' => $mark->status,
                'note' => $displayNote,
                'arrived_at' => $mark->arrived_at,
                'departed_at' => $mark->departed_at,
                'is_biometric' => !empty($mark->arrived_at) && ($hasBioNote || !empty($mark->departed_at)),
                'taken_by' => $mark->sheet?->takenBy?->name ?? 'Class Roll Call',
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
