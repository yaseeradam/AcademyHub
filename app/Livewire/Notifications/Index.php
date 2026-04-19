<?php

namespace App\Livewire\Notifications;

use App\Models\InAppNotification;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Notifications')]
class Index extends Component
{
    public function markAllRead(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        InAppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->dispatch('alert', message: 'All notifications marked as read.', type: 'success');
        $this->dispatch('notifications-updated');
    }

    public function markRead(int $id): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        InAppNotification::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->update(['read_at' => now()]);
    }

    public function clearAll(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        DB::transaction(function () use ($user) {
            InAppNotification::query()->where('user_id', $user->id)->delete();
        });

        $this->dispatch('alert', message: 'Notifications cleared.', type: 'success');
        $this->dispatch('notifications-updated');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $items = InAppNotification::query()
            ->where('user_id', $user->id)
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('livewire.notifications.index', [
            'items' => $items,
            'unreadCount' => (int) $items->whereNull('read_at')->count(),
        ]);
    }
}

