<div class="space-y-6">

    {{-- Hero Card --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative flex items-center justify-between px-8 py-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color:#93c5fd;">System Activity</span>
                </div>
                <h2 class="text-4xl font-bold text-white tracking-tight">Audit Logs</h2>
                <p class="mt-2 text-base font-medium" style="color:#93c5fd;">Track important actions — backups, approvals, user changes</p>
            </div>
            <a href="{{ route('settings.index') }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
               style="background:rgba(255,255,255,0.12);">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
        <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
            <div class="grid h-9 w-9 place-items-center rounded-xl bg-amber-50 text-amber-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2a1 1 0 0 1-.293.707L13 13.414V19a1 1 0 0 1-.553.894l-4 2A1 1 0 0 1 7 21v-7.586L3.293 6.707A1 1 0 0 1 3 6V4z"/></svg>
            </div>
            <div class="text-sm font-bold text-slate-800">Filter Logs</div>
        </div>
        <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Action</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="action" type="text" placeholder="e.g. backup.created"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100" />
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">User</label>
                <select wire:model.live="userId"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 px-4 text-sm text-slate-800 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100">
                    <option value="">All Users</option>
                    @foreach ($this->users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">From</label>
                <input wire:model.live="from" type="date"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 px-4 text-sm text-slate-800 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100" />
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">To</label>
                <input wire:model.live="to" type="date"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 px-4 text-sm text-slate-800 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100" />
            </div>
        </div>
    </div>

    {{-- Logs List --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="text-sm font-bold text-slate-800">Recent Activity</div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">
                {{ $this->logs->total() }} entries
            </span>
        </div>

        @php
            $actionColor = function(string $action): string {
                if (str_contains($action, 'delete') || str_contains($action, 'void') || str_contains($action, 'remove'))
                    return 'bg-red-100 text-red-700';
                if (str_contains($action, 'create') || str_contains($action, 'add') || str_contains($action, 'backup'))
                    return 'bg-emerald-100 text-emerald-700';
                if (str_contains($action, 'update') || str_contains($action, 'edit') || str_contains($action, 'change'))
                    return 'bg-blue-100 text-blue-700';
                if (str_contains($action, 'login') || str_contains($action, 'logout') || str_contains($action, 'auth'))
                    return 'bg-violet-100 text-violet-700';
                if (str_contains($action, 'approve') || str_contains($action, 'publish'))
                    return 'bg-amber-100 text-amber-700';
                return 'bg-slate-100 text-slate-600';
            };
        @endphp

        <div class="divide-y divide-slate-100">
            @forelse ($this->logs as $log)
                <div class="flex items-start gap-4 px-6 py-5 transition hover:bg-slate-50/60">

                    {{-- Avatar --}}
                    <div class="mt-0.5 flex-shrink-0 grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 text-sm font-bold text-white shadow-sm">
                        {{ mb_strtoupper(mb_substr($log->user?->name ?? 'S', 0, 1)) }}
                    </div>

                    {{-- Main content --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Action badge --}}
                            <span class="rounded-lg px-2.5 py-1 text-xs font-bold {{ $actionColor($log->action) }}">
                                {{ $log->action }}
                            </span>
                            {{-- Target --}}
                            @if($log->auditable_type)
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1">
                            <span class="text-sm font-semibold text-slate-800">{{ $log->user?->name ?? 'System' }}</span>
                            @if($log->user?->email)
                                <span class="text-sm text-slate-500">{{ $log->user->email }}</span>
                            @endif
                            @if($log->user?->role)
                                <span class="rounded-full bg-orange-50 px-2 py-0.5 text-xs font-semibold text-orange-600">{{ $log->user->role }}</span>
                            @endif
                        </div>

                        {{-- Meta --}}
                        @if(is_array($log->meta) && $log->meta !== [])
                            <details class="mt-2 group">
                                <summary class="inline-flex cursor-pointer items-center gap-1 text-xs font-semibold text-slate-400 hover:text-slate-600 select-none list-none">
                                    <svg class="h-3.5 w-3.5 transition group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                                    Show details
                                </summary>
                                <pre class="mt-2 max-w-2xl overflow-x-auto whitespace-pre-wrap rounded-xl bg-slate-50 p-4 text-xs leading-relaxed text-slate-700 ring-1 ring-slate-200">{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @endif
                    </div>

                    {{-- Timestamp --}}
                    <div class="flex-shrink-0 text-right">
                        <div class="text-sm font-semibold text-slate-700">{{ $log->created_at?->format('M j, Y') }}</div>
                        <div class="mt-0.5 text-xs text-slate-400">{{ $log->created_at?->format('g:i A') }}</div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100">
                        <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-600">No audit logs found.</p>
                    <p class="mt-1 text-xs text-slate-400">Try adjusting your filters.</p>
                </div>
            @endforelse
        </div>

        @if($this->logs->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $this->logs->links() }}
            </div>
        @endif
    </div>

</div>
