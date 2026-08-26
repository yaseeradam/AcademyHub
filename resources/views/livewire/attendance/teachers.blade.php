<div
    class="space-y-6" wire:poll.10s
    x-data="{
        init() {},
        onKeydown(e) {
            const isTyping = (el) => {
                if (!el) return false;
                const tag = (el.tagName || '').toLowerCase();
                return tag === 'input' || tag === 'textarea' || tag === 'select' || el.isContentEditable;
            };

            const key = (e.key || '').toLowerCase();

            if ((e.ctrlKey || e.metaKey) && key === 's') {
                e.preventDefault();
                this.$wire.save();
                return;
            }

            if (isTyping(e.target)) {
                if (key === 'escape') e.target.blur();
                return;
            }

            if (key === '/') {
                e.preventDefault();
                const search = document.getElementById('teacherAttendanceSearch');
                if (search) search.focus();
                return;
            }

            if (key === 'escape') {
                this.$wire.set('search', '');
                this.$wire.set('onlyExceptions', false);
                return;
            }

            if (key === 'a') this.$wire.setTool('Absent');
            if (key === 'l') this.$wire.setTool('Late');
            if (key === 'e') this.$wire.setTool('Excused');
            if (key === 'p') this.$wire.setTool('Present');
        },
    }"
    x-init="init()"
    @keydown.window="onKeydown($event)"
