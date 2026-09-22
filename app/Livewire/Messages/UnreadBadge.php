<?php

namespace App\Livewire\Messages;

use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UnreadBadge extends Component
{
    public int $count = 0;
    public int $lastCount = 0;
    public bool $initialized = false;

    public function mount(): void
    {
        $this->refreshCount();
        $this->lastCount = $this->count;
        $this->initialized = false;
    }

    public function refreshCount(): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->count = 0;
            $this->lastCount = 0;
            $this->initialized = false;
            return;
        }

        $newCount = (int) Message::query()
            ->join('conversation_user as cu', function ($join) use ($user) {
                $join->on('cu.conversation_id', '=', 'messages.conversation_id')
                    ->where('cu.user_id', '=', $user->id);
            })
            ->where('messages.sender_id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereNull('cu.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'cu.last_read_at');
            })
            ->count(DB::raw('messages.id'));

        if (! $this->initialized) {
            $this->count = $newCount;
            $this->lastCount = $newCount;
            $this->initialized = true;
            return;
        }

        if ($newCount > $this->lastCount) {
            $this->dispatch('messages-unread', count: $newCount);
        }

        $this->count = $newCount;
        $this->lastCount = $newCount;
    }

    public function render()
    {
        return view('livewire.messages.unread-badge');
    }
}

