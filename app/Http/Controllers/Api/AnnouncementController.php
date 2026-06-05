<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::query()->latest('created_at');
        $announcements = $query->get();
        return response()->json(['data' => $announcements]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'audience' => 'required|string|in:all,parents,students,staff',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'body' => $request->body,
            'audience' => $request->audience,
            'published_at' => now(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Announcement published successfully.',
            'data' => $announcement
        ], 201);
    }
}
