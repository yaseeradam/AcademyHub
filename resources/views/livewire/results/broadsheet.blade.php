<div class="space-y-6">
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative flex flex-col gap-4 px-8 py-8 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color:#93c5fd;">Results</span>
                </div>
                <h1 class="text-4xl font-bold text-white tracking-tight">Broadsheet</h1>
                <p class="mt-1.5 text-base font-medium" style="color:#93c5fd;">All students × all subjects</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('results.entry') }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition-all" style="background:rgba(255,255,255,0.12);">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Score Entry
                </a>
                @if ($classId && auth()->user()?->role === 'admin')
                    @if ($isPublished)
                        <button wire:click="unpublish" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-red-600 disabled:opacity-50">
                            <span wire:loading.remove wire:target="unpublish">Unpublish</span>
                            <span wire:loading wire:target="unpublish">Unpublishing...</span>
                        </button>
                    @else
                        <button wire:click="publish" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-amber-600 disabled:opacity-50">
                            <span wire:loading.remove wire:target="publish">Publish Results</span>
                            <span wire:loading wire:target="publish">Publishing...</span>
                        </button>
                    @endif
                @endif
                @if ($classId && $isPublished)
                    <button wire:click="generateBulk" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition-all disabled:opacity-50" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 10v6m0 0-3-3m3 3 3-3M3 17a4 4 0 0 1 4-4h.01M17 17a4 4 0 0 0-4-4"/>
                        </svg>
                        <span wire:loading.remove wire:target="generateBulk">Bulk Report Cards</span>
                        <span wire:loading wire:target="generateBulk">Generating...</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-semibold text-slate-900">Filters</div>
                <div class="mt-1 text-sm text-slate-600">Pick class, session, and term.</div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class</label>
                    <select
                        wire:key="class-select-{{ $classId ?: 'empty' }}"
                        wire:model.live="classId"
                        class="mt-2 select min-w-52"
                    >
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                    <input
                        wire:key="session-input-{{ $classId ?: 'empty' }}"
                        wire:model.live.debounce.300ms="session"
                        type="text"
                        placeholder="2025/2026"
                        class="mt-2 input-compact min-w-40"
                    />
                </div>

                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                    <select
                        wire:key="term-select-{{ $classId ?: 'empty' }}"
                        wire:model.live="term"
                        class="mt-2 select min-w-24"
                    >
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if (! $classId)
        <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
            <div class="text-lg font-semibold text-gray-900">Select a class</div>
            <div class="mt-2 text-sm text-gray-600">Choose a class to generate the broadsheet.</div>
        </div>
    @elseif ($subjects->isEmpty())
        <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
            <div class="text-lg font-semibold text-gray-900">No subjects</div>
            <div class="mt-2 text-sm text-gray-600">Allocate subjects to this class to populate the broadsheet.</div>
        </div>
    @else
        <div class="overflow-x-auto overflow-hidden rounded-2xl bg-white shadow-lg" 
             wire:key="broadsheet-table-{{ $classId }}-{{ $term }}-{{ $session }}">
            <x-table class="text-xs">
                <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3">Student</th>
                    @foreach ($subjects as $subject)
                        <th class="px-4 py-3 text-right whitespace-nowrap">{{ $subject->code }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Avg</th>
                    <th class="px-4 py-3 text-right">Pos</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $row)
                    <tr class="bg-white hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $row['student']->full_name }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $row['student']->admission_number }}</div>
                        </td>

                        @foreach ($subjects as $subject)
                            @php($val = $row['subjectTotals'][$subject->id] ?? null)
                            <td class="px-4 py-4 text-right text-sm font-semibold {{ $val === null ? 'text-gray-300' : 'text-gray-900' }}">
                                {{ $val ?? '—' }}
                            </td>
                        @endforeach

                        <td class="px-4 py-4 text-right text-sm font-bold text-gray-900">{{ $row['grandTotal'] }}</td>
                        <td class="px-4 py-4 text-right text-sm font-semibold text-gray-700">{{ number_format($row['average'], 2) }}</td>
                        <td class="px-4 py-4 text-right">
                            <x-status-badge variant="{{ $row['position'] <= 3 ? 'success' : 'neutral' }}">
                                {{ $row['position'] }}
                            </x-status-badge>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a
                                href="{{ route('results.report-card', ['student' => $row['student'], 'term' => $term, 'session' => $session, '_t' => time()]) }}"
                                class="text-sm font-semibold text-brand-600 hover:text-brand-700"
                            >
                                Report Card
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + $subjects->count() + 4 }}" class="px-5 py-10 text-center text-sm text-gray-500">
                            No students found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </x-table>
        </div>
    @endif
</div>
