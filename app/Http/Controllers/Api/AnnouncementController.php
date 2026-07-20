<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        if (Announcement::count() === 0) {
            Announcement::create([
                'title' => 'Annual Inter-House Sports & Athletic Competition',
                'body' => 'We are excited to announce our Annual Sports Day taking place next Friday. All parents and guardians are cordially invited.',
                'audience' => 'all',
                'published_at' => now(),
                'created_by' => 1,
            ]);
            Announcement::create([
                'title' => 'Parent-Teacher Academic Progress Conference',
                'body' => 'Parent-Teacher conference for Term 2 will hold on Saturday at 10:00 AM. Class report cards will be reviewed in detail.',
                'audience' => 'all',
                'published_at' => now(),
                'created_by' => 1,
            ]);
            Announcement::create([
                'title' => 'Term 2 Mid-Term Break Notice',
                'body' => 'The school will be closed for mid-term break starting Thursday. Normal classes resume on Monday at 08:00 AM.',
                'audience' => 'all',
                'published_at' => now(),
                'created_by' => 1,
            ]);
        }

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