>
    <x-page-header title="Teacher Attendance" subtitle="Fast, simple tap-to-mark staff attendance" accent="attendance">
        <x-slot:actions>
            <a href="{{ route('attendance') }}" class="btn-outline">Student Attendance</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="space-y-4 lg:col-span-4">
            <div class="card-padded lg:sticky lg:top-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Setup</div>
                        <div class="mt-1 text-xs text-slate-500">Pick date. Then tap teachers to mark.</div>
                    </div>
                    <div class="text-xs font-semibold text-slate-600">
                        @if ($sheetId)
                            Loaded
                        @else
                            New
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Date</label>
                        <input wire:model.live="date" type="date" class="mt-2 input-compact" />
                    </div>

                    <details class="rounded-xl bg-slate-50 p-3 ring-1 ring-inset ring-slate-200">
                        <summary class="cursor-pointer select-none text-xs font-semibold text-slate-700">Advanced</summary>
                        <div class="mt-3 grid grid-cols-1 gap-3">
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
                                <input wire:model.live="session" type="text" placeholder="2025/2026" class="mt-2 input-compact" />
                            </div>
                        </div>
                    </details>
                </div>

                <div class="mt-4 rounded-xl bg-white ring-1 ring-inset ring-slate-200">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-600">Marking tool</div>
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                            <input type="checkbox" wire:model.live="onlyExceptions" class="checkbox-custom" />
                            Exceptions only
                        </label>
                    </div>

                    @php
                        $tool = $tool ?? 'Absent';
                        $toolBtn = fn (string $name, string $active, string $inactive) => $tool === $name ? $active : $inactive;
                    @endphp

                    <div class="grid grid-cols-2 gap-2 px-4 py-3">
                        <button type="button" wire:click="setTool('Absent')" class="{{ $toolBtn('Absent', 'rounded-xl bg-red-600 px-3 py-2 text-xs font-bold text-white shadow-sm', 'rounded-xl bg-white px-3 py-2 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50') }}">
                            Absent <span class="font-semibold opacity-80">(A)</span>
                        </button>
                        <button type="button" wire:click="setTool('Late')" class="{{ $toolBtn('Late', 'rounded-xl bg-orange-600 px-3 py-2 text-xs font-bold text-white shadow-sm', 'rounded-xl bg-white px-3 py-2 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50') }}">
                            Late <span class="font-semibold opacity-80">(L)</span>
                        </button>
                        <button type="button" wire:click="setTool('Excused')" class="{{ $toolBtn('Excused', 'rounded-xl bg-slate-700 px-3 py-2 text-xs font-bold text-white shadow-sm', 'rounded-xl bg-white px-3 py-2 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50') }}">
                            Excused <span class="font-semibold opacity-80">(E)</span>
                        </button>
                        <button type="button" wire:click="setTool('Present')" class="{{ $toolBtn('Present', 'rounded-xl bg-green-600 px-3 py-2 text-xs font-bold text-white shadow-sm', 'rounded-xl bg-white px-3 py-2 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50') }}">
                            Present <span class="font-semibold opacity-80">(P)</span>
                        </button>
                    </div>

                    <div class="px-4 pb-4">
                        <div class="relative">
                            <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 21l-4.35-4.35" />
                                    <circle cx="11" cy="11" r="7" />
                                </svg>
                            </div>
                            <input id="teacherAttendanceSearch" wire:model.live.debounce.250ms="search" type="text" placeholder="Search name or email (/)" class="input-search" />
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" wire:click="markAll('Present')" class="btn-outline text-xs">
                        All Present
                    </button>
                    <button type="button" wire:click="markAll('Absent')" class="btn-outline text-xs">
                        All Absent
                    </button>
                    <button type="button" wire:click="save" class="btn-success flex-1">
                        <span wire:loading.remove wire:target="save">Save</span>
                        <span wire:loading wire:target="save">Saving…</span>
                        <span class="text-xs font-semibold opacity-80">(Ctrl+S)</span>
                    </button>
                </div>

                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <x-status-badge variant="success">Present: {{ $this->markCounts['Present'] ?? 0 }}</x-status-badge>
                    <x-status-badge variant="warning">Absent: {{ $this->markCounts['Absent'] ?? 0 }}</x-status-badge>
                    <x-status-badge variant="info">Late: {{ $this->markCounts['Late'] ?? 0 }}</x-status-badge>
                    <x-status-badge variant="default">Excused: {{ $this->markCounts['Excused'] ?? 0 }}</x-status-badge>
                </div>

                <div class="mt-3 text-xs text-slate-500">
                    Shortcuts: <span class="font-semibold">A</span> Absent, <span class="font-semibold">L</span> Late, <span class="font-semibold">E</span> Excused, <span class="font-semibold">P</span> Present, <span class="font-semibold">/</span> Search, <span class="font-semibold">Ctrl+S</span> Save.
                </div>
            </div>
        </div>

        <div class="space-y-3 lg:col-span-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm font-semibold text-slate-900">
                    Teachers <span class="text-slate-500">({{ $this->visibleTeachers->count() }})</span>
                </div>
                <div class="text-xs font-semibold text-slate-600">
                    Tool: <span class="text-slate-900">{{ $tool }}</span> — tap a teacher to toggle
                </div>
            </div>

            @forelse ($this->visibleTeachers as $teacher)
                @php
                    $status = $marks[$teacher->id]['status'] ?? 'Present';
                    $note = $marks[$teacher->id]['note'] ?? null;

                    $pill = [
                        'Present' => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
                        'Absent' => 'bg-red-600 text-white',
                        'Late' => 'bg-orange-600 text-white',
                        'Excused' => 'bg-slate-700 text-white',
                    ][$status] ?? 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200';

                    $row = [
                        'Present' => 'border-slate-200 bg-white',
                        'Absent' => 'border-red-200 bg-red-50/40',
                        'Late' => 'border-orange-200 bg-orange-50/40',
                        'Excused' => 'border-slate-200 bg-slate-50/60',
                    ][$status] ?? 'border-slate-200 bg-white';
                @endphp

                <div
                    wire:key="teacher-{{ $teacher->id }}"
                    wire:click="applyTool({{ $teacher->id }})"
                    class="cursor-pointer rounded-2xl border p-4 shadow-soft transition-colors hover:border-amber-200 hover:bg-amber-50/30 {{ $row }}"
                    title="Tap to toggle {{ $tool }}"
                >
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-indigo-50 text-sm font-extrabold text-indigo-600 ring-1 ring-inset ring-indigo-100 flex-shrink-0">
                            {{ mb_substr($teacher->name, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-900">{{ $teacher->name }}</div>
                            <div class="mt-0.5 text-xs font-semibold text-slate-500">{{ $teacher->email }}</div>

                            @if (($status !== 'Present') || ($note))
                                <div class="mt-2" @click.stop>
                                    <input
                                        type="text"
                                        class="input-compact"
                                        placeholder="Optional note (e.g. on leave)"
                                        wire:model.live.debounce.400ms="marks.{{ $teacher->id }}.note"
                                    />
                                </div>
                            @endif
                        </div>

                        <button
                            type="button"
                            wire:click.stop="cycleStatus({{ $teacher->id }})"
                            class="flex-shrink-0 rounded-xl px-4 py-2 text-xs font-bold transition-colors {{ $pill }}"
                            aria-label="Change status for {{ $teacher->name }}"
                            title="Click to cycle status"
                        >
                            {{ $status }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="card-padded text-center text-sm text-gray-500">
                    No teachers found.
                </div>
            @endforelse
        </div>
    </div>
</div>

