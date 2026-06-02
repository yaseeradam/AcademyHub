<?php

namespace App\Support;

use App\Models\Student;
use App\Models\Score;
use App\Models\Subject;
use App\Models\AttendanceMark;
use App\Models\HomeworkSubmission;
use App\Models\CbtAttempt;
use App\Models\AcademicTerm;
use Illuminate\Support\Collection;

class StudentPerformanceService
{
    /**
     * Clear all performance caches for a specific student across common terms and sessions.
     */
    public static function clearCache(int $studentId): void
    {
        $currentYear = (int)date('Y');
        $sessions = [
            ($currentYear - 1) . '/' . $currentYear,
            $currentYear . '/' . ($currentYear + 1),
            ($currentYear + 1) . '/' . ($currentYear + 2),
        ];

        foreach ([1, 2, 3] as $term) {
            foreach ($sessions as $session) {
                $cacheKey = "student_performance:{$studentId}:term_{$term}:session_" . urlencode($session);
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }
        }
    }

    public function getPerformanceAnalysis(Student $student, ?int $termNumber = null, ?string $session = null): array
    {
        $currentTerm = AcademicTerm::active() ?? AcademicTerm::latest()->first();
        $termNumber = $termNumber ?? $currentTerm?->term_number ?? 1;
        $session = $session ?? $currentTerm?->academicSession?->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1);

