<?php

namespace App\Livewire\Academics;

use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Academic Sessions')]
class Sessions extends Component
{
    /* ------------------------------------------------------------------ */
    /*  Session form                                                       */
    /* ------------------------------------------------------------------ */
    public ?int $editingId = null;
    public string $name = '';
    public ?string $startsOn = null;
    public ?string $endsOn = null;
    public bool $isActive = false;

    /* ------------------------------------------------------------------ */
    /*  Term form                                                          */
    /* ------------------------------------------------------------------ */
    public ?int $termSessionId = null;   // which session we're adding/editing a term for
    public ?int $editingTermId = null;
    public string $termName = '';
    public int $termNumber = 1;
    public ?string $termStartsOn = null;
    public ?string $termEndsOn = null;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);
    }

    /* ================================================================== */
    /*  SESSION CRUD                                                       */
    /* ================================================================== */

    public function edit(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $s = AcademicSession::query()->findOrFail($id);
        $this->editingId = $s->id;
        $this->name = $s->name;
        $this->startsOn = $s->starts_on?->toDateString();
        $this->endsOn = $s->ends_on?->toDateString();
        $this->isActive = (bool) $s->is_active;
    }

    public function clearForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->startsOn = null;
        $this->endsOn = null;
        $this->isActive = false;
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/', 'max:9'],
            'startsOn' => ['nullable', 'date'],
            'endsOn' => ['nullable', 'date'],
            'isActive' => ['boolean'],
        ]);

        [$y1, $y2] = explode('/', $data['name'], 2);
        if ((int) $y2 !== (int) $y1 + 1) {
            throw ValidationException::withMessages([
                'name' => 'Session should look like 2026/2027.',
            ]);
        }

        if ($data['startsOn'] && $data['endsOn'] && $data['endsOn'] < $data['startsOn']) {
            throw ValidationException::withMessages([
                'endsOn' => 'End date must be after start date.',
            ]);
        }

        DB::transaction(function () use ($data) {
            if ($data['isActive']) {
                AcademicSession::query()->where('is_active', true)->update(['is_active' => false]);
            }

            AcademicSession::query()->updateOrCreate(
                ['id' => $this->editingId],
                [
                    'name' => $data['name'],
                    'starts_on' => $data['startsOn'] ?: null,
                    'ends_on' => $data['endsOn'] ?: null,
                    'is_active' => (bool) $data['isActive'],
                ]
            );
        });

        $this->dispatch('alert', message: 'Academic session saved.', type: 'success');
        $this->clearForm();
    }

    public function setActive(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        DB::transaction(function () use ($id) {
            AcademicSession::query()->where('is_active', true)->update(['is_active' => false]);
            AcademicSession::query()->whereKey($id)->update(['is_active' => true]);
        });

        $this->dispatch('alert', message: 'Active session updated.', type: 'success');
    }

    public function delete(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        AcademicSession::query()->whereKey($id)->delete();

        if ($this->editingId === $id) {
            $this->clearForm();
        }

        $this->dispatch('alert', message: 'Session deleted.', type: 'success');
    }

    /* ================================================================== */
    /*  TERM CRUD                                                          */
    /* ================================================================== */

    public function showTermForm(int $sessionId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $this->termSessionId = $sessionId;
        $this->clearTermForm();
    }

    public function clearTermForm(): void
    {
        $this->editingTermId = null;
        $this->termName = '';
        $this->termNumber = 1;
        $this->termStartsOn = null;
        $this->termEndsOn = null;
    }

    public function hideTermForm(): void
    {
        $this->termSessionId = null;
        $this->clearTermForm();
    }

    public function editTerm(int $termId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $term = AcademicTerm::query()->findOrFail($termId);

        $this->termSessionId = $term->academic_session_id;
        $this->editingTermId = $term->id;
        $this->termName = $term->name;
        $this->termNumber = $term->term_number;
        $this->termStartsOn = $term->starts_on?->toDateString();
        $this->termEndsOn = $term->ends_on?->toDateString();
    }

    public function saveTerm(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        if (!$this->termSessionId) {
            return;
        }

        // Ensure the session exists
        AcademicSession::query()->findOrFail($this->termSessionId);

        $data = $this->validate([
            'termName' => ['required', 'string', 'max:50'],
            'termNumber' => ['required', 'integer', 'between:1,3'],
            'termStartsOn' => ['nullable', 'date'],
            'termEndsOn' => ['nullable', 'date'],
        ]);

        if ($data['termStartsOn'] && $data['termEndsOn'] && $data['termEndsOn'] < $data['termStartsOn']) {
            throw ValidationException::withMessages([
                'termEndsOn' => 'End date must be after start date.',
            ]);
        }

        // Check for duplicate term_number under same session (excluding current record)
        $duplicate = AcademicTerm::query()
            ->where('academic_session_id', $this->termSessionId)
            ->where('term_number', $data['termNumber'])
            ->when($this->editingTermId, fn($q) => $q->where('id', '!=', $this->editingTermId))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'termNumber' => 'This term number already exists for this session.',
            ]);
        }

        AcademicTerm::query()->updateOrCreate(
            ['id' => $this->editingTermId],
            [
                'academic_session_id' => $this->termSessionId,
                'name' => trim($data['termName']),
                'term_number' => (int) $data['termNumber'],
                'starts_on' => $data['termStartsOn'] ?: null,
                'ends_on' => $data['termEndsOn'] ?: null,
            ]
        );

        $this->dispatch('alert', message: 'Term saved.', type: 'success');
        $this->clearTermForm();
    }

    public function setActiveTerm(int $termId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        DB::transaction(function () use ($termId) {
            // Deactivate ALL terms across ALL sessions
            AcademicTerm::query()->where('is_active', true)->update(['is_active' => false]);
            // Activate this one
            AcademicTerm::query()->whereKey($termId)->update(['is_active' => true]);

            // Also ensure the parent session is set as active
            $term = AcademicTerm::query()->find($termId);
            if ($term) {
                AcademicSession::query()->where('is_active', true)->update(['is_active' => false]);
                AcademicSession::query()->whereKey($term->academic_session_id)->update(['is_active' => true]);
            }
        });

        $this->dispatch('alert', message: 'Active term updated.', type: 'success');
    }

    public function deleteTerm(int $termId): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        AcademicTerm::query()->whereKey($termId)->delete();

        if ($this->editingTermId === $termId) {
            $this->clearTermForm();
        }

        $this->dispatch('alert', message: 'Term deleted.', type: 'success');
    }

    /* ================================================================== */
    /*  RENDER                                                             */
    /* ================================================================== */

    public function render()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $sessions = AcademicSession::query()
            ->with(['terms' => fn($q) => $q->orderBy('term_number')])
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $activeTerm = AcademicTerm::active();

        return view('livewire.academics.sessions', [
            'sessions' => $sessions,
            'activeTerm' => $activeTerm,
        ]);
    }
}
