<?php

namespace App\Livewire\Analytics;

use App\Models\Student;
use App\Models\Score;
use App\Models\Transaction;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\AttendanceMark;
use App\Models\CbtAttempt;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Analytics Dashboard')]
class Dashboard extends Component
{
    public string $selectedPeriod = 'current_term';
    public ?int $selectedClass = null;
    public string $selectedMetric = 'overview';

    public function mount()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);
    }

    #[Computed]
    public function currentSession()
    {
        return AcademicSession::where('is_active', true)->first() ?? AcademicSession::latest()->first();
    }

    #[Computed]
    public function currentTerm()
    {
        return AcademicTerm::active() ?? AcademicTerm::latest()->first();
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::orderBy('level')->get();
    }

    #[Computed]
    public function studentStats()
    {
        $query = Student::query();
        
        if ($this->selectedClass) {
            $query->where('class_id', $this->selectedClass);
        }

        $total = $query->count();
        $active = $query->where('status', 'Active')->count();
        $inactive = $query->where('status', 'Inactive')->count();
        $graduated = $query->where('status', 'Graduated')->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'graduated' => $graduated,
            'active_percentage' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
        ];
    }

    #[Computed]
    public function academicPerformance()
    {
        $session = $this->currentSession?->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1);
        $term = $this->currentTerm?->term_number ?? 1;

        $query = Score::query()
            ->where('session', $session)
            ->where('term', $term);

        if ($this->selectedClass) {
            $query->where('class_id', $this->selectedClass);
        }

        $scores = $query->get();
        
        if ($scores->isEmpty()) {
            return [
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'total_assessments' => 0,
                'grade_distribution' => [],
                'subject_performance' => [],
            ];
        }

        $totals = $scores->map(fn($score) => $score->total);
        $maxPossible = config('myacademy.results_ca1_max', 20) + 
                      config('myacademy.results_ca2_max', 20) + 
                      config('myacademy.results_exam_max', 60);

        // Grade distribution
        $gradeDistribution = [];
        foreach ($scores as $score) {
            $grade = Score::gradeForTotal($score->total, $maxPossible);
            $gradeDistribution[$grade] = ($gradeDistribution[$grade] ?? 0) + 1;
        }

        // Subject performance
        $subjectPerformance = $scores->groupBy('subject_id')->map(function ($subjectScores) use ($maxPossible) {
            $avg = $subjectScores->avg('total');
            $subject = Subject::find($subjectScores->first()->subject_id);
            return [
                'subject' => $subject?->name ?? 'Unknown',
                'average' => round($avg, 1),
                'percentage' => round(($avg / $maxPossible) * 100, 1),
                'count' => $subjectScores->count(),
            ];
        })->sortByDesc('average')->take(10);

        return [
            'average_score' => round($totals->avg(), 1),
            'highest_score' => $totals->max(),
            'lowest_score' => $totals->min(),
            'total_assessments' => $scores->count(),
            'grade_distribution' => $gradeDistribution,
            'subject_performance' => $subjectPerformance->values(),
        ];
    }

    #[Computed]
    public function attendanceStats()
    {
        $startDate = match($this->selectedPeriod) {
            'current_week' => now()->startOfWeek(),
            'current_month' => now()->startOfMonth(),
            'current_term' => $this->currentTerm?->start_date ?? now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $query = AttendanceMark::query()
            ->whereHas('sheet', fn($q) => $q->where('date', '>=', $startDate)->where('date', '<=', now()));

        if ($this->selectedClass) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $this->selectedClass));
        }

        $marks = $query->with('sheet')->get();
        $total = $marks->count();
        $present = $marks->where('status', 'Present')->count();
        $absent = $marks->where('status', 'Absent')->count();
        $late = $marks->where('status', 'Late')->count();

        // Daily attendance trend (last 7 days)
        $dailyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayMarks = $marks->filter(fn($m) => optional($m->sheet)->date === $date->format('Y-m-d'));
            $dayTotal = $dayMarks->count();
            $dayPresent = $dayMarks->where('status', 'Present')->count();
            
            $dailyTrend[] = [
                'date' => $date->format('M j'),
                'attendance_rate' => $dayTotal > 0 ? round(($dayPresent / $dayTotal) * 100, 1) : 0,
                'total' => $dayTotal,
                'present' => $dayPresent,
            ];
        }

        return [
            'total_records' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            'daily_trend' => $dailyTrend,
        ];
    }

    #[Computed]
    public function financialStats()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'bursar'], true)) {
            return [
                'total_revenue' => 0,
                'pending_fees' => 0,
                'monthly_trend' => [],
                'payment_methods' => [],
            ];
        }

        $startDate = match($this->selectedPeriod) {
            'current_week' => now()->startOfWeek(),
            'current_month' => now()->startOfMonth(),
            'current_term' => $this->currentTerm?->start_date ?? now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $transactions = Transaction::query()
            ->where('type', 'Income')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', now())
            ->get();

        $totalRevenue = $transactions->sum('amount');
        
        // Monthly trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthTransactions = Transaction::query()
                ->where('type', 'Income')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            
            $monthlyTrend[] = [
                'month' => $month->format('M Y'),
                'revenue' => $monthTransactions,
            ];
        }

        // Payment methods distribution
        $paymentMethods = $transactions->groupBy('payment_method')->map(function ($group, $method) {
            return [
                'method' => $method ?: 'Cash',
                'amount' => $group->sum('amount'),
                'count' => $group->count(),
            ];
        })->sortByDesc('amount')->values();

        return [
            'total_revenue' => $totalRevenue,
            'transaction_count' => $transactions->count(),
            'average_transaction' => $transactions->count() > 0 ? round($totalRevenue / $transactions->count(), 2) : 0,
            'monthly_trend' => $monthlyTrend,
            'payment_methods' => $paymentMethods,
        ];
    }

    #[Computed]
    public function classPerformanceComparison()
    {
        $session = $this->currentSession?->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1);
        $term = $this->currentTerm?->term_number ?? 1;

        $classStats = SchoolClass::query()
            ->withCount(['students' => fn($q) => $q->where('status', 'Active')])
            ->get()
            ->map(function ($class) use ($session, $term) {
                $scores = Score::query()
                    ->where('class_id', $class->id)
                    ->where('session', $session)
                    ->where('term', $term)
                    ->get();

                $avgScore = $scores->avg('total') ?? 0;
                $attendanceRate = $this->getClassAttendanceRate($class->id);

                return [
                    'class_name' => $class->name,
                    'student_count' => $class->students_count,
                    'average_score' => round($avgScore, 1),
                    'attendance_rate' => $attendanceRate,
                    'total_assessments' => $scores->count(),
                ];
            })
            ->sortByDesc('average_score')
            ->values();

        return $classStats;
    }

    #[Computed]
    public function cbtStats()
    {
        $attempts = CbtAttempt::query()
            ->with(['exam', 'student'])
            ->when($this->selectedClass, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('class_id', $this->selectedClass)))
            ->get();

        $totalAttempts = $attempts->count();
        $completedAttempts = $attempts->whereNotNull('submitted_at')->count();
        $averageScore = $attempts->whereNotNull('score')->avg('score') ?? 0;
        $averagePercent = $attempts->whereNotNull('percent')->avg('percent') ?? 0;

        // Performance distribution
        $performanceRanges = [
            '90-100%' => $attempts->where('percent', '>=', 90)->count(),
            '80-89%' => $attempts->whereBetween('percent', [80, 89])->count(),
            '70-79%' => $attempts->whereBetween('percent', [70, 79])->count(),
            '60-69%' => $attempts->whereBetween('percent', [60, 69])->count(),
            'Below 60%' => $attempts->where('percent', '<', 60)->count(),
        ];

        return [
            'total_attempts' => $totalAttempts,
            'completed_attempts' => $completedAttempts,
            'completion_rate' => $totalAttempts > 0 ? round(($completedAttempts / $totalAttempts) * 100, 1) : 0,
            'average_score' => round($averageScore, 1),
            'average_percent' => round($averagePercent, 1),
            'performance_distribution' => $performanceRanges,
        ];
    }

    private function getClassAttendanceRate(int $classId): float
    {
        $startDate = match($this->selectedPeriod) {
            'current_week' => now()->startOfWeek(),
            'current_month' => now()->startOfMonth(),
            'current_term' => $this->currentTerm?->start_date ?? now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $marks = AttendanceMark::query()
            ->whereHas('student', fn($q) => $q->where('class_id', $classId))
            ->whereHas('sheet', fn($q) => $q->where('date', '>=', $startDate)->where('date', '<=', now()))
            ->get();

        $total = $marks->count();
        $present = $marks->where('status', 'Present')->count();

        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    public function render()
    {
        return view('livewire.analytics.dashboard');
    }
}