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
}
