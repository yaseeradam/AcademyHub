<div class="space-y-6">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Broadsheet</h1>
                    <p class="mt-2 text-base text-emerald-50">All students (rows) × all subjects (columns)</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('results.entry') }}" class="rounded-xl bg-white/20 px-5 py-2.5 text-sm font-semibold text-white shadow-lg backdrop-blur-sm transition-all hover:bg-white/30 hover:shadow-xl">Score Entry</a>
                    @if ($classId && auth()->user()?->role === 'admin')
                        @if ($this->isPublished)
                            <button wire:click="unpublish" wire:loading.attr="disabled" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all hover:bg-red-600 hover:shadow-xl disabled:opacity-50">
                                <span wire:loading.remove wire:target="unpublish">Unpublish</span>
                                <span wire:loading wire:target="unpublish">Unpublishing...</span>
                            </button>
                        @else
                            <button wire:click="publish" wire:loading.attr="disabled" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-emerald-600 shadow-lg transition-all hover:bg-emerald-50 hover:shadow-xl disabled:opacity-50">
                                <span wire:loading.remove wire:target="publish">Publish Results</span>
                                <span wire:loading wire:target="publish">Publishing...</span>
                            </button>
                        @endif
                    @endif
                    @if ($classId && $this->isPublished)
                        <button wire:click="generateBulk" wire:loading.attr="disabled" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-emerald-600 shadow-lg transition-all hover:bg-emerald-50 hover:shadow-xl disabled:opacity-50">
                            <span wire:loading.remove wire:target="generateBulk">Bulk Report Cards</span>
                            <span wire:loading wire:target="generateBulk">Generating...</span>
                        </button>
                    @endif
                </div>
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
                        @foreach ($this->classes as $class)
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
    @elseif ($this->subjects->isEmpty())
        <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
            <div class="text-lg font-semibold text-gray-900">No subjects</div>
            <div class="mt-2 text-sm text-gray-600">Allocate subjects to this class to populate the broadsheet.</div>
        </div>
    @else
        <div class="overflow-x-auto overflow-hidden rounded-2xl bg-white shadow-lg" 
             wire:key="broadsheet-table-{{ $classId }}-{{ $term }}-{{ $session }}">
            <x-table class="text-xs">
                <thead class="bg-gradient-to-r from-emerald-500 to-cyan-600 text-[11px] font-semibold uppercase tracking-wider text-white">
                <tr>
                    <th class="px-5 py-3">Student</th>
                    @foreach ($this->subjects as $subject)
                        <th class="px-4 py-3 text-right whitespace-nowrap">{{ $subject->code }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Avg</th>
                    <th class="px-4 py-3 text-right">Pos</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($this->rows as $row)
                    <tr class="bg-white hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $row['student']->full_name }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $row['student']->admission_number }}</div>
                        </td>

                        @foreach ($this->subjects as $subject)
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
                        <td colspan="{{ 1 + $this->subjects->count() + 4 }}" class="px-5 py-10 text-center text-sm text-gray-500">
                            No students found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </x-table>
        </div>
    @endif
</div>
