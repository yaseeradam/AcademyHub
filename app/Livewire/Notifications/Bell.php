<?php

namespace App\Livewire\Notifications;

use App\Models\InAppNotification;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Bell extends Component
{
    #[Computed]
    public function unreadCount(): int
    {
        $user = auth()->user();
        if (! $user) return 0;

        return (int) InAppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    #[Computed]
    public function notifications()
    {
        $user = auth()->user();
        if (! $user) return collect();

        return InAppNotification::query()
            ->where('user_id', $user->id)
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('id')
            ->limit(30)
            ->get();
    }

    public function markRead(int $id): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        InAppNotification::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->update(['read_at' => now()]);

        unset($this->unreadCount, $this->notifications);
    }

    public function markAllRead(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        InAppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        unset($this->unreadCount, $this->notifications);
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }
}
