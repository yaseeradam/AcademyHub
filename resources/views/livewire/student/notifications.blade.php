<div class="space-y-6">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 p-6 shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Notifications</h1>
                <p class="mt-1 text-sm text-green-100">Your latest updates and alerts</p>
            </div>
            @if($notifications->whereNull('read_at')->count() > 0)
                <button wire:click="markAllRead" class="rounded-xl bg-white/20 px-4 py-2 text-sm font-bold text-white hover:bg-white/30 transition">
                    Mark all read
                </button>
            @endif
        </div>
    </div>

    @if($notifications->isEmpty())
        <div class="rounded-2xl bg-white p-12 text-center shadow-sm border border-gray-100">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-green-50">
                <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900">No notifications yet</h3>
            <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($notifications as $n)
                @php
                    $isUnread = is_null($n->read_at);
                    $icon = match($n->type) {
                        'homework' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'path' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8 M16 17H8'],
                        'grade'    => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600',   'path' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        'result'   => ['bg' => 'bg-amber-100',  'text' => 'text-amber-600',  'path' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                        default    => ['bg' => 'bg-green-100',  'text' => 'text-green-600',  'path' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                    };
                @endphp
                <div wire:key="notif-{{ $n->id }}"
                     class="flex items-start gap-4 rounded-2xl border bg-white p-4 shadow-sm transition {{ $isUnread ? 'border-green-200 bg-green-50/40' : 'border-gray-100' }}"
                     wire:click="markRead({{ $n->id }})">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $icon['bg'] }}">
                        <svg class="h-5 w-5 {{ $icon['text'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-bold text-gray-900 {{ $isUnread ? '' : 'font-semibold' }}">{{ $n->title }}</p>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($isUnread)
                                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                @endif
                                <button wire:click.stop="delete({{ $n->id }})" class="text-gray-300 hover:text-red-400 transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        @if($n->body)
                            <p class="mt-0.5 text-sm text-gray-600">{{ $n->body }}</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
