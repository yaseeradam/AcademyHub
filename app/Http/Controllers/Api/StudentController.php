<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Score;
use App\Models\Student;
use App\Models\SubjectAllocation;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Student::with(['schoolClass', 'section'])->where('status', 'Active')->orderBy('last_name');

        if ($user->role === 'parent') {
            $childIds = $user->students()->pluck('students.id');
            $query->whereIn('id', $childIds);
        } elseif ($user->role === 'student') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'teacher') {
            $classIds = SubjectAllocation::where('teacher_id', $user->id)->distinct()->pluck('class_id');
            $query->whereIn('class_id', $classIds);
        }

        return response()->json($query->paginate(20));
    }

    public function reportCard(Request $request, int $id)
    {
        $user    = $request->user();
        $student = Student::with(['schoolClass', 'section'])->findOrFail($id);

        // Authorization
        if ($user->role === 'parent') {
            abort_unless($user->students()->where('students.id', $id)->exists(), 403);
        } elseif ($user->role === 'student') {
            abort_unless($student->user_id === $user->id, 403);
        } elseif ($user->role === 'teacher') {
            $classIds = SubjectAllocation::where('teacher_id', $user->id)->distinct()->pluck('class_id')->toArray();
            abort_unless(in_array($student->class_id, $classIds), 403);
        }

        $term    = (int) $request->query('term', AcademicTerm::activeTermNumber());
        $session = $request->query('session', AcademicTerm::activeSessionName());

        $scores = Score::where('student_id', $id)
            ->where('term', $term)
            ->where('session', $session)
            ->with('subject:id,name')
            ->get()
            ->map(fn($s) => [
                'subject' => $s->subject?->name ?? 'Unknown',
                'ca1'     => $s->ca1,
                'ca2'     => $s->ca2,
                'exam'    => $s->exam,
                'total'   => $s->total,
                'grade'   => $s->grade,
            ]);

        return response()->json([
            'data' => [
                'student'    => $student,
                'session'    => $session,
                'term'       => $term,
                'subjects'   => $scores,
            ],
        ]);
    }
}