        $cacheKey = "student_performance:{$student->id}:term_{$termNumber}:session_" . urlencode($session);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDay(), function () use ($student, $termNumber, $session) {
            return [
                'overview' => $this->getOverview($student, $termNumber, $session),
                'subject_performance' => $this->getSubjectPerformance($student, $termNumber, $session),
                'strengths_weaknesses' => $this->getStrengthsAndWeaknesses($student, $termNumber, $session),
                'term_comparison' => $this->getTermComparison($student, $session),
                'attendance_impact' => $this->getAttendanceImpact($student, $termNumber, $session),
                'homework_performance' => $this->getHomeworkPerformance($student),
                'cbt_performance' => $this->getCbtPerformance($student),
                'improvement_areas' => $this->getImprovementAreas($student, $termNumber, $session),
                'progress_trend' => $this->getProgressTrend($student, $session),
            ];
        });
    }

    public function getOverview(Student $student, int $term, string $session): array
    {
        $scores = Score::where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->get();

        $maxPossible = config('myacademy.results_ca1_max', 20) + 
                      config('myacademy.results_ca2_max', 20) + 
                      config('myacademy.results_exam_max', 60);

        $average = $scores->avg('total') ?? 0;
        $percentage = $maxPossible > 0 ? ($average / $maxPossible) * 100 : 0;

        return [
            'total_subjects' => $scores->count(),
            'average_score' => round($average, 1),
            'percentage' => round($percentage, 1),
            'grade' => Score::gradeForTotal((int)$average, $maxPossible),
            'highest_score' => $scores->max('total') ?? 0,
            'lowest_score' => $scores->min('total') ?? 0,
            'subjects_passed' => $scores->filter(fn($s) => $s->grade !== 'F')->count(),
            'subjects_failed' => $scores->filter(fn($s) => $s->grade === 'F')->count(),
        ];
    }

    private function getSubjectPerformance(Student $student, int $term, string $session): Collection
    {
        $maxPossible = config('myacademy.results_ca1_max', 20) + 
                      config('myacademy.results_ca2_max', 20) + 
                      config('myacademy.results_exam_max', 60);

        return Score::where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->with('subject')
            ->get()
            ->map(function ($score) use ($maxPossible) {
                $percentage = $maxPossible > 0 ? ($score->total / $maxPossible) * 100 : 0;
                
                return [
                    'subject' => $score->subject->name,
                    'ca1' => $score->ca1,
                    'ca2' => $score->ca2,
                    'exam' => $score->exam,
                    'total' => $score->total,
                    'grade' => $score->grade,
                    'percentage' => round($percentage, 1),
                    'position' => $score->position,
                ];
            })
            ->sortByDesc('total');
    }

    private function getStrengthsAndWeaknesses(Student $student, int $term, string $session): array
    {
        $scores = Score::where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->with('subject')
            ->get();

        $maxPossible = config('myacademy.results_ca1_max', 20) + 
                      config('myacademy.results_ca2_max', 20) + 
                      config('myacademy.results_exam_max', 60);

        $strengths = $scores
            ->filter(fn($s) => ($s->total / $maxPossible) >= 0.7)
            ->sortByDesc('total')
            ->take(3)
            ->map(fn($s) => [
                'subject' => $s->subject->name,
                'score' => $s->total,
                'grade' => $s->grade,
                'percentage' => round(($s->total / $maxPossible) * 100, 1),
            ])
            ->values();

        $weaknesses = $scores
            ->filter(fn($s) => ($s->total / $maxPossible) < 0.6)
            ->sortBy('total')
            ->take(3)
            ->map(fn($s) => [
                'subject' => $s->subject->name,
                'score' => $s->total,
                'grade' => $s->grade,
                'percentage' => round(($s->total / $maxPossible) * 100, 1),
            ])
            ->values();

        return [
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
        ];
    }

    private function getTermComparison(Student $student, string $session): Collection
    {
        $maxPossible = config('myacademy.results_ca1_max', 20) + 
                      config('myacademy.results_ca2_max', 20) + 
                      config('myacademy.results_exam_max', 60);

        return collect([1, 2, 3])->map(function ($term) use ($student, $session, $maxPossible) {
            $scores = Score::where('student_id', $student->id)
                ->where('term', $term)
                ->where('session', $session)
                ->get();

            $average = $scores->avg('total') ?? 0;
            $percentage = $maxPossible > 0 ? ($average / $maxPossible) * 100 : 0;

            return [
                'term' => $term,
                'average_score' => round($average, 1),
                'percentage' => round($percentage, 1),
                'subjects_count' => $scores->count(),
                'grade' => Score::gradeForTotal((int)$average, $maxPossible),
            ];
        })->filter(fn($t) => $t['subjects_count'] > 0);
    }

    public function getAttendanceImpact(Student $student, int $term, string $session): array
    {
        $marks = AttendanceMark::where('student_id', $student->id)
            ->whereHas('sheet', function($q) use ($term, $session) {
                $q->where('term', $term)->where('session', $session);
            })
            ->get();

        $total = $marks->count();
        $present = $marks->where('status', 'Present')->count();
        $absent = $marks->where('status', 'Absent')->count();
        $late = $marks->where('status', 'Late')->count();
        $attendanceRate = $total > 0 ? ($present / $total) * 100 : 0;

        $scores = Score::where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->get();
        
        $avgScore = $scores->avg('total') ?? 0;
        $maxPossible = config('myacademy.results_ca1_max', 20) + 
                      config('myacademy.results_ca2_max', 20) + 
                      config('myacademy.results_exam_max', 60);
        $performanceRate = $maxPossible > 0 ? ($avgScore / $maxPossible) * 100 : 0;

        $correlation = match(true) {
            $total === 0 => 'No attendance data recorded yet.',
            $attendanceRate >= 90 && $performanceRate >= 70 => 'Excellent attendance, excellent performance',
            $attendanceRate >= 90 && $performanceRate < 70 => 'Good attendance, needs academic improvement',
            $attendanceRate < 75 && $performanceRate < 60 => 'Poor attendance affecting performance',
            $attendanceRate < 75 => 'Attendance needs improvement',
            default => 'Moderate correlation',
        };

        return [
            'attendance_rate' => round($attendanceRate, 1),
            'total_days' => $total,
            'present_days' => $present,
            'absent_days' => $absent,
            'late_days' => $late,
            'correlation' => $correlation,
        ];
    }

    public function getHomeworkPerformance(Student $student): array
    {
        $submissions = HomeworkSubmission::where('student_id', $student->id)
            ->with('homework')
            ->get();

        $total = $submissions->count();
        $graded = $submissions->whereNotNull('grade')->count();
        $onTime = $submissions->where('status', 'Submitted')->count();
        $late = $submissions->where('status', 'Late')->count();
        $avgGrade = $submissions->whereNotNull('grade')->avg('grade') ?? 0;

        return [
            'total_assignments' => $total,
            'submitted' => $onTime + $late,
            'on_time' => $onTime,
            'late' => $late,
            'graded' => $graded,
            'average_grade' => round($avgGrade, 1),
            'completion_rate' => $total > 0 ? round((($onTime + $late) / $total) * 100, 1) : 0,
        ];
    }

    public function getCbtPerformance(Student $student): array
    {
        $attempts = CbtAttempt::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->get();

        $total = $attempts->count();
        $avgScore = $attempts->avg('score') ?? 0;
        $avgPercent = $attempts->avg('percent') ?? 0;
        $highestScore = $attempts->max('score') ?? 0;
        $lowestScore = $attempts->min('score') ?? 0;

        return [
            'total_exams' => $total,
            'average_score' => round($avgScore, 1),
            'average_percent' => round($avgPercent, 1),
            'highest_score' => $highestScore,
            'lowest_score' => $lowestScore,
            'exams_passed' => $attempts->where('percent', '>=', 50)->count(),
            'exams_failed' => $attempts->where('percent', '<', 50)->count(),
        ];
    }

    private function getImprovementAreas(Student $student, int $term, string $session): Collection
    {
        $currentScores = Score::where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->with('subject')
            ->get();

        $previousTerm = $term > 1 ? $term - 1 : 3;
        $previousSession = $term > 1 ? $session : $this->getPreviousSession($session);

        $previousScores = Score::where('student_id', $student->id)
            ->where('term', $previousTerm)
            ->where('session', $previousSession)
            ->get()
            ->keyBy('subject_id');

        return $currentScores->map(function ($current) use ($previousScores) {
            $previous = $previousScores->get($current->subject_id);
            $change = $previous ? $current->total - $previous->total : 0;
            
            return [
                'subject' => $current->subject->name,
                'current_score' => $current->total,
                'previous_score' => $previous?->total ?? 0,
                'change' => $change,
                'trend' => match(true) {
                    $change > 5 => 'Improving',
                    $change < -5 => 'Declining',
                    default => 'Stable',
                },
                'needs_attention' => $current->grade === 'F' || $change < -5,
            ];
        })->sortBy('current_score');
    }

    private function getProgressTrend(Student $student, string $session): Collection
    {
        $maxPossible = config('myacademy.results_ca1_max', 20) + 
                      config('myacademy.results_ca2_max', 20) + 
                      config('myacademy.results_exam_max', 60);

        return collect([1, 2, 3])->map(function ($term) use ($student, $session, $maxPossible) {
            $scores = Score::where('student_id', $student->id)
                ->where('term', $term)
                ->where('session', $session)
                ->get();

            if ($scores->isEmpty()) {
                return null;
            }

            $average = $scores->avg('total');
            $percentage = $maxPossible > 0 ? ($average / $maxPossible) * 100 : 0;

            return [
                'term' => "Term $term",
                'average' => round($average, 1),
                'percentage' => round($percentage, 1),
            ];
        })->filter();
    }

    private function getPreviousSession(string $session): string
    {
        $parts = explode('/', $session);
        if (count($parts) === 2) {
            $year1 = (int)$parts[0] - 1;
            $year2 = (int)$parts[1] - 1;
            return "$year1/$year2";
        }
        return $session;
    }

    /**
     * Delete any cached report card PDFs for a specific student when grades change.
     */
    public static function clearReportCardCache(Student $student): void
    {
        $baseDir = storage_path("app/public/report-cards");
        if (!is_dir($baseDir)) {
            return;
        }

        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($baseDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if ($file->isFile() && str_contains($file->getFilename(), 'report-card-' . $student->admission_number)) {
                    @unlink($file->getPathname());
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Could not clear PDF cache: " . $e->getMessage());
        }
    }
}
