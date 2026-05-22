<div class="space-y-6">
    <div class="relative overflow-hidden rounded-[2rem] shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative flex flex-col gap-6 p-6 sm:p-10 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2 mb-4">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs sm:text-sm font-black uppercase tracking-[0.2em]" style="color:#93c5fd;">Academic Results</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">Broadsheet</h1>
                <p class="mt-3 text-base sm:text-lg font-medium opacity-80" style="color:#93c5fd;">View all students × all subjects for a complete academic overview.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 relative z-10">
                <a href="{{ route('results.entry') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-white transition-all backdrop-blur-md border border-white/10 hover:bg-white/20" style="background:rgba(255,255,255,0.1);">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Score Entry
                </a>
                @if ($classId && auth()->user()?->role === 'admin')
                    @if ($isPublished)
                        <button wire:click="unpublish" wire:loading.attr="disabled"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500/90 px-5 py-3 text-sm font-bold text-white transition-all hover:bg-red-600 disabled:opacity-50">
                            <span wire:loading.remove wire:target="unpublish">Unpublish</span>
                            <span wire:loading wire:target="unpublish">Working...</span>
                        </button>
                    @else
                        <button wire:click="publish" wire:loading.attr="disabled"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white transition-all hover:bg-amber-600 disabled:opacity-50">
                            <span wire:loading.remove wire:target="publish">Publish Results</span>
                            <span wire:loading wire:target="publish">Working...</span>
                        </button>
                    @endif
                @endif
                @if ($classId && $isPublished)
                    <button wire:click="generateBulk" wire:loading.attr="disabled"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-white transition-all backdrop-blur-md border border-white/10 hover:bg-white/20 disabled:opacity-50" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 10v6m0 0-3-3m3 3 3-3M3 17a4 4 0 0 1 4-4h.01M17 17a4 4 0 0 0-4-4"/>
                        </svg>
                        <span wire:loading.remove wire:target="generateBulk">Report Cards</span>
                        <span wire:loading wire:target="generateBulk">Working...</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-[2rem] bg-white p-6 sm:p-8 shadow-xl ring-1 ring-slate-100">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-black text-slate-900 uppercase tracking-wider">Broadsheet Filters</div>
                <div class="mt-1 text-sm font-medium text-slate-500">Customize the broadsheet view.</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:flex lg:items-end">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Class</label>
                    <select wire:key="class-select-{{ $classId ?: 'empty' }}" wire:model.live="classId"
                        class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-900 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10">
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Session</label>
                    <input wire:key="session-input-{{ $classId ?: 'empty' }}" wire:model.live.debounce.300ms="session" type="text" placeholder="2025/2026"
                        class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-900 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10" />
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Term</label>
                    <select wire:key="term-select-{{ $classId ?: 'empty' }}" wire:model.live="term"
                        class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-900 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
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
        <div class="overflow-x-auto rounded-[2rem] bg-white shadow-2xl ring-1 ring-gray-100" 
             wire:key="broadsheet-table-{{ $classId }}-{{ $term }}-{{ $session }}">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50/80 backdrop-blur-md border-b border-gray-100">
                <tr>
                    <th class="sticky left-0 z-20 bg-gray-50 px-6 py-5 text-left text-[11px] font-black uppercase tracking-widest text-gray-500 shadow-[2px_0_10px_rgba(0,0,0,0.03)]">Student</th>
                    @foreach ($subjects as $subject)
                        <th class="px-4 py-5 text-right whitespace-nowrap text-[11px] font-black uppercase tracking-widest text-indigo-700">{{ $subject->code }}</th>
                    @endforeach
                    <th class="px-4 py-5 text-right text-[11px] font-black uppercase tracking-widest text-gray-700">Total</th>
                    <th class="px-4 py-5 text-right text-[11px] font-black uppercase tracking-widest text-gray-700">Avg</th>
                    <th class="px-4 py-5 text-right text-[11px] font-black uppercase tracking-widest text-gray-700">Pos</th>
                    <th class="px-6 py-5 text-right text-[11px] font-black uppercase tracking-widest text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $row)
                    <tr class="group hover:bg-indigo-50/30 transition-colors">
                        <td class="sticky left-0 z-10 bg-white group-hover:bg-indigo-50/30 px-6 py-4 shadow-[2px_0_10px_rgba(0,0,0,0.03)] transition-colors">
                            <div class="text-sm font-black text-gray-900">{{ $row['student']->full_name }}</div>
                            <div class="mt-0.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $row['student']->admission_number }}</div>
                        </td>

                        @foreach ($subjects as $subject)
                            @php($val = $row['subjectTotals'][$subject->id] ?? null)
                            <td class="px-4 py-4 text-right text-sm font-bold {{ $val === null ? 'text-gray-300' : 'text-gray-900' }}">
                                {{ $val ?? '—' }}
                            </td>
                        @endforeach

                        <td class="px-4 py-4 text-right text-sm font-black text-gray-900">{{ $row['grandTotal'] }}</td>
                        <td class="px-4 py-4 text-right text-sm font-bold text-indigo-600">{{ number_format($row['average'], 1) }}%</td>
                        <td class="px-4 py-4 text-right">
                            <span class="inline-flex items-center justify-center rounded-lg {{ $row['position'] <= 3 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }} px-2 py-1 text-[10px] font-black min-w-[32px]">
                                #{{ $row['position'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a
                                href="{{ route('results.report-card', ['student' => $row['student'], 'term' => $term, 'session' => $session, '_t' => time()]) }}"
                                class="rounded-xl bg-indigo-50 px-4 py-2 text-xs font-black text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm"
                            >
                                PDF
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + $subjects->count() + 4 }}" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="h-12 w-12 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <p class="mt-4 text-sm font-bold text-gray-400">No students found for this class.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    @endif
</div>