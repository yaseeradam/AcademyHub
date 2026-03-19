<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Models\InAppNotification;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Messages')]
class Index extends Component
{
    use WithFileUploads;

    public ?int $conversationId = null;

    public string $userSearch = '';
    public ?int $recipientId = null;

    public string $body = '';
    public $attachment = null;

    public function updatedUserSearch(): void
    {
        unset($this->recipientOptions);
    }

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('messages.access'), 403);
    }

    #[Computed]
    public function unreadByUser(): array
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $rows = Message::query()
            ->join('conversation_user as cu', function ($join) use ($user) {
                $join->on('cu.conversation_id', '=', 'messages.conversation_id')
                    ->where('cu.user_id', '=', $user->id);
            })
            ->where('messages.sender_id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereNull('cu.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'cu.last_read_at');
            })
            ->groupBy('messages.sender_id')
            ->get([
                'messages.sender_id as user_id',
                DB::raw('count(messages.id) as unread'),
            ]);

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->user_id] = (int) $row->unread;
        }

        return $map;
    }

    private function unreadByConversation(): array
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $rows = Message::query()
            ->join('conversation_user as cu', function ($join) use ($user) {
                $join->on('cu.conversation_id', '=', 'messages.conversation_id')
                    ->where('cu.user_id', '=', $user->id);
            })
            ->where('messages.sender_id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereNull('cu.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'cu.last_read_at');
            })
            ->groupBy('messages.conversation_id')
            ->get([
                'messages.conversation_id as conversation_id',
                DB::raw('count(messages.id) as unread'),
            ]);

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->conversation_id] = (int) $row->unread;
        }

        return $map;
    }

    #[Computed]
    public function conversations()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $allowedRoles = $this->allowedRecipientRoles($user);
        if (empty($allowedRoles)) {
            return collect();
        }

        $pivots = DB::table('conversation_user')
            ->where('user_id', $user->id)
            ->get(['conversation_id', 'last_read_at'])
            ->keyBy('conversation_id');

        $conversations = Conversation::query()
            ->whereIn('id', $pivots->keys())
            ->with([
                'participants:id,name,role,profile_photo',
                'messages' => fn ($q) => $q->latest('id')->limit(1)->with('sender:id,name,profile_photo'),
            ])
            ->get();

        $unreadByConversation = $this->unreadByConversation();

        return $conversations
            ->filter(function (Conversation $c) use ($user, $allowedRoles) {
                $others = $c->participants->where('id', '!=', $user->id);
                if ($others->isEmpty()) {
                    return false;
                }

                return $others->every(fn (User $u) => in_array($u->role, $allowedRoles, true));
            })
            ->map(function (Conversation $c) use ($user, $pivots) {
                $pivot = $pivots->get($c->id);
                $lastReadAt = $pivot?->last_read_at ? Carbon::parse($pivot->last_read_at) : null;
                $lastMessage = $c->messages->first();
                $lastMessageAt = $lastMessage?->created_at;

                $unread = $lastMessageAt && (! $lastReadAt || $lastMessageAt->gt($lastReadAt));
                $unreadCount = (int) ($unreadByConversation[$c->id] ?? 0);

                $others = $c->participants->where('id', '!=', $user->id)->values();
                $otherUser = $others->count() === 1 ? $others->first() : null;
                $title = $others->isEmpty()
                    ? 'Conversation'
                    : ($others->count() === 1 ? $others->first()->name : $others->pluck('name')->take(3)->join(', '));

                return [
                    'id' => $c->id,
                    'title' => $title,
                    'other_user_id' => $others->count() === 1 ? (int) $others->first()->id : null,
                    'other_user_photo_url' => $otherUser?->profile_photo_url,
                    'unread' => $unread,
                    'unread_count' => $unreadCount,
                    'last_message' => $lastMessage?->body,
                    'last_message_at' => $lastMessageAt,
                ];
            })
            ->sortByDesc(fn ($row) => $row['last_message_at']?->timestamp ?? 0)
            ->values();
    }

    #[Computed]
    public function recipientOptions()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $allowedRoles = $this->allowedRecipientRoles($user);
        if (empty($allowedRoles)) {
            return collect();
        }

        $query = User::query()
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereIn('role', $allowedRoles)
            ->orderBy('name');

        $search = trim($this->userSearch);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->limit(20)->get(['id', 'name', 'role', 'profile_photo']);
    }

    #[Computed]
    public function chatMessages()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if (! $this->conversationId) {
            return collect();
        }

        $this->assertConversationAccess($this->conversationId, $user);

        return Message::query()
            ->where('conversation_id', $this->conversationId)
            ->with('sender:id,name,profile_photo')
            ->orderBy('id')
            ->limit(300)
            ->get();
    }

    public function openConversation(int $id): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $this->assertConversationAccess($id, $user);

        $this->conversationId = $id;
        unset($this->chatMessages);
        $this->markConversationRead($id);
    }

    public function startConversation(?int $recipientId = null): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $recipientId = (int) ($recipientId ?? $this->recipientId ?? 0);
        if ($recipientId <= 0) {
            throw ValidationException::withMessages(['recipientId' => 'Select a user.']);
        }

        if ($recipientId === $user->id) {
            throw ValidationException::withMessages(['recipientId' => 'You cannot message yourself.']);
        }

        $recipient = User::query()->where('is_active', true)->findOrFail($recipientId);

        $allowedRoles = $this->allowedRecipientRoles($user);
        if (! in_array($recipient->role, $allowedRoles, true)) {
            throw ValidationException::withMessages(['recipientId' => 'You cannot message this user.']);
        }

        $existing = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
            ->whereHas('participants', fn ($q) => $q->where('users.id', $recipient->id))
            ->whereDoesntHave('participants', fn ($q) => $q->whereNotIn('users.id', [$user->id, $recipient->id]))
            ->value('id');

        if ($existing) {
            $this->conversationId = (int) $existing;
            $this->markConversationRead($this->conversationId);
            $this->recipientId = null;
            unset($this->chatMessages);
            return;
        }

        $conversation = DB::transaction(function () use ($user, $recipient) {
            $c = Conversation::query()->create(['created_by' => $user->id]);
            $c->participants()->attach([$user->id, $recipient->id]);
            return $c;
        });

        $this->conversationId = $conversation->id;
        $this->recipientId = null;
        $this->body = '';
        unset($this->conversations, $this->chatMessages);
        $this->dispatch('alert', message: 'Conversation started.', type: 'success');
    }

    public function send(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if (! $this->conversationId) {
            throw ValidationException::withMessages(['conversationId' => 'Select a conversation.']);
        }

        $this->assertConversationAccess($this->conversationId, $user);

        $data = $this->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:51200'],
        ]);

        $body = trim((string) ($data['body'] ?? ''));
        $hasAttachment = (bool) ($this->attachment);
        if ($body === '' && ! $hasAttachment) {
            throw ValidationException::withMessages(['body' => 'Type a message or attach a file.']);
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($hasAttachment) {
            $attachmentName = (string) ($this->attachment->getClientOriginalName() ?: 'attachment');
            $attachmentMime = (string) ($this->attachment->getMimeType() ?: 'application/octet-stream');
            $attachmentSize = (int) ($this->attachment->getSize() ?: 0);

            $attachmentPath = $this->attachment->storeAs(
                'myacademy/messages/'.$this->conversationId,
                now()->format('YmdHis').'_'.bin2hex(random_bytes(6)).'_'.$attachmentName,
                'local'
            );
        }

        try {
            $message = DB::transaction(function () use ($user, $body, $attachmentPath, $attachmentName, $attachmentMime, $attachmentSize) {
                $m = Message::query()->create([
                    'conversation_id' => $this->conversationId,
                    'sender_id' => $user->id,
                    'body' => $body !== '' ? $body : '',
                    'attachment_path' => $attachmentPath,
                    'attachment_name' => $attachmentName,
                    'attachment_mime' => $attachmentMime,
                    'attachment_size' => $attachmentSize,
                ]);

                DB::table('conversation_user')
                    ->where('conversation_id', $this->conversationId)
                    ->where('user_id', $user->id)
                    ->update(['last_read_at' => now()]);

                $recipientIds = DB::table('conversation_user')
                    ->where('conversation_id', $this->conversationId)
                    ->where('user_id', '!=', $user->id)
                    ->pluck('user_id')
                    ->all();

                foreach ($recipientIds as $rid) {
                    InAppNotification::query()->create([
                        'user_id' => (int) $rid,
                        'title' => 'New message',
                        'body' => $user->name.': '.mb_strimwidth($m->body !== '' ? $m->body : ($m->attachment_name ?: 'Attachment'), 0, 120, '...'),
                        'link' => route('messages', ['c' => $this->conversationId]),
                    ]);
                }

                return $m;
            });
        } catch (\Throwable $e) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }

            throw $e;
        }

        $this->body = '';
        $this->attachment = null;
        unset($this->chatMessages, $this->conversations);
        $this->markConversationRead($this->conversationId);
    }

    private function markConversationRead(int $conversationId): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);
    }

    private function allowedRecipientRoles(User $user): array
    {
        return match ($user->role) {
            'admin' => ['admin', 'teacher', 'bursar'],
            'teacher' => ['admin', 'teacher', 'bursar'],
            'bursar' => ['admin', 'teacher', 'bursar'],
            default => [],
        };
    }

    private function assertConversationAccess(int $conversationId, User $user): void
    {
        $allowed = DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($allowed, 403);

        $allowedRoles = $this->allowedRecipientRoles($user);
        if (empty($allowedRoles)) {
            abort(403);
        }

        $otherRoles = DB::table('conversation_user')
            ->join('users', 'users.id', '=', 'conversation_user.user_id')
            ->where('conversation_id', $conversationId)
            ->where('users.id', '!=', $user->id)
            ->pluck('users.role')
            ->unique();

        if ($otherRoles->isEmpty()) {
            abort(403);
        }

        foreach ($otherRoles as $role) {
            if (! in_array($role, $allowedRoles, true)) {
                abort(403);
            }
        }
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user && $user->hasPermission('messages.access'), 403);

        $defaultConversation = (int) request('c', 0);
        if (! $this->conversationId && $defaultConversation > 0) {
            $this->openConversation($defaultConversation);
        }

        return view('livewire.messages.index', [
            'me' => $user,
        ]);
    }
}
