<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = TimetableEntry::with(['subject:id,name', 'teacher:id,name', 'schoolClass:id,name'])
            ->orderBy('day_of_week')->orderBy('starts_at');

        if ($user->role === 'teacher') {
            $query->where('teacher_id', $user->id);
        } elseif ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
            abort_unless($student, 403);
            $query->where('class_id', $student->class_id);
        } elseif ($user->role === 'parent') {
            $classIds = $user->students()->pluck('class_id');
            $query->whereIn('class_id', $classIds);
        }

        return response()->json(['data' => $query->get()]);
    }
}
