<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaUploadController extends Controller
{
    public function upload(Request $request)
    {
        $user = $request->user();

        // Check if user has permission to upload (teacher or admin or student)
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'file' => 'required|file|max:20480|mimes:jpg,jpeg,png,gif,webp,bmp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,mp4,mp3,zip', // 20MB limit, restricted file types
            'type' => 'required|string|in:note,assignment,profile_photo',
        ]);

        $file = $request->file('file');
        $type = $request->type;

        $folder = match ($type) {
            'note' => 'class-notes',
            'assignment' => 'homework-attachments',
            'profile_photo' => 'passport-photos',
            default => 'uploads',
        };

        try {
            $path = $file->store($folder, 'public');
            $url = asset('storage/' . $path);
            $size = $file->getSize();
            $originalName = $file->getClientOriginalName();

            return response()->json([
                'url' => $url,
                'path' => $path, // Relative path stored in database
                'size' => $size,
                'file_name' => $originalName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
