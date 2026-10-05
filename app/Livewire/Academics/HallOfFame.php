<?php

namespace App\Livewire\Academics;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Hall of Fame — Best Students Hierarchy')]
class HallOfFame extends Component
{
    public string $scope = 'overall'; // 'overall' or 'class'
    public ?int $selectedClassId = null;
    public ?string $selectedSession = null;
    public ?int $selectedTerm = null;
    public ?int $selectedStudentId = null;
    public string $viewMode = 'tree'; // 'tree' or 'leaderboard'

    public function mount(): void
    {
        // Check access: Student session or authenticated role
        $isStudentSession = (bool) session('student_id');
        $isAuthUser = auth()->check() && in_array(auth()->user()->role, ['admin', 'teacher', 'bursar', 'proprietor', 'student', 'parent'], true);

        if (!$isStudentSession && !$isAuthUser) {
            abort(403, 'Unauthorized access to Hall of Fame.');
        }

        // Set active session and term defaults
        $activeTerm = AcademicTerm::active();
        $this->selectedTerm = $activeTerm?->term_number ?? 1;
        $this->selectedSession = $activeTerm?->academicSession?->name
            ?? AcademicSession::where('is_active', true)->value('name')
            ?? now()->format('Y') . '/' . (now()->format('Y') + 1);

        // If user is a student or teacher scoped to a class, provide smart default class
        if ($isStudentSession) {
            $student = Student::find(session('student_id'));
            if ($student && $student->class_id) {
                $this->selectedClassId = $student->class_id;
            }
        } elseif (!$this->selectedClassId && $this->classes()->isNotEmpty()) {
            $this->selectedClassId = $this->classes()->first()->id;
        }
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::orderBy('name')->get();
    }

    #[Computed]
    public function sessions()
    {
        return AcademicSession::orderByDesc('name')->get();
    }

    #[Computed]
    public function currentClassName(): string
    {
        if ($this->scope === 'overall' || !$this->selectedClassId) {
            return 'Overall School (All Classes)';
        }

        return SchoolClass::find($this->selectedClassId)?->name ?? 'Selected Class';
    }

    #[Computed]
    public function rankedStudents(): array
    {
        if (!$this->selectedSession || !$this->selectedTerm) {
            return [];
        }

        $query = Student::query()
            ->where('status', 'Active');

        if ($this->scope === 'class' && $this->selectedClassId) {
            $query->where('class_id', $this->selectedClassId);
        }

        $students = $query->with([
            'schoolClass',
            'section',
            'scores' => function ($q) {
                $q->where('term', $this->selectedTerm)
                  ->where('session', $this->selectedSession)
                  ->with('subject');
            }
        ])->get();

        $ranked = [];

        foreach ($students as $student) {
            $scores = $student->scores;
            if ($scores->isEmpty()) {
                continue;
            }

            $totalPoints = $scores->sum('total');
            $subjectCount = $scores->count();
            if ($subjectCount === 0) {
                continue;
            }

            $average = round($totalPoints / $subjectCount, 1);
            $distinctions = $scores->filter(fn($s) => $s->total >= 75)->count();
            $passes = $scores->filter(fn($s) => $s->total >= 50)->count();
            
            $bestScore = $scores->sortByDesc('total')->first();

            $ranked[] = [
                'id' => $student->id,
                'admission_number' => $student->admission_number,
                'full_name' => $student->full_name,
                'gender' => $student->gender,
                'photo' => $student->passport_photo_url,
                'class_name' => $student->schoolClass?->name ?? 'Class',
                'arm_name' => $student->section?->name,
                'total_points' => $totalPoints,
                'subject_count' => $subjectCount,
                'average' => $average,
                'distinctions' => $distinctions,
                'passes' => $passes,
                'highest_subject' => $bestScore?->subject?->name ?? 'General',
                'highest_score' => $bestScore?->total ?? 0,
            ];
        }

        // Sort descending by average percentage, then total points, then distinctions
        usort($ranked, function ($a, $b) {
            if ($b['average'] != $a['average']) {
                return $b['average'] <=> $a['average'];
            }
            if ($b['total_points'] != $a['total_points']) {
                return $b['total_points'] <=> $a['total_points'];
            }
            return $b['distinctions'] <=> $a['distinctions'];
        });

        // Assign rankings (handling ties gracefully)
        $currentRank = 1;
        foreach ($ranked as $index => &$item) {
            $item['rank'] = $index + 1;
        }

        return $ranked;
    }

    #[Computed]
    public function apexStudent(): ?array
    {
        return $this->rankedStudents()[0] ?? null;
    }

    #[Computed]
    public function silverStudent(): ?array
    {
        return $this->rankedStudents()[1] ?? null;
    }

    #[Computed]
    public function bronzeStudent(): ?array
    {
        return $this->rankedStudents()[2] ?? null;
    }

    #[Computed]
    public function honorRoll(): array
    {
        // 4th to 7th place
        return array_slice($this->rankedStudents(), 3, 4);
    }

    #[Computed]
    public function runnerUps(): array
    {
        // 8th to 20th place
        return array_slice($this->rankedStudents(), 7, 13);
    }

    #[Computed]
    public function modalStudent(): ?Student
    {
        if (!$this->selectedStudentId) {
            return null;
        }

        return Student::with([
            'schoolClass',
            'section',
            'scores' => function ($q) {
                $q->where('term', $this->selectedTerm)
                  ->where('session', $this->selectedSession)
                  ->with('subject');
            }
        ])->find($this->selectedStudentId);
    }

    public function selectStudent(int $studentId): void
    {
        $this->selectedStudentId = $studentId;
    }

    public function closeModal(): void
    {
        $this->selectedStudentId = null;
    }

    public function setScope(string $newScope): void
    {
        $this->scope = $newScope;
        if ($newScope === 'class' && !$this->selectedClassId && $this->classes()->isNotEmpty()) {
            $this->selectedClassId = $this->classes()->first()->id;
        }
    }

    public function render()
    {
        // If current session is a student session, render in student layout; else standard app layout
        $layout = session('student_id') || (auth()->check() && auth()->user()->role === 'student')
            ? 'layouts.student'
            : 'layouts.app';

        $ranked = $this->rankedStudents();

        return view('livewire.academics.hall-of-fame', [
            'currentClassName' => $this->currentClassName(),
            'sessions' => $this->sessions(),
            'classes' => $this->classes(),
            'rankedStudents' => $ranked,
            'apexStudent' => $ranked[0] ?? null,
            'silverStudent' => $ranked[1] ?? null,
            'bronzeStudent' => $ranked[2] ?? null,
            'honorRoll' => array_slice($ranked, 3, 4),
            'runnerUps' => array_slice($ranked, 7, 13),
            'modalStudent' => $this->modalStudent(),
        ])->layout($layout);
    }
}
