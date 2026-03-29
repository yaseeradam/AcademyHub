<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Support\ReportCardService;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Student::with(['schoolClass', 'section'])->orderBy('last_name');

        // Role-based filtering
        if ($user->role === 'parent') {
            // Future phase 6 implementation for parents seeing only their kids
            // $query->whereHas('parents', function($q) use ($user) {
            //     $q->where('users.id', $user->id);
            // });
        } elseif ($user->role === 'teacher') {
            // Future logic for teacher seeing only their class, but for now they can see all
        } elseif ($user->role === 'student') {
            // A student user could only see themselves
            // $query->where('user_id', $user->id);
        }

        $students = $query->paginate(20);

        return response()->json($students);
    }

    public function reportCard(Request $request, int $id, ReportCardService $reportCardService)
    {
        // Simple permission check (in a real app, use Policies)
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'teacher', 'parent', 'student'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = Student::with(['schoolClass', 'section'])->findOrFail($id);
        
        // This is a mockup of fetching the report card data as JSON instead of PDF
        $options = $reportCardService->build();
        
        // Dummy data structure for the app to consume
        $data = [
            'student' => $student,
            'session' => $request->query('session', '2025/2026'),
            'term' => $request->query('term', 1),
            'options' => $options,
            'subjects' => [
                // Real implementation would gather Results model here
                ['subject' => 'Mathematics', 'ca1' => 15, 'ca2' => 10, 'exam' => 60, 'total' => 85, 'grade' => 'A'],
                ['subject' => 'English', 'ca1' => 12, 'ca2' => 15, 'exam' => 50, 'total' => 77, 'grade' => 'B'],
                ['subject' => 'Science', 'ca1' => 18, 'ca2' => 18, 'exam' => 60, 'total' => 96, 'grade' => 'A+'],
            ],
            'attendance' => 95,
            'remarks' => [
                'teacher' => 'Excellent performance.',
                'principal' => 'Keep it up.'
            ]
        ];

        return response()->json(['data' => $data]);
    }
}
