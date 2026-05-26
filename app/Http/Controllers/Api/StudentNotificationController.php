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
