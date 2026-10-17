<?php

namespace App\Livewire\Parents;

use App\Models\AttendanceExcuse;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Excuse Requests')]
class ExcuseRequest extends Component
{
    public ?int $selectedStudentId = null;
    public string $statusFilter = 'all';
    public string $search = '';

    // Modal state for submitting a new excuse request
    public bool $showModal = false;
    public ?int $student_id = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = 'Illness / Medical';
    public string $notes = '';

    public array $reasonOptions = [
        'Illness / Medical',
        'Doctor / Dental Appointment',
        'Family Emergency',
        'Bereavement',
        'Religious Observance',
        'Travel / Relocation',
        'Official Exam / Competition',
        'Other',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $tenant = $user?->tenant;
        if ($tenant && $tenant->isSubscriptionExpired()) {
            abort(403, 'Your school\'s subscription has expired.');
        }

        $this->start_date = now()->toDateString();
        $this->end_date   = now()->toDateString();

        if (request()->has('child')) {
            $this->selectedStudentId = (int) request('child');
        }

        if (request()->has('status')) {
            $this->statusFilter = (string) request('status');
        }

        if (request()->boolean('new') || request('action') === 'new') {
            $this->openCreateModal($this->selectedStudentId);
        }
    }

    #[Computed]
    public function isParent(): bool
    {
        return auth()->user()?->role === 'parent';
    }

    #[Computed]
    public function isStaff(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'teacher', 'proprietor'], true);
    }

    #[Computed]
    public function children(): Collection
    {
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        if ($this->isParent) {
            return $user->students()
                ->with(['schoolClass', 'section'])
                ->orderBy('first_name')
                ->get();
        }

        // For teachers/admins: return students in scope
        if ($user->role === 'teacher') {
            return Student::query()
                ->with(['schoolClass', 'section'])
                ->orderBy('first_name')
                ->limit(200)
                ->get();
        }

        return Student::query()
            ->with(['schoolClass', 'section'])
            ->orderBy('first_name')
            ->limit(300)
            ->get();
    }

    #[Computed]
    public function excuses(): Collection
    {
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        $query = AttendanceExcuse::query()
            ->with(['student.schoolClass', 'student.section', 'parent', 'reviewer'])
            ->latest('start_date');

        if ($this->isParent) {
            $childIds = $this->children->pluck('id')->all();
            $query->whereIn('student_id', $childIds);
        }

        if ($this->selectedStudentId) {
            $query->where('student_id', $this->selectedStudentId);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reason', 'like', $term)
                  ->orWhere('notes', 'like', $term)
                  ->orWhereHas('student', function ($sq) use ($term) {
                      $sq->where('first_name', 'like', $term)
                         ->orWhere('last_name', 'like', $term)
                         ->orWhere('admission_number', 'like', $term);
                  });
            });
        }

        return $query->get();
    }

    #[Computed]
    public function counts(): array
    {
        $user = auth()->user();
        if (!$user) {
            return ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        }

        $base = AttendanceExcuse::query();
        if ($this->isParent) {
            $childIds = $this->children->pluck('id')->all();
            $base->whereIn('student_id', $childIds);
        }

        $all = (clone $base)->count();
        $pending = (clone $base)->where('status', 'pending')->count();
        $approved = (clone $base)->where('status', 'approved')->count();
        $rejected = (clone $base)->where('status', 'rejected')->count();

        return compact('all', 'pending', 'approved', 'rejected');
    }

    public function openCreateModal(?int $studentId = null): void
    {
        $this->student_id = $studentId ?: ($this->selectedStudentId ?: ($this->children->first()?->id ?? null));
        $this->start_date = now()->toDateString();
        $this->end_date   = now()->toDateString();
        $this->reason     = 'Illness / Medical';
        $this->notes      = '';
        $this->resetValidation();
        $this->showModal  = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function submitRequest(): void
    {
        $this->validate([
            'student_id' => 'required|integer|exists:students,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required|string|max:100',
            'notes'      => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        // If parent, verify ownership of student
        if ($this->isParent && ! $this->children->contains('id', (int) $this->student_id)) {
            $this->addError('student_id', 'You can only submit excuse requests for your registered children.');
            return;
        }

        AttendanceExcuse::create([
            'tenant_id'   => $user->tenant_id ?? 1,
            'student_id'  => $this->student_id,
            'parent_id'   => $user->id,
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date,
            'reason'      => $this->reason,
            'notes'       => $this->notes ?: null,
            'status'      => 'pending',
        ]);

        $this->showModal = false;
        $student = Student::find($this->student_id);
        $studentName = $student ? $student->first_name : 'your child';
        $this->dispatch('alert', message: "Excuse request for {$studentName} submitted successfully. School staff have been notified.", type: 'success');
    }

    public function cancelRequest(int $id): void
    {
        $query = AttendanceExcuse::where('id', $id)->where('status', 'pending');
        if ($this->isParent) {
            $query->where('parent_id', auth()->id());
        }

        $excuse = $query->first();
        if ($excuse) {
            $excuse->delete();
            $this->dispatch('alert', message: 'Excuse request cancelled.', type: 'info');
        }
    }

    public function approveRequest(int $id): void
    {
        if (! $this->isStaff) {
            abort(403);
        }

        $excuse = AttendanceExcuse::find($id);
        if ($excuse) {
            $excuse->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
            $this->dispatch('alert', message: 'Excuse request approved.', type: 'success');
        }
    }

    public function rejectRequest(int $id): void
    {
        if (! $this->isStaff) {
            abort(403);
        }

        $excuse = AttendanceExcuse::find($id);
        if ($excuse) {
            $excuse->update([
                'status'      => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
            $this->dispatch('alert', message: 'Excuse request rejected.', type: 'warning');
        }
    }

    public function render()
    {
        return view('livewire.parents.excuse-request', [
            'excuses'  => $this->excuses,
            'counts'   => $this->counts,
            'children' => $this->children,
        ]);
    }
}
