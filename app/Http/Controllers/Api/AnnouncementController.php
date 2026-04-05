<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        // For now, load all valid announcements
        $query = Announcement::query()
            ->latest('created_at');
            
        $announcements = $query->paginate(20);
        return response()->json($announcements);
    }
}
