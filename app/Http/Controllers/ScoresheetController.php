<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScoresheetController extends Controller
{
    public function download(Request $request): Response
    {
        try {
            // Simple validation first
            if (!$request->has(['class_id', 'subject_id', 'term', 'session'])) {
                return response()->json(['error' => 'Missing required parameters'], 400);
            }

            $user = auth()->user();
            if (!$user || !$user->hasPermission('results.entry')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // For now, let's return a simple response to test if the route works
            return response()->json([
                'message' => 'Scoresheet download endpoint reached successfully',
                'params' => $request->all(),
                'user' => $user->name,
                'timestamp' => now()
            ]);
            
            // TODO: Uncomment the PDF generation code below once basic routing works
            /*
            $request->validate([
                'class_id' => 'required|integer|exists:school_classes,id',
                'subject_id' => 'required|integer|exists:subjects,id',
                'term' => 'required|integer|between:1,3',
                'session' => 'required|string|max:9',
            ]);

            $classId = (int) $request->class_id;
            $subjectId = (int) $request->subject_id;
            $term = (int) $request->term;
            $session = trim($request->session);

            // Check teacher permissions
            if ($user->role === 'teacher') {
                $allowed = SubjectAllocation::query()
                    ->where('teacher_id', $user->id)
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->exists();

                if (!$allowed) {
                    return response()->json(['error' => 'Not authorized for this class/subject'], 403);
                }
            }

            $class = SchoolClass::findOrFail($classId);
            $subject = Subject::findOrFail($subjectId);

            $students = Student::query()
                ->where('class_id', $classId)
                ->where('status', 'Active')
                ->orderBy('last_name')
                ->get();

            $scores = Score::query()
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('term', $term)
                ->where('session', $session)
                ->get()
                ->keyBy('student_id');

            $maxMarks = [
                'ca1' => max(0, (int) config('myacademy.results_ca1_max', 20)),
                'ca2' => max(0, (int) config('myacademy.results_ca2_max', 20)),
                'exam' => max(0, (int) config('myacademy.results_exam_max', 60)),
            ];

            $data = [
                'schoolName' => config('myacademy.school_name', config('app.name', 'School')),
                'schoolAddress' => config('myacademy.school_address', ''),
                'schoolLogo' => config('myacademy.school_logo') ? public_path('uploads/' . config('myacademy.school_logo')) : null,
                'class' => $class,
                'subject' => $subject,
                'term' => $term,
                'session' => $session,
                'students' => $students,
                'scores' => $scores,
                'maxMarks' => $maxMarks,
            ];

            $pdf = Pdf::loadView('pdf.scoresheet', $data)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif'
                ]);

            $filename = "scoresheet_{$class->name}_{$subject->name}_T{$term}_{$session}.pdf";
            $filename = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $filename);

            return $pdf->download($filename);
            */
            
        } catch (\Exception $e) {
            \Log::error('Scoresheet download error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate scoresheet: ' . $e->getMessage()], 500);
        }
    }
}