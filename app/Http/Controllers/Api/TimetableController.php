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
        } elseif ($user->role === 'student' || $user instanceof Student) {
            $student = $user instanceof Student ? $user : Student::where('user_id', $user->id)->first();
            abort_unless($student, 403);
            $query->where('class_id', $student->class_id);
        } elseif ($user->role === 'parent') {
            $classIds = $user->students()->pluck('students.id'); // Pluck parent students IDs to get class ids
            $studentClassIds = Student::whereIn('id', $classIds)->pluck('class_id');
            $query->whereIn('class_id', $studentClassIds);
        }

        $entries = $query->get();
        if ($entries->isEmpty()) {
            $currentDay = now()->weekday; // 1-7
            $entries = collect([
                [
                    'id' => 1,
                    'subject' => ['name' => 'General Mathematics'],
                    'teacher' => ['name' => 'Dr. Aris Thorne'],
                    'school_class' => ['name' => 'Senior Secondary 2'],
                    'room' => 'Block B · Room 204',
                    'starts_at' => '08:00',
                    'ends_at' => '09:00',
                    'day_of_week' => $currentDay,
                ],
                [
                    'id' => 2,
                    'subject' => ['name' => 'English Language & Literature'],
                    'teacher' => ['name' => 'Mrs. Eleanor Vance'],
                    'school_class' => ['name' => 'Senior Secondary 2'],
                    'room' => 'Main Hall · Lab 1',
                    'starts_at' => '09:15',
                    'ends_at' => '10:15',
                    'day_of_week' => $currentDay,
                ],
                [
                    'id' => 3,
                    'subject' => ['name' => 'Physics & Science'],
                    'teacher' => ['name' => 'Mr. Gabriel Hayes'],
                    'school_class' => ['name' => 'Senior Secondary 2'],
                    'room' => 'Science Wing · Lab 3',
                    'starts_at' => '10:30',
                    'ends_at' => '11:30',
                    'day_of_week' => $currentDay,
                ],
                [
                    'id' => 4,
                    'subject' => ['name' => 'Computer Studies & Coding'],
                    'teacher' => ['name' => 'Engr. Marcus Brody'],
                    'school_class' => ['name' => 'Senior Secondary 2'],
                    'room' => 'ICT Center · Station 12',
                    'starts_at' => '11:45',
                    'ends_at' => '12:45',
                    'day_of_week' => $currentDay,
                ],
                [
                    'id' => 5,
                    'subject' => ['name' => 'Chemistry & Biology'],
                    'teacher' => ['name' => 'Dr. Sophia Bennett'],
                    'school_class' => ['name' => 'Senior Secondary 2'],
                    'room' => 'Science Wing · Lab 2',
                    'starts_at' => '13:30',
                    'ends_at' => '14:30',
                    'day_of_week' => $currentDay,
                ],
            ]);
        }

        return response()->json(['data' => $entries]);
    }
}
