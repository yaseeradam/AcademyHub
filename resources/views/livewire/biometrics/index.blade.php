<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-3xl shadow-2xl" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/20 via-transparent to-transparent"></div>
        <div class="relative flex flex-col gap-6 px-8 py-8 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-emerald-300">Live Hardware Link</span>
                    @if($lastPingAt)
                        <span class="text-[11px] text-slate-300 bg-white/10 px-2 py-0.5 rounded-full font-mono">Pinged at {{ $lastPingAt }}</span>
                    @endif
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">ZKTeco K40 Gate &amp; Biometrics Hub</h1>
                <p class="text-sm font-medium text-slate-300 max-w-2xl">
                    Live hardware terminal health, real-time student/staff scans, dual-shift timetable, and automated parent WhatsApp notifications.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button wire:click="refreshGateStatus" type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-white/10 hover:bg-white/20 rounded-xl border border-white/15 transition-all">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Ping Terminals
                </button>
                <button wire:click="$set('showTerminalModal', true)" type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-white/10 hover:bg-white/20 rounded-xl border border-white/15 transition-all">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Configure IPs
                </button>
                <button wire:click="$set('showWhatsAppModal', true)" type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-md transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.586 1.861.979 3.013.979 3.182 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.766-5.768-5.766zm9.969 5.766c0 5.523-4.477 10-10 10-1.753 0-3.4-.452-4.836-1.246l-5.164 1.352 1.378-5.034c-.886-1.492-1.378-3.23-1.378-5.072 0-5.523 4.477-10 10-10s10 4.477 10 10z"/></svg>
                    Parent WhatsApp
                </button>
            </div>
        </div>
    </div>

    {{-- Offline Warning Banner (if any terminal is down) --}}
    @php
        $offlineTerminals = collect($terminals)->where('status', 'Offline');
    @endphp
    @if($offlineTerminals->isNotEmpty())
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl dark:bg-rose-950/40 dark:border-rose-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <h3 class="text-sm font-bold text-rose-800 dark:text-rose-200">Terminal Connection Notice</h3>
                        <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">
                            {{ $offlineTerminals->count() }} gate terminal(s) unreached:
                            <strong>{{ $offlineTerminals->pluck('name')->implode(', ') }}</strong>.
                            Verify ethernet cable connection, local subnet routing, and port 4370.
                        </p>
                    </div>
                </div>
                <button wire:click="refreshGateStatus" class="text-xs font-semibold px-3 py-1.5 bg-rose-600 text-white rounded-lg hover:bg-rose-700">
                    Re-test Ping
                </button>
            </div>
        </div>
    @endif

    {{-- Terminal Status Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($terminals as $index => $term)
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            @if(($term['status'] ?? '') === 'Online')
                                <span class="h-3 w-3 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Online &bull; {{ $term['latency'] ?? 'Connected' }}</span>
                            @else
                                <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Offline</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1">{{ $term['name'] }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $term['location'] ?? 'Gate Terminal' }}</p>
                    </div>
                    <span class="p-2 rounded-xl bg-slate-50 dark:bg-slate-700 text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                    </span>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Device IP &amp; Port:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-white">{{ $term['ip'] }}:{{ $term['port'] ?? 4370 }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Model:</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $term['device'] ?? 'ZKTeco K40' }}</span>
                    </div>
                    @if(!empty($term['error']))
                        <div class="text-[11px] text-rose-500 font-mono mt-1">
                            {{ $term['error'] }}
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex items-center justify-between gap-2">
                    <span class="text-[11px] text-slate-400">Last test: {{ $term['last_checked'] ?? 'Just now' }}</span>
                    <button wire:click="removeTerminal({{ $index }})" wire:confirm="Remove this terminal from monitor?" class="text-[11px] text-slate-400 hover:text-rose-500">
                        Remove
                    </button>
                </div>
            </div>
        @endforeach

        {{-- Add Terminal Action Card --}}
        <div wire:click="$set('showTerminalModal', true)" class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:border-indigo-500 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all">
            <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <div class="text-sm font-bold text-slate-800 dark:text-white">Add Gate Terminal</div>
            <div class="text-xs text-slate-500 mt-0.5">Configure additional K40 terminal IP</div>
        </div>
    </div>

    {{-- Today's Real-time Punch Feeds & Shift Guide --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Today's Live Student Punches --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Today's Gate Punch Logs</h3>
                    <p class="text-xs text-slate-500">Biometric arrivals and departures recorded today</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="triggerTestPopup" type="button" class="px-2.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg dark:bg-indigo-900/40 dark:text-indigo-300">
                        ⚡ Sim Arrival
                    </button>
                    <button wire:click="triggerDepartureTest" type="button" class="px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg dark:bg-emerald-900/40 dark:text-emerald-300">
                        👋 Sim Departure
                    </button>
                </div>
            </div>

            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                @forelse($studentMarks as $mark)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/60 flex items-center justify-center font-bold text-indigo-700 dark:text-indigo-300 text-xs">
                                {{ substr($mark->student->full_name ?? 'S', 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $mark->student->full_name ?? 'Unknown Student' }}</div>
                                <div class="text-xs text-slate-400">
                                    {{ $mark->student->schoolClass->name ?? 'Class' }} &bull; {{ $mark->student->admission_number ?? '' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $mark->status === 'Present' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                {{ $mark->status }}
                            </span>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $mark->note }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400 text-xs">
                        No biometric student scans recorded yet today.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Shift Timetable & Links --}}
        <div class="space-y-5">
            {{-- Dual Shift Reference --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Dual-Shift Schedule Reference</h3>

                <div class="p-3.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/40">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300">Western Section</span>
                        <span class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-400">Morning Shift</span>
                    </div>
                    <div class="text-xs text-indigo-800 dark:text-indigo-200 mt-1 font-mono">7:00 AM – 12:30 PM</div>
                    <div class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-0.5">Late cutoff after: <strong>8:15 AM</strong></div>
                </div>

                <div class="p-3.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/40">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-900 dark:text-emerald-300">Islamic Section</span>
                        <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Afternoon Shift</span>
                    </div>
                    <div class="text-xs text-emerald-800 dark:text-emerald-200 mt-1 font-mono">12:30 PM – 5:00 PM</div>
                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">Late cutoff after: <strong>12:45 PM</strong></div>
                </div>

                <div class="pt-2">
                    <a href="{{ route('attendance.staff-timesheet') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-all">
                        Open Monthly Staff Timesheet &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Add/Edit Terminal Modal --}}
    @if($showTerminalModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="$set('showTerminalModal', false)" class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>
                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Configure K40 Terminal</h3>
                        <button wire:click="$set('showTerminalModal', false)" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-sm">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Terminal Label / Name</label>
                            <input type="text" wire:model="editingTerminalName" placeholder="e.g. Gate 3 Western Entrance" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            @error('editingTerminalName') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Terminal IP Address</label>
                                <input type="text" wire:model="editingTerminalIp" placeholder="192.168.0.201" class="w-full text-sm font-mono border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                @error('editingTerminalIp') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Port</label>
                                <input type="number" wire:model="editingTerminalPort" class="w-full text-sm font-mono border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                @error('editingTerminalPort') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Physical Location</label>
                            <input type="text" wire:model="editingTerminalLocation" placeholder="e.g. Main Entrance Gate 1" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            @error('editingTerminalLocation') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-slate-50 dark:bg-slate-700/30 text-right border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                        <button wire:click="$set('showTerminalModal', false)" type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button wire:click="addTerminal" type="button" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Save Terminal</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- WhatsApp Settings Modal --}}
    @if($showWhatsAppModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="$set('showWhatsAppModal', false)" class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>
                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.586 1.861.979 3.013.979 3.182 0 5.768-2.587 5.768-5.766.001-3.181-2.585-5.766-5.768-5.766zm9.969 5.766c0 5.523-4.477 10-10 10-1.753 0-3.4-.452-4.836-1.246l-5.164 1.352 1.378-5.034c-.886-1.492-1.378-3.23-1.378-5.072 0-5.523 4.477-10 10-10s10 4.477 10 10z"/></svg>
                            </span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Parent WhatsApp Attendance Alerts</h3>
                        </div>
                        <button wire:click="$set('showWhatsAppModal', false)" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-sm">
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="whatsappEnabled" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="font-medium text-slate-800 dark:text-white">Enable Automated WhatsApp Notifications</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer pl-7">
                                <input type="checkbox" wire:model="notifyCheckin" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="text-slate-600 dark:text-slate-300">Notify Guardian on Morning Arrival / Check-in</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer pl-7">
                                <input type="checkbox" wire:model="notifyCheckout" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="text-slate-600 dark:text-slate-300">Notify Guardian on Afternoon Departure / Check-out</span>
                            </label>
                        </div>

                        {{-- Test Alert Tool --}}
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Send Test Notification</h4>
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="testPhoneNumber" placeholder="e.g. 08034519822" class="flex-1 text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                <button wire:click="sendTestAlert" type="button" class="px-3 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg whitespace-nowrap">
                                    Send Test
                                </button>
                            </div>
                            @if($testAlertStatus)
                                <div class="text-xs mt-2 text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/50 p-2 rounded">
                                    {{ $testAlertStatus }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-slate-50 dark:bg-slate-700/30 text-right border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                        <button wire:click="$set('showWhatsAppModal', false)" type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Close</button>
                        <button wire:click="saveWhatsAppSettings" type="button" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
