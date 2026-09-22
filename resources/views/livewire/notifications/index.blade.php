<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50" wire:poll.20s>
    <x-page-header title="Notifications" subtitle="In-app notifications and activity." accent="more">
        <x-slot:actions>
            <a href="{{ route('more-features') }}" class="btn-outline">Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6">
        <div class="rounded-3xl bg-white/80 backdrop-blur-xl p-6 shadow-2xl shadow-indigo-500/10 ring-1 ring-black/5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-lg font-bold text-gray-900">Inbox</div>
                    <div class="mt-1 flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-sm font-semibold text-gray-600">{{ $unreadCount }} unread</span>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="markAllRead" class="rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-shadow duration-200" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Mark all read
                    </button>
                    <button type="button" wire:click="clearAll" class="rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-500/30 hover:shadow-xl hover:shadow-rose-500/40 transition-shadow duration-200" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Clear all
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($items as $n)
                <div class="group relative rounded-2xl bg-white/90 backdrop-blur-sm p-5 shadow-lg ring-1 ring-black/5 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 {{ !$n->read_at ? 'ring-2 ring-indigo-400/30 bg-gradient-to-r from-indigo-50/50 to-purple-50/50' : '' }}">
                    @if(!$n->read_at)
                        <div class="absolute -left-0.5 top-1/2 -translate-y-1/2 h-16 w-1 rounded-full bg-gradient-to-b from-indigo-500 to-purple-600 shadow-md"></div>
                    @endif
                    
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br {{ !$n->read_at ? 'from-indigo-500 to-purple-600' : 'from-gray-400 to-gray-500' }} shadow-lg {{ !$n->read_at ? 'shadow-indigo-500/50' : 'shadow-gray-500/30' }} transition-all duration-300 group-hover:scale-110 group-hover:rotate-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-base font-bold text-gray-900">{{ $n->title }}</h3>
                                        @if(!$n->read_at)
                                            <span class="rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-white shadow-lg shadow-emerald-500/30">New</span>
                                        @endif
                                    </div>
                                    @if($n->body)
                                        <p class="mt-2 text-sm leading-relaxed text-gray-700">{{ $n->body }}</p>
                                    @endif
                                    <div class="mt-3 flex items-center gap-2 text-xs font-medium text-gray-500">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $n->created_at?->diffForHumans() }}
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    @if($n->link)
                                        <a href="{{ $n->link }}" wire:click="markRead({{ $n->id }})" class="rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-shadow duration-200 whitespace-nowrap">
                                            Open
                                        </a>
                                    @endif
                                    @if(!$n->read_at)
                                        <button type="button" wire:click="markRead({{ $n->id }})" class="rounded-xl bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-md ring-1 ring-gray-200 hover:bg-gray-50 hover:shadow-lg transition-shadow duration-200 whitespace-nowrap">
                                            Mark read
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border-2 border-dashed border-gray-300 bg-white/60 backdrop-blur-sm p-12 text-center shadow-xl shadow-gray-900/5">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 shadow-lg">
                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-gray-600">No notifications yet.</p>
                    <p class="mt-1 text-xs text-gray-500">You're all caught up!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
