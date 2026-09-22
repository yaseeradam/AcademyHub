<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperadminNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationsController extends Controller
{
    /** Return the latest notifications as JSON for the bell dropdown. */
    public function index(): JsonResponse
    {
        $notifications = SuperadminNotification::with('tenant')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'action_url' => $n->action_url,
                'read_at'    => $n->read_at?->toISOString(),
                'created_at' => $n->created_at->diffForHumans(),
                'tenant'     => $n->tenant?->name,
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => SuperadminNotification::unreadCount(),
        ]);
    }

    /** Mark a single notification as read. */
    public function markRead(SuperadminNotification $notification): JsonResponse
    {
        $notification->update(['read_at' => now()]);
        return response()->json(['unread_count' => SuperadminNotification::unreadCount()]);
    }

    /** Mark ALL notifications as read. */
    public function markAllRead(): JsonResponse
    {
        SuperadminNotification::whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['unread_count' => 0]);
    }

    /** Render a paginated list of all superadmin notifications. */
    public function listView()
    {
        $notifications = SuperadminNotification::with('tenant')
            ->latest()
            ->paginate(15);

        return view('superadmin.notifications.index', compact('notifications'));
    }

    /** Mark the notification as read and redirect to its action URL. */
    public function open(SuperadminNotification $notification)
    {
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return redirect()->route('superadmin.notifications.list')->with('status', 'Notification marked as read.');
    }
}
