<?php

namespace App\Livewire\DataCollection;

use App\Models\SchoolClass;
use App\Models\WeeklyDataCollection;
use App\Models\AcademicSession;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Data Collection Submissions')]
class Submissions extends Component
{
    public ?int $classId = null;
    public ?int $term = null;
    public string $session = '';
    public string $status = 'submitted'; // submitted|approved|rejected|all

    public ?int $rejectingId = null;
    public string $rejectNote = '';

    public function mount(): void
    {
        $this->term = $this->term ?: 1;
        $this->session = $this->session ?: $this->defaultSession();
    }

    #[Computed]
    public function classes()
    {
        return SchoolClass::query()->orderBy('level')->orderBy('name')->get(['id', 'name', 'level']);
    }

    #[Computed]
    public function rows()
    {
        $query = WeeklyDataCollection::query()
            ->with(['teacher:id,name', 'reviewer:id,name', 'schoolClass:id,name', 'section:id,name'])
            ->where('term', $this->term)
            ->where('session', $this->session);

        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query
            ->orderByDesc('week_start')
            ->orderByDesc('submitted_at')
            ->limit(80)
            ->get();
    }

    public function approve(int $id): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('data_collection.review'), 403);

        $row = WeeklyDataCollection::query()->findOrFail($id);
        if ($row->status !== 'submitted') {
            $this->dispatch('alert', message: 'Only submitted items can be approved.', type: 'warning');
            return;
        }

        $row->forceFill([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'rejection_note' => null,
        ])->save();

        unset($this->rows);
        $this->dispatch('alert', message: 'Approved.', type: 'success');
    }

    public function startReject(int $id): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('data_collection.review'), 403);

        $row = WeeklyDataCollection::query()->findOrFail($id);
        if ($row->status !== 'submitted') {
            $this->dispatch('alert', message: 'Only submitted items can be rejected.', type: 'warning');
            return;
        }

        $this->rejectingId = $id;
        $this->rejectNote = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
        $this->rejectNote = '';
    }

    public function confirmReject(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('data_collection.review'), 403);

        $id = (int) ($this->rejectingId ?? 0);
        if ($id <= 0) {
            return;
        }

        $this->validate([
            'rejectingId' => ['required', 'integer', Rule::exists('weekly_data_collections', 'id')],
            'rejectNote' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $row = WeeklyDataCollection::query()->findOrFail($id);
        if ($row->status !== 'submitted') {
            $this->dispatch('alert', message: 'Only submitted items can be rejected.', type: 'warning');
            $this->cancelReject();
            return;
        }

        $row->forceFill([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'rejection_note' => trim($this->rejectNote),
        ])->save();

        $this->cancelReject();
        unset($this->rows);
        $this->dispatch('alert', message: 'Rejected.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('data_collection.review'), 403);

        return view('livewire.data-collection.submissions');
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
}
