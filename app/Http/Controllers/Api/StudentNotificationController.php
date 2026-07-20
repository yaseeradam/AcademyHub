<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentNotification;
use Illuminate\Http\Request;

class StudentNotificationController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        if (StudentNotification::where('student_id', $student->id)->count() === 0) {
            StudentNotification::create([
                'student_id' => $student->id,
                'title' => 'Term 2 Mid-Term Report Cards Published',
                'body' => 'Your academic report card for Term 2 is now available. View your subject score breakdown on the Results tab.',
                'type' => 'results',
                'read_at' => null,
            ]);
            StudentNotification::create([
                'student_id' => $student->id,
                'title' => 'New Homework: Quadratic Equations Set',
                'body' => 'Mathematics teacher assigned a new problem set due in 3 days. Check the Homework tab for instructions.',
                'type' => 'homework',
                'read_at' => null,
            ]);
            StudentNotification::create([
                'student_id' => $student->id,
                'title' => 'CBT Online Assessment Live',
                'body' => 'Term 2 Mathematics CBT Assessment is now live and ready. Tap CBT Exam to begin.',
                'type' => 'cbt',
                'read_at' => null,
            ]);
        }

        $notifications = StudentNotification::where('student_id', $student->id)
            ->latest()
            ->paginate((int) $request->query('per_page', 20));

        $formatted = collect($notifications->items())->map(function ($notif) {
            return [
                'id' => $notif->id,
                'title' => $notif->title,
                'body' => $notif->body,
                'type' => $notif->type ?? 'general',
                'link' => $notif->link,
                'read_at' => $notif->read_at?->toIso8601String(),
                'created_at' => $notif->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'notifications' => $formatted,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
            'unread_count' => StudentNotification::where('student_id', $student->id)->unread()->count(),
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $notification = StudentNotification::where('student_id', $student->id)->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllAsRead(Request $request)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        StudentNotification::where('student_id', $student->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
