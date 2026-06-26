<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassNote;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentELearningController extends Controller
{
    public function notes(Request $request)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $query = ClassNote::with(['subject', 'user'])
            ->where('class_id', $student->class_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        if ($request->filled('term_name')) {
            $query->where('term_name', $request->term_name);
        }

        $notes = $query->latest()->get();

        $formattedNotes = $notes->map(function ($note) {
            return [
                'id' => $note->id,
                'title' => $note->title,
                'description' => $note->description,
                'term_name' => $note->term_name,
                'subject_id' => $note->subject_id,
                'subject_name' => $note->subject?->name,
                'uploaded_by' => $note->user?->name,
                'file_name' => $note->file_name,
                'file_size' => $note->file_size,
                'downloads' => $note->downloads,
                'created_at' => $note->created_at->toIso8601String(),
                'download_url' => route('api.student.notes.download', $note->id),
            ];
        });

        // Filter subjects list
        $subjectIds = ClassNote::where('class_id', $student->class_id)
            ->pluck('subject_id')
            ->unique();
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'notes' => $formattedNotes,
            'subjects' => $subjects,
        ]);
    }

    public function download(Request $request, $id)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $note = ClassNote::where('class_id', $student->class_id)->findOrFail($id);

        if (empty($note->file_path) || !Storage::disk('public')->exists($note->file_path)) {
            return response()->json(['message' => 'File not found on server.'], 404);
        }

        $note->increment('downloads');

        return Storage::disk('public')->download($note->file_path, $note->file_name);
    }
}
