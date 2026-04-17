<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\SubjectAllocation;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    /** GET /api/homework?class_id=1&term=1&session=... */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Homework::with(['subject:id,name', 'teacher:id,name'])
            ->orderByDesc('due_date');

        if ($user->role === 'teacher') {
            $query->where('teacher_id', $user->id);
            if ($request->class_id) $query->where('class_id', $request->class_id);
        } elseif ($user->role === 'student') {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            abort_unless($student, 403);
            $query->where('class_id', $student->class_id);
        } elseif ($user->role === 'parent') {
            $childIds   = $user->students()->pluck('students.id');
            $classIds   = \App\Models\Student::whereIn('id', $childIds)->pluck('class_id');
            $query->whereIn('class_id', $classIds);
        }

        if ($request->term)    $query->whereHas('subject', fn($q) => $q); // passthrough
        if ($request->session) {} // future filter

        return response()->json(['data' => $query->get()]);
    }

    /** POST /api/homework */
    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['teacher', 'admin']), 403);

        $data = $request->validate([
            'class_id'   => 'required|integer|exists:classes,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'due_date'   => 'required|date',
            'section_id' => 'nullable|integer',
        ]);

        if ($user->role === 'teacher') {
            $allowed = SubjectAllocation::where('teacher_id', $user->id)
                ->where('class_id', $data['class_id'])
                ->where('subject_id', $data['subject_id'])
                ->exists();
            abort_unless($allowed, 403, 'Not assigned to this class/subject.');
        }

        $homework = Homework::create(array_merge($data, ['teacher_id' => $user->id]));

        return response()->json(['data' => $homework->load(['subject:id,name', 'teacher:id,name'])], 201);
    }

    /** PUT /api/homework/{id} */
    public function update(Request $request, int $id)
    {
        $user     = $request->user();
        $homework = Homework::findOrFail($id);
        abort_unless($user->role === 'admin' || $homework->teacher_id === $user->id, 403);

        $homework->update($request->only(['title', 'content', 'due_date']));

        return response()->json(['data' => $homework]);
    }

    /** DELETE /api/homework/{id} */
    public function destroy(Request $request, int $id)
    {
        $user     = $request->user();
        $homework = Homework::findOrFail($id);
        abort_unless($user->role === 'admin' || $homework->teacher_id === $user->id, 403);
        $homework->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    /** GET /api/homework/{id}/submissions */
    public function submissions(Request $request, int $id)
    {
        $user     = $request->user();
        $homework = Homework::findOrFail($id);
        abort_unless($user->role === 'admin' || $homework->teacher_id === $user->id, 403);

        return response()->json([
            'data' => $homework->submissions()->with('student:id,first_name,last_name,admission_number')->get(),
        ]);
    }

    /** POST /api/homework/{id}/submit — student submits */
    public function submit(Request $request, int $id)
    {
        $user    = $request->user();
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        abort_unless($student, 403);

        $data = $request->validate([
            'submission' => 'required|string',
        ]);

        $sub = HomeworkSubmission::updateOrCreate(
            ['homework_id' => $id, 'student_id' => $student->id],
            ['submission' => $data['submission'], 'submitted_at' => now()]
        );

        return response()->json(['data' => $sub], 201);
    }

    /** POST /api/homework/{id}/grade — teacher grades */
    public function grade(Request $request, int $id)
    {
        $user     = $request->user();
        $homework = Homework::findOrFail($id);
        abort_unless($user->role === 'admin' || $homework->teacher_id === $user->id, 403);

        $data = $request->validate([
            'student_id' => 'required|integer',
            'grade'      => 'required|string|max:10',
            'feedback'   => 'nullable|string',
        ]);

        $sub = HomeworkSubmission::where('homework_id', $id)
            ->where('student_id', $data['student_id'])
            ->firstOrFail();

        $sub->update(['grade' => $data['grade'], 'feedback' => $data['feedback'], 'graded_at' => now()]);

        return response()->json(['data' => $sub]);
    }
}
