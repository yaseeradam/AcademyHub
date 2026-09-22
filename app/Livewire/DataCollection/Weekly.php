<?php

namespace App\Livewire\DataCollection;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\WeeklyDataCollection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Data Collection')]
class Weekly extends Component
{
    public ?int $classId = null;
    public ?int $sectionId = null;
    public ?int $term = null;
    public string $session = '';
    public string $weekStart = '';

    public int $boysPresent = 0;
    public int $boysAbsent = 0;
    public int $girlsPresent = 0;
    public int $girlsAbsent = 0;
    public ?int $schoolDays = null;
    public string $note = '';

    public function mount(): void
    {
        $this->session = $this->session ?: $this->defaultSession();
        $this->term = $this->term ?: $this->defaultTerm();
        $this->weekStart = $this->weekStart ?: $this->defaultWeekStart();
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::query()->orderBy('level')->orderBy('name')->get(['id', 'name', 'level']);
    }

    #[Computed]
    public function sections()
    {
        if (!$this->classId) {
            return collect();
        }

        return Section::query()
            ->where('class_id', $this->classId)
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);
    }

    #[Computed]
    public function weekEnd(): string
    {
        try {
            $start = Carbon::parse($this->weekStart)->startOfDay();
        } catch (\Throwable) {
            return '';
        }

        return $start->copy()->addDays(6)->toDateString();
    }

    #[Computed]
    public function totals(): array
    {
        $boysPresent = max(0, (int) $this->boysPresent);
        $boysAbsent = max(0, (int) $this->boysAbsent);
        $girlsPresent = max(0, (int) $this->girlsPresent);
        $girlsAbsent = max(0, (int) $this->girlsAbsent);

        return [
            'boys_total' => $boysPresent + $boysAbsent,
            'girls_total' => $girlsPresent + $girlsAbsent,
            'present_total' => $boysPresent + $girlsPresent,
            'absent_total' => $boysAbsent + $girlsAbsent,
            'student_total' => $boysPresent + $boysAbsent + $girlsPresent + $girlsAbsent,
        ];
    }

    #[Computed]
    public function existing(): ?WeeklyDataCollection
    {
        if (!$this->classId || !$this->sectionId || trim($this->weekStart) === '') {
            return null;
        }

        return WeeklyDataCollection::query()
            ->where('class_id', $this->classId)
            ->where('section_id', $this->sectionId)
            ->where('term', $this->term)
            ->where('session', $this->session)
            ->where('week_start', $this->weekStart)
            ->with(['teacher:id,name', 'reviewer:id,name'])
            ->first();
    }

    #[Computed]
    public function recent()
    {
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        return WeeklyDataCollection::query()
            ->where('teacher_id', $user->id)
            ->with(['schoolClass:id,name', 'section:id,name'])
            ->orderByDesc('week_start')
            ->limit(12)
            ->get();
    }

    public function updatedClassId(): void
    {
        $this->sectionId = null;
        unset($this->sections);
    }

    public function loadFromExisting(): void
    {
        $row = $this->existing;
        if (!$row) {
            return;
        }

        $this->boysPresent = (int) $row->boys_present;
        $this->boysAbsent = (int) $row->boys_absent;
        $this->girlsPresent = (int) $row->girls_present;
        $this->girlsAbsent = (int) $row->girls_absent;
        $this->schoolDays = $row->school_days !== null ? (int) $row->school_days : null;
        $this->note = (string) ($row->note ?? '');
    }

    public function submit(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);
        abort_unless($user->hasPermission('data_collection.submit'), 403);

        $data = $this->validate([
            'classId' => ['required', 'integer', Rule::exists('classes', 'id')],
            'sectionId' => ['required', 'integer', Rule::exists('sections', 'id')],
            'term' => ['required', 'integer', 'between:1,3'],
            'session' => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'],
            'weekStart' => ['required', 'date'],

            'boysPresent' => ['required', 'integer', 'min:0', 'max:2000'],
            'boysAbsent' => ['required', 'integer', 'min:0', 'max:2000'],
            'girlsPresent' => ['required', 'integer', 'min:0', 'max:2000'],
            'girlsAbsent' => ['required', 'integer', 'min:0', 'max:2000'],
            'schoolDays' => ['nullable', 'integer', 'min:1', 'max:7'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $sectionOk = Section::query()
            ->whereKey((int) $data['sectionId'])
            ->where('class_id', (int) $data['classId'])
            ->exists();

        abort_unless($sectionOk, 422);

        $start = Carbon::parse($data['weekStart'])->startOfDay();
        $end = $start->copy()->addDays(6);

        $existing = WeeklyDataCollection::query()
            ->where('class_id', (int) $data['classId'])
            ->where('section_id', (int) $data['sectionId'])
            ->where('term', (int) $data['term'])
            ->where('session', (string) $data['session'])
            ->where('week_start', $start->toDateString())
            ->with(['teacher:id,name'])
            ->first();

        if ($existing && (int) $existing->teacher_id !== (int) $user->id && $existing->status !== 'rejected') {
            $who = $existing->teacher?->name ? " ({$existing->teacher->name})" : '';
            $this->dispatch('alert', message: "This week is already submitted{$who}.", type: 'warning');
            return;
        }

        if ($existing && $existing->status === 'approved') {
            $this->dispatch('alert', message: 'This submission is already approved and cannot be changed.', type: 'warning');
            return;
        }

        WeeklyDataCollection::query()->updateOrCreate(
            [
                'class_id' => (int) $data['classId'],
                'section_id' => (int) $data['sectionId'],
                'term' => (int) $data['term'],
                'session' => (string) $data['session'],
                'week_start' => $start->toDateString(),
            ],
            [
                'teacher_id' => (int) $user->id,
                'week_end' => $end->toDateString(),
                'boys_present' => max(0, (int) $data['boysPresent']),
                'boys_absent' => max(0, (int) $data['boysAbsent']),
                'girls_present' => max(0, (int) $data['girlsPresent']),
                'girls_absent' => max(0, (int) $data['girlsAbsent']),
                'school_days' => $data['schoolDays'] !== null ? (int) $data['schoolDays'] : null,
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
                'status' => 'submitted',
                'submitted_at' => now(),
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_note' => null,
            ]
        );

        unset($this->existing);
        unset($this->recent);
        $this->dispatch('alert', message: 'Submitted to admin.', type: 'success');
    }

    public function bump(string $field, int $delta): void
    {
        if (!in_array($field, ['boysPresent', 'boysAbsent', 'girlsPresent', 'girlsAbsent'], true)) {
            return;
        }

        $value = max(0, (int) $this->{$field} + $delta);
        $this->{$field} = $value;
    }

    private function defaultWeekStart(): string
    {
        return now()->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    private function defaultSession(): string
    {
        $active = AcademicSession::activeName();
        if ($active) {
            return $active;
        }

        $year = (int) now()->format('Y');
        $next = $year + 1;

        return "{$year}/{$next}";
    }

    private function defaultTerm(): int
    {
        return AcademicTerm::activeTermNumber();
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin', 'teacher'], true), 403);
        abort_unless($user->hasPermission('data_collection.submit'), 403);

        return view('livewire.data-collection.weekly');
    }
}

