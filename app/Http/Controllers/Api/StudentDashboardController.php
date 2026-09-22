<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\Score;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $student = $request->user(); // auth:sanctum resolves Student model!

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $currentSession = AcademicTerm::activeSessionName() ?? config('academyhub.current_session', '');
        $currentTerm = AcademicTerm::activeTermNumber();

        $stats = [
            'current_session' => $currentSession,
            'current_term'    => $currentTerm,
        ];

        // Attendance stats
        $totalAttendance = AttendanceMark::where('student_id', $student->id)
            ->whereHas('sheet', function ($query) use ($currentSession, $currentTerm) {
                $query->where('session', $currentSession)
                    ->where('term', $currentTerm);
            })
            ->count();

        $presentCount = AttendanceMark::where('student_id', $student->id)
            ->where('status', 'Present')
            ->whereHas('sheet', function ($query) use ($currentSession, $currentTerm) {
                $query->where('session', $currentSession)
                    ->where('term', $currentTerm);
            })
            ->count();

        $stats['attendance_rate'] = $totalAttendance > 0
            ? round(($presentCount / $totalAttendance) * 100, 1)
            : 0;
        $stats['total_days'] = $totalAttendance;
        $stats['present_days'] = $presentCount;

        // Academic performance
        $scores = Score::where('student_id', $student->id)
            ->where('session', $currentSession)
            ->where('term', $currentTerm)
            ->get();

        $stats['total_subjects'] = $scores->count();
        $stats['average_score'] = $scores->count() > 0
            ? round($scores->avg('total'), 1)
            : 0;

        // Grade distribution
        $stats['grades'] = $scores->groupBy('grade')->map->count()->toArray();

        // Position in class
        $classScores = Score::where('class_id', $student->class_id)
            ->where('session', $currentSession)
            ->where('term', $currentTerm)
            ->selectRaw('student_id, SUM(total) as total_score')
            ->groupBy('student_id')
            ->orderByDesc('total_score')
            ->get();

        $position = $classScores->search(function ($item) use ($student) {
            return $item->student_id == $student->id;
        });

        $stats['position'] = $position !== false ? $position + 1 : null;
        $stats['total_students'] = $classScores->count();

        // Homework stats
        $homework = $student->getHomeworkForStudent();
        $stats['pending_homework'] = $homework->filter(function ($hw) {
            return $hw->submissions->isEmpty() && $hw->due_date >= now();
        })->count();

        $stats['overdue_homework'] = $homework->filter(function ($hw) {
            return $hw->submissions->isEmpty() && $hw->due_date < now();
        })->count();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class' => $student->schoolClass?->name,
                'section' => $student->section?->name,
            ],
            'stats' => $stats,
        ]);
    }
}
