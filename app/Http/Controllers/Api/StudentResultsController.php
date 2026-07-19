<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Score;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use Illuminate\Http\Request;

class StudentResultsController extends Controller
{
    public function results(Request $request)
    {
        $student = $request->user();

        if (!$student || !($student instanceof \App\Models\Student)) {
            return response()->json(['message' => 'Unauthorized or invalid student context.'], 403);
        }

        $session = $request->query('session', AcademicTerm::activeSessionName() ?? config('academyhub.current_session', ''));
        $term = (int) $request->query('term', AcademicTerm::activeTermNumber());

        // Get class subjects assigned or allocated
        $subjects = \App\Models\SchoolClass::allSubjectsForClass($student->class_id);

        $scores = Score::with('subject')
            ->where('student_id', $student->id)
            ->where('class_id', $student->class_id)
            ->where('term', $term)
            ->where('session', $session)
            ->get()
            ->keyBy('subject_id');

        // All class scores for calculating averages/rankings
        $allClassScores = Score::where('class_id', $student->class_id)
            ->where('term', $term)
            ->where('session', $session)
            ->get(['subject_id', 'student_id', 'total']);

        $subjectClassAvgs = $allClassScores
            ->groupBy('subject_id')
            ->map(fn ($rows) => round($rows->avg('total'), 1));

        $subjectPositions = $allClassScores
            ->groupBy('subject_id')
            ->map(function ($rows) use ($student) {
                $sorted = $rows->sortByDesc('total')->values();
                $rank = 1; $last = null; $pos = 1;
                foreach ($sorted as $i => $r) {
                    if ($last !== null && $r->total !== $last) { $rank = $i + 1; }
                    if ((int) $r->student_id === (int) $student->id) { $pos = $rank; break; }
                    $last = $r->total;
                }
                return $pos;
            });

        $rows = $subjects->map(function (Subject $subject) use ($scores, $subjectClassAvgs, $subjectPositions) {
            $score = $scores->get($subject->id);

            return [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'ca1' => $score?->ca1 ?? null,
                'ca2' => $score?->ca2 ?? null,
                'exam' => $score?->exam ?? null,
                'total' => $score?->total ?? null,
                'grade' => $score?->grade ?? ($score ? Score::gradeForTotal((int) $score->total, max(0, (int) config('academyhub.results_ca1_max', 20)) + max(0, (int) config('academyhub.results_ca2_max', 20)) + max(0, (int) config('academyhub.results_exam_max', 60))) : null),
                'class_avg' => $subjectClassAvgs->get($subject->id) ?? null,
                'position' => $score ? ($subjectPositions->get($subject->id) ?? null) : null,
            ];
        })->values();

        $publicationRecord = \App\Models\ResultPublication::where('class_id', $student->class_id)
            ->where('term', $term)
            ->where('session', $session)
            ->first();

        $published = ($publicationRecord && $publicationRecord->published_at !== null) || $scores->count() > 0;

        // Calculate summary
        $grandTotal = $rows->sum('total');
        $validScoreCount = $rows->filter(fn($r) => $r['total'] !== null)->count();
        $average = $validScoreCount > 0 ? round($grandTotal / $validScoreCount, 2) : 0;

        return response()->json([
            'session' => $session,
            'term' => $term,
            'is_published' => $published,
            'results' => $rows,
            'summary' => [
                'grand_total' => $grandTotal,
                'average' => $average,
                'total_subjects' => $subjects->count(),
                'graded_subjects' => $validScoreCount,
            ],
        ]);
    }
}
