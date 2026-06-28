<?php

namespace App\Livewire\Announcements;

use App\Models\Announcement;
use App\Models\InAppNotification;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Announcements')]
class Index extends Component
{
    public string $title = '';
    public string $body = '';
    public string $audience = 'all';
    public ?int $editingId = null;

    public function save(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        abort_unless($user->hasPermission('announcements.manage'), 403);

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'audience' => ['required', 'string', 'in:all,staff,admin,teacher,bursar'],
        ]);

        $announcement = Announcement::query()->updateOrCreate(
            ['id' => $this->editingId],
            [
                'title' => $data['title'],
                'body' => $data['body'],
                'audience' => $data['audience'],
                'created_by' => $user->id,
            ]
        );

        Audit::log($this->editingId ? 'announcement.updated' : 'announcement.created', $announcement, [
            'audience' => $announcement->audience,
        ]);

        $this->editingId = $announcement->id;
        $this->dispatch('alert', message: 'Announcement saved.', type: 'success');
    }

    public function edit(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('announcements.manage'), 403);

        $a = Announcement::query()->findOrFail($id);
        $this->editingId = $a->id;
        $this->title = $a->title;
        $this->body = $a->body;
        $this->audience = $a->audience;
    }

    public function clearForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->body = '';
        $this->audience = 'all';
    }

    public function publish(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('announcements.manage'), 403);

        $a = Announcement::query()->findOrFail($id);
        $a->published_at = now();
        $a->save();

        Audit::log('announcement.published', $a, ['audience' => $a->audience]);

        $this->notifyAudience($a);
        $this->dispatch('alert', message: 'Announcement published.', type: 'success');
    }

    public function unpublish(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('announcements.manage'), 403);

        $a = Announcement::query()->findOrFail($id);
        $a->published_at = null;
        $a->save();

        Audit::log('announcement.unpublished', $a);

        $this->dispatch('alert', message: 'Announcement unpublished.', type: 'success');
    }

    public function delete(int $id): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('announcements.manage'), 403);

        $a = Announcement::query()->findOrFail($id);
        $a->delete();

        Audit::log('announcement.deleted', $a);

        if ($this->editingId === $id) {
            $this->clearForm();
        }

        $this->dispatch('alert', message: 'Announcement deleted.', type: 'success');
    }

    private function notifyAudience(Announcement $announcement): void
    {
        $query = User::query()->where('is_active', true);

        if ($announcement->audience === 'staff') {
            $query->whereIn('role', ['admin', 'teacher', 'bursar']);
        } elseif ($announcement->audience !== 'all') {
            $query->where('role', $announcement->audience);
        }

        $users = $query->get(['id']);
        if ($users->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($users, $announcement) {
            foreach ($users as $u) {
                InAppNotification::query()->create([
                    'user_id' => $u->id,
                    'title' => 'New announcement',
                    'body' => $announcement->title,
                    'link' => '/announcements',
                ]);
            }
        });
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $audience = $user->role;
        $canManage = $user->hasPermission('announcements.manage');

        $announcements = Announcement::query()
            ->when(! $canManage, function ($q) use ($audience) {
                $q->whereNotNull('published_at')
                    ->where(function ($q) use ($audience) {
                        $q->where('audience', 'all')
                            ->orWhere('audience', 'staff')
                            ->orWhere('audience', $audience);
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('livewire.announcements.index', [
            'announcements' => $announcements,
            'isAdmin' => $canManage,
        ]);
    }
}
