@php($user = auth()->user())

@php($activeConversation = $conversationId ? $this->conversations->firstWhere('id', $conversationId) : null)
@php($activeTitle = data_get($activeConversation, 'title', 'Chat'))
@php($activePhotoUrl = data_get($activeConversation, 'other_user_photo_url'))

<div class="-mx-6 -my-6 overflow-hidden">
<div class="flex h-[calc(100vh-4rem)] bg-slate-100">

    {{-- ===================== SIDEBAR ===================== --}}
    {{--
        Mobile: full width, hidden when a conversation is open
        Desktop: fixed width, always visible
    --}}
    <div class="flex flex-col bg-white border-r border-slate-100 shadow-sm
                {{ $conversationId ? 'hidden lg:flex' : 'flex w-full' }}
                lg:flex lg:w-80 xl:w-96">

        {{-- Header --}}
        <div class="relative overflow-hidden px-5 py-5 flex-shrink-0" style="background-color: #1a2e4a;">
            <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 70%);"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-semibold uppercase tracking-widest" style="color:#93c5fd;">Messaging</span>
                    </div>
                    <h1 class="text-lg font-bold text-white">Messages</h1>
                    <p class="text-xs mt-0.5" style="color:#93c5fd;">{{ $user->name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="openNewChat"
                            class="grid h-9 w-9 place-items-center rounded-xl text-white transition-all"
                            style="background:rgba(255,255,255,0.15);"
                            title="New message">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                    </button>
                    <a href="{{ route('more-features') }}"
                       class="rounded-xl px-3 py-1.5 text-xs font-semibold text-white transition-all"
                       style="background:rgba(255,255,255,0.12);">
                        ← Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Conversation list --}}
        <div class="flex-1 overflow-y-auto">
            @forelse($this->conversations as $conv)
                @php($isActive = (int) $conversationId === (int) $conv['id'])
                @php($unread = (int) ($conv['unread_count'] ?? 0))
                <button type="button"
                        wire:click="openConversation({{ $conv['id'] }})"
                        class="group flex w-full items-center gap-3 border-b border-slate-50 px-4 py-3.5 text-left transition-colors {{ $isActive ? 'bg-amber-50' : 'hover:bg-slate-50' }}">
                    <div class="relative flex-shrink-0">
                        @if(!empty($conv['other_user_photo_url']))
                            <img src="{{ $conv['other_user_photo_url'] }}" alt="{{ $conv['title'] }}"
                                 class="h-11 w-11 rounded-full object-cover ring-2 {{ $isActive ? 'ring-amber-400' : 'ring-slate-100' }}"/>
                        @else
                            <div class="grid h-11 w-11 place-items-center rounded-full text-sm font-bold text-white {{ $isActive ? 'bg-amber-500' : 'bg-slate-700' }}">
                                {{ strtoupper(substr($conv['title'], 0, 1)) }}
                            </div>
                        @endif
                        @if($unread > 0)
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[9px] font-black text-white ring-2 ring-white">
                                {{ $unread > 9 ? '9+' : $unread }}
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <div class="truncate text-sm font-semibold {{ $isActive ? 'text-amber-700' : 'text-slate-800' }}">
                                {{ $conv['title'] }}
                            </div>
                            @if($conv['last_message_at'])
                                <div class="flex-shrink-0 text-[10px] text-slate-400">
                                    {{ $conv['last_message_at']->diffForHumans(short: true) }}
                                </div>
                            @endif
                        </div>
                        @if($conv['last_message'])
                            <div class="mt-0.5 truncate text-xs {{ $unread > 0 ? 'font-semibold text-slate-700' : 'text-slate-400' }}">
                                {{ $conv['last_message'] }}
                            </div>
                        @else
                            <div class="mt-0.5 text-xs text-slate-400 italic">No messages yet</div>
                        @endif
                    </div>
                    <svg class="h-4 w-4 flex-shrink-0 text-slate-200 lg:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @empty
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-slate-700">No conversations yet</div>
                    <div class="mt-1 text-xs text-slate-400">Tap + to start a new message</div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ===================== CHAT PANEL ===================== --}}
    {{--
        Mobile: full width, hidden when no conversation is open
        Desktop: flex-1, always visible
    --}}
    <div class="flex flex-col bg-slate-50
                {{ $conversationId ? 'flex w-full' : 'hidden lg:flex' }}
                lg:flex lg:flex-1">

        @if(!$conversationId)
            {{-- Desktop empty state --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="text-center">
                    <div class="mx-auto mb-5 grid h-20 w-20 place-items-center rounded-3xl bg-white shadow-sm ring-1 ring-slate-100 text-slate-300">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div class="text-base font-bold text-slate-700">No conversation selected</div>
                    <div class="mt-1 text-sm text-slate-400">Pick a conversation or start a new one</div>
                    <button wire:click="openNewChat"
                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        New Message
                    </button>
                </div>
            </div>
        @else
            {{-- Chat header --}}
            <div class="flex items-center gap-3 border-b border-slate-100 bg-white px-4 py-3.5 shadow-sm flex-shrink-0">
                {{-- Back button — mobile only --}}
                <button wire:click="$set('conversationId', null)"
                        class="grid h-9 w-9 flex-shrink-0 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 transition-colors lg:hidden">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div class="relative flex-shrink-0">
                    @if ($activePhotoUrl)
                        <img src="{{ $activePhotoUrl }}" alt="{{ $activeTitle }}"
                             class="h-10 w-10 rounded-full object-cover ring-2 ring-amber-100"/>
                    @else
                        <div class="grid h-10 w-10 place-items-center rounded-full bg-slate-700 text-sm font-bold text-white">
                            {{ strtoupper(substr($activeTitle !== '' ? $activeTitle : 'C', 0, 1)) }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-emerald-400"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-bold text-slate-900">{{ $activeTitle }}</div>
                    <div class="text-xs text-emerald-500 font-semibold">Online</div>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3" wire:poll.5s>
                @foreach($this->chatMessages as $m)
                    @php($isMe = (int) $m->sender_id === (int) $me->id)
                    <div class="flex items-end gap-2 {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        @if(!$isMe)
                            <div class="flex-shrink-0">
                                @if ($m->sender?->profile_photo_url)
                                    <img src="{{ $m->sender->profile_photo_url }}" alt="{{ $m->sender->name }}"
                                         class="h-8 w-8 rounded-full object-cover ring-1 ring-slate-200"/>
                                @else
                                    <x-avatar :name="$m->sender?->name ?? 'User'" size="32" class="ring-1 ring-slate-200"/>
                                @endif
                            </div>
                        @endif
                        <div class="max-w-[80%] sm:max-w-[72%]">
                            @if(!$isMe)
                                <div class="mb-1 ml-1 text-xs font-semibold text-slate-500">{{ $m->sender?->name }}</div>
                            @endif
                            <div class="rounded-2xl px-4 py-2.5 shadow-sm {{ $isMe ? 'rounded-br-sm bg-amber-500 text-white' : 'rounded-bl-sm bg-white text-slate-800 ring-1 ring-slate-100' }}">
                                @if(trim((string) $m->body) !== '')
                                    <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ $m->body }}</p>
                                @endif
                                @if($m->attachment_path)
                                    <div class="mt-2">
                                        <a href="{{ route('messages.attachments.download', $m) }}" target="_blank"
                                           class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $isMe ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                                            </svg>
                                            {{ $m->attachment_name ?: 'attachment' }}
                                        </a>
                                        @if($m->attachment_size)
                                            <div class="mt-0.5 text-[10px] {{ $isMe ? 'text-amber-100' : 'text-slate-400' }}">{{ number_format($m->attachment_size / 1024, 1) }} KB</div>
                                        @endif
                                    </div>
                                @endif
                                <div class="mt-1 text-right text-[10px] {{ $isMe ? 'text-amber-100' : 'text-slate-400' }}">
                                    {{ $m->created_at?->format('g:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Input bar --}}
            <div class="border-t border-slate-100 bg-white px-4 py-3 flex-shrink-0">
                @if($attachment)
                    <div class="mb-2 flex items-center justify-between gap-2 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800 ring-1 ring-amber-200">
                        <div class="truncate font-semibold">📎 {{ $attachment->getClientOriginalName() }}</div>
                        <button type="button" wire:click="$set('attachment', null)" class="font-bold text-red-500 hover:text-red-600">Remove</button>
                    </div>
                @endif
                <div class="flex items-end gap-2">
                    <label class="grid h-10 w-10 flex-shrink-0 cursor-pointer place-items-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200" title="Attach file">
                        <input type="file" wire:model="attachment" class="hidden"/>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                        </svg>
                    </label>
                    <textarea wire:model="body" rows="1"
                              class="flex-1 resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100"
                              placeholder="Type a message..."
                              wire:keydown.enter.prevent="send"></textarea>
                    <button type="button" wire:click="send"
                            class="grid h-10 w-10 flex-shrink-0 place-items-center rounded-xl bg-amber-500 text-white shadow-sm transition hover:bg-amber-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
                @error('body') <div class="mt-1.5 text-xs text-red-500">{{ $message }}</div> @enderror
                @error('attachment') <div class="mt-1.5 text-xs text-red-500">{{ $message }}</div> @enderror
            </div>
        @endif
    </div>

</div>

{{-- ===================== NEW MESSAGE MODAL ===================== --}}
@if($showNewChat)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
         style="background:rgba(0,0,0,0.4);"
         wire:click.self="closeNewChat">
        {{-- Sheet on mobile, centered modal on sm+ --}}
        <div class="w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-white shadow-2xl overflow-hidden">
            {{-- Handle bar — mobile only --}}
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="h-1 w-10 rounded-full bg-slate-200"></div>
            </div>
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="text-sm font-bold text-slate-900">New Message</div>
                <button wire:click="closeNewChat" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            {{-- Search --}}
            <div class="px-5 py-3 border-b border-slate-100">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.250ms="userSearch"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-800 placeholder-slate-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100"
                           placeholder="Search by name or email..."
                           autofocus />
                </div>
            </div>
            {{-- User list --}}
            <div class="max-h-64 sm:max-h-72 overflow-y-auto">
                @foreach($this->recipientOptions as $u)
                    <button type="button"
                            wire:click="startConversation({{ $u->id }})"
                            class="flex w-full items-center gap-3 px-5 py-3.5 text-left transition-colors hover:bg-amber-50 border-b border-slate-50 last:border-0">
                        @if ($u->profile_photo_url)
                            <img src="{{ $u->profile_photo_url }}" alt="{{ $u->name }}"
                                 class="h-10 w-10 flex-shrink-0 rounded-full object-cover ring-2 ring-slate-100"/>
                        @else
                            <div class="grid h-10 w-10 flex-shrink-0 place-items-center rounded-full bg-slate-700 text-sm font-bold text-white">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-800">{{ $u->name }}</div>
                            <div class="text-xs capitalize text-slate-400">{{ $u->role }}</div>
                        </div>
                        <svg class="h-4 w-4 flex-shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                @endforeach
                @if($this->recipientOptions->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <div class="text-sm font-semibold text-slate-600">
                            {{ trim($userSearch) ? 'No users match "' . $userSearch . '"' : 'No users available' }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

</div>
