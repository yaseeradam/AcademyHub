<?php

namespace App\Livewire\Events;

use App\Models\InAppNotification;
use App\Models\SchoolEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Events')]
class Index extends Component
{
    public ?int $editingId = null;
    public string $title = '';
    public ?string $description = null;
    public string $startsAt = '';
    public ?string $endsAt = null;
    public ?string $location = null;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($this->startsAt === '') {
            $this->startsAt = now()->addDay()->format('Y-m-d\TH:i');
        }
    }

    public function edit(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $e = SchoolEvent::query()->findOrFail($id);
        $this->editingId = $e->id;
        $this->title = $e->title;
        $this->description = $e->description;
        $this->startsAt = $e->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->endsAt = $e->ends_at?->format('Y-m-d\TH:i');
        $this->location = $e->location;
    }

    public function clearForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = null;
        $this->startsAt = now()->addDay()->format('Y-m-d\TH:i');
        $this->endsAt = null;
        $this->location = null;
    }

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'startsAt' => ['required', 'date'],
            'endsAt' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $startsAt = now()->parse($data['startsAt']);
        $endsAt = $data['endsAt'] ? now()->parse($data['endsAt']) : null;

        if ($endsAt && $endsAt->lte($startsAt)) {
            throw ValidationException::withMessages([
                'endsAt' => 'End time must be after start time.',
            ]);
        }

        $event = SchoolEvent::query()->updateOrCreate(
            ['id' => $this->editingId],
            [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'location' => $data['location'] ?? null,
                'created_by' => $user->id,
            ]
        );

        $this->editingId = $event->id;

        $this->dispatch('alert', message: 'Event saved.', type: 'success');
    }

    public function delete(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        SchoolEvent::query()->whereKey($id)->delete();

        if ($this->editingId === $id) {
            $this->clearForm();
        }

        $this->dispatch('alert', message: 'Event deleted.', type: 'success');
    }

    public function notifyStaff(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $event = SchoolEvent::query()->findOrFail($id);

        $users = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'teacher', 'bursar'])
            ->get(['id']);

        DB::transaction(function () use ($users, $event) {
            foreach ($users as $u) {
                InAppNotification::query()->create([
                    'user_id' => $u->id,
                    'title' => 'New event scheduled',
                    'body' => $event->title,
                    'link' => '/events',
                ]);
            }
        });

        $this->dispatch('alert', message: 'Staff notified.', type: 'success');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if (! in_array($user->role, ['admin', 'teacher', 'bursar'], true)) {
            abort(403);
        }

        $events = SchoolEvent::query()
            ->orderBy('starts_at')
            ->get();

        $now = now();
        $upcoming = $events->filter(fn (SchoolEvent $e) => $e->starts_at && $e->starts_at->gte($now))->values();
        $past = $events->filter(fn (SchoolEvent $e) => $e->starts_at && $e->starts_at->lt($now))->sortByDesc('starts_at')->values();

        return view('livewire.events.index', [
            'isAdmin' => $user->role === 'admin',
            'upcoming' => $upcoming,
            'past' => $past,
        ]);
    }
}

