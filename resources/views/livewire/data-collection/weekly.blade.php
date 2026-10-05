<div class="space-y-6">
    <x-page-header title="Data Collection" subtitle="Quick weekly summary (boys/girls present & absent) to send to admin." accent="attendance" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-1">
            <div class="card-padded">
                <div class="text-sm font-black text-gray-900">Week</div>
                <div class="mt-1 text-xs font-semibold text-gray-500">Pick class, section and week start.</div>

                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class</label>
                        <select wire:model.live="classId" class="mt-2 select">
                            <option value="">Select class</option>
                            @foreach ($this->classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Section</label>
                        <select wire:model.live="sectionId" class="mt-2 select" @disabled(! $classId)>
                            <option value="">Select section</option>
                            @foreach ($this->sections as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                            <select wire:model.live="term" class="mt-2 select">
                                <option value="1">Term 1</option>
                                <option value="2">Term 2</option>
                                <option value="3">Term 3</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                            <input wire:model.live.debounce.300ms="session" type="text" placeholder="2025/2026" class="mt-2 input-compact" />
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Week start (Mon)</label>
                        <input wire:model.live="weekStart" type="date" class="mt-2 input-compact" />
                        @if ($this->weekEnd)
                            <div class="mt-2 text-xs font-semibold text-gray-500">Ends: {{ \Illuminate\Support\Carbon::parse($this->weekEnd)->format('D, M j') }}</div>
                        @endif
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">School days opened (optional)</label>
                        <select wire:model.live="schoolDays" class="mt-2 select">
                            <option value="">—</option>
                            @for ($i = 1; $i <= 7; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Note (optional)</label>
                        <textarea wire:model.live.debounce.400ms="note" rows="3" class="mt-2 input-compact"></textarea>
                    </div>
                </div>

                @if ($this->existing)
                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-black text-slate-900">Current week status</div>
                            <div class="text-xs font-bold uppercase tracking-wider">
                                @php($st = $this->existing->status)
                                <span class="{{ $st === 'approved' ? 'text-emerald-700' : ($st === 'rejected' ? 'text-rose-700' : 'text-amber-700') }}">
                                    {{ strtoupper($st) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-1 text-xs font-semibold text-slate-600">
                            Submitted by {{ $this->existing->teacher?->name ?? '—' }}
                            @if ($this->existing->submitted_at)
                                · {{ $this->existing->submitted_at->format('M j, g:i A') }}
                            @endif
                        </div>
                        @if ($this->existing->status === 'rejected' && $this->existing->rejection_note)
                            <div class="mt-3 rounded-xl bg-rose-50 p-3 text-xs font-semibold text-rose-800">
                                Rejected: {{ $this->existing->rejection_note }}
                            </div>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" class="btn-outline" wire:click="loadFromExisting" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Load values</button>
                        </div>
                    </div>
                @endif
            </div>

            @if ($this->recent->count())
                <div class="card-padded">
                    <div class="text-sm font-black text-gray-900">Recent submissions</div>
                    <div class="mt-3 space-y-2">
                        @foreach ($this->recent as $r)
                            <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-bold text-slate-900">
                                        {{ $r->schoolClass?->name ?? '-' }} {{ $r->section?->name ?? '' }}
                                    </div>
                                    <div class="mt-1 text-xs font-semibold text-slate-600">
                                        {{ $r->session }} · T{{ $r->term }} · {{ $r->week_start?->format('M j') }}–{{ $r->week_end?->format('M j') }}
                                    </div>
                                </div>
                                <div class="text-xs font-black uppercase tracking-wider {{ $r->status === 'approved' ? 'text-emerald-700' : ($r->status === 'rejected' ? 'text-rose-700' : 'text-amber-700') }}">
                                    {{ $r->status }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-4 lg:col-span-2">
            <div class="card-padded">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-black text-gray-900">Weekly summary</div>
                        <div class="mt-1 text-xs font-semibold text-gray-500">Fast inputs (use + / − or type numbers).</div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn-primary" wire:click="submit" @disabled(! $classId || ! $sectionId) wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                            Submit to Admin
                        </button>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-5">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-black text-slate-900">Boys</div>
                            <div class="rounded-full bg-blue-600 px-3 py-1 text-xs font-black text-white">BOYS</div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Present</div>
                                <div class="mt-2 flex items-center gap-2">
                                    <button type="button" class="btn-outline px-3" wire:click="bump('boysPresent', -1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">−</button>
                                    <input wire:model.live="boysPresent" type="number" min="0" class="input-compact text-center" />
                                    <button type="button" class="btn-outline px-3" wire:click="bump('boysPresent', 1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">+</button>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Absent</div>
                                <div class="mt-2 flex items-center gap-2">
                                    <button type="button" class="btn-outline px-3" wire:click="bump('boysAbsent', -1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">−</button>
                                    <input wire:model.live="boysAbsent" type="number" min="0" class="input-compact text-center" />
                                    <button type="button" class="btn-outline px-3" wire:click="bump('boysAbsent', 1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-xs font-semibold text-slate-700">
                            Total boys: <span class="font-black">{{ $this->totals['boys_total'] }}</span>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-rose-100 bg-gradient-to-br from-rose-50 to-pink-50 p-5">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-black text-slate-900">Girls</div>
                            <div class="rounded-full bg-rose-600 px-3 py-1 text-xs font-black text-white">GIRLS</div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Present</div>
                                <div class="mt-2 flex items-center gap-2">
                                    <button type="button" class="btn-outline px-3" wire:click="bump('girlsPresent', -1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">−</button>
                                    <input wire:model.live="girlsPresent" type="number" min="0" class="input-compact text-center" />
                                    <button type="button" class="btn-outline px-3" wire:click="bump('girlsPresent', 1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">+</button>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Absent</div>
                                <div class="mt-2 flex items-center gap-2">
                                    <button type="button" class="btn-outline px-3" wire:click="bump('girlsAbsent', -1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">−</button>
                                    <input wire:model.live="girlsAbsent" type="number" min="0" class="input-compact text-center" />
                                    <button type="button" class="btn-outline px-3" wire:click="bump('girlsAbsent', 1)" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-xs font-semibold text-slate-700">
                            Total girls: <span class="font-black">{{ $this->totals['girls_total'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-emerald-50 p-4 ring-1 ring-emerald-100">
                        <div class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Present (Total)</div>
                        <div class="mt-1 text-2xl font-black text-emerald-900">{{ $this->totals['present_total'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-orange-50 p-4 ring-1 ring-orange-100">
                        <div class="text-[10px] font-black uppercase tracking-wider text-orange-700">Absent (Total)</div>
                        <div class="mt-1 text-2xl font-black text-orange-900">{{ $this->totals['absent_total'] }}</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100">
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-700">Students (Total)</div>
                        <div class="mt-1 text-2xl font-black text-slate-900">{{ $this->totals['student_total'] }}</div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-sm text-orange-900">
                        <div class="font-black">Fix these:</div>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm font-semibold">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            @if(auth()->user()?->role === 'admin')
                <div class="card-padded">
                    <div class="text-sm font-black text-gray-900">Admin</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ route('data-collection.submissions') }}" class="btn-outline">Review submissions</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

