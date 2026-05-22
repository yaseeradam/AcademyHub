<div class="space-y-6">

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="relative flex flex-col gap-4 px-8 py-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color:#93c5fd;">Assignments</span>
                </div>
                <h1 class="text-4xl font-bold text-white tracking-tight">Homework</h1>
                <p class="mt-1.5 text-base font-medium" style="color:#93c5fd;">Manage homework assignments for your classes</p>
            </div>
            <button wire:click="create"
                    class="inline-flex items-center gap-2 self-start sm:self-auto rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                New Homework
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session()->has('message'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Overlay Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" wire:click.self="closeModal">
            <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden animate-fade-in">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-amber-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $editMode ? 'Edit Homework' : 'New Homework' }}</div>
                            <div class="text-xs text-slate-500">Fill in the details below</div>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-8 w-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save" class="p-6 space-y-4">
                    {{-- Row 1: Class + Subject --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600 uppercase tracking-wide">Class <span class="text-red-500">*</span></label>
                            <select wire:model.live="class_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100 transition">
                                <option value="">Select class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('class_id') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600 uppercase tracking-wide">Subject <span class="text-red-500">*</span></label>
                            <select wire:model="subject_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100 transition">
                                <option value="">Select subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 2: Section + Due Date --}}
                    <div class="grid grid-cols-2 gap-4">
                        @if($this->sections->isNotEmpty())
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600 uppercase tracking-wide">Section</label>
                            <select wire:model="section_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100 transition">
                                <option value="">All Sections</option>
                                @foreach($this->sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="{{ $this->sections->isNotEmpty() ? '' : 'col-span-2' }}">
                            <label class="mb-1.5 block text-xs font-semibold text-slate-600 uppercase tracking-wide">Due Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="due_date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100 transition">
                            @error('due_date') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600 uppercase tracking-wide">Title <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title" placeholder="e.g., Photosynthesis, World War 2, Fractions"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100 transition">
                        @error('title') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Content + AI --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Content <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="formatWithAI" wire:loading.attr="disabled" wire:target="formatWithAI,generateWithAI"
                                        class="inline-flex items-center gap-1 rounded-lg border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 hover:bg-violet-100 transition disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="formatWithAI,generateWithAI" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <svg wire:loading wire:target="formatWithAI,generateWithAI" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    <span wire:loading.remove wire:target="formatWithAI,generateWithAI">Format</span>
                                    <span wire:loading wire:target="formatWithAI">...</span>
                                </button>
                                <button type="button" wire:click="generateWithAI" wire:loading.attr="disabled" wire:target="formatWithAI,generateWithAI"
                                        class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="formatWithAI,generateWithAI" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                    <span wire:loading.remove wire:target="formatWithAI,generateWithAI">AI Generate</span>
                                    <span wire:loading wire:target="generateWithAI">Generating...</span>
                                </button>
                            </div>
                        </div>
                        <textarea wire:model="content" rows="5" placeholder="Write content here, or use AI Generate above..."
                                  class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100 transition"></textarea>
                        @error('content') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 text-sm font-bold text-white hover:bg-amber-600 transition">
                            {{ $editMode ? 'Update' : 'Create Homework' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search homework..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm text-slate-800 placeholder-slate-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-400/10 transition-all">
            </div>
            <span class="text-xs font-semibold text-slate-400">{{ $homework->total() }} assignments</span>
        </div>

        <div class="overflow-x-auto">
            <x-table>
                <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Due Date</th>
                        <th class="px-4 py-3 text-left">Submissions</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($homework as $hw)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="text-sm font-semibold text-slate-900">{{ $hw->title }}</div>
                                <div class="mt-0.5 text-xs text-slate-400">{{ Str::limit($hw->content, 50) }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-slate-600">
                                {{ $hw->class?->name }}
                                @if($hw->section)<span class="text-slate-400">/ {{ $hw->section->name }}</span>@endif
                            </td>
                            <td class="px-4 py-3.5 text-sm text-slate-600">{{ $hw->subject?->name }}</td>
                            <td class="px-4 py-3.5">
                                <div class="text-sm text-slate-700">{{ $hw->due_date->format('M j, Y') }}</div>
                                <div class="mt-0.5">
                                    @if($hw->due_date->isPast())
                                        <x-status-badge variant="warning">Overdue</x-status-badge>
                                    @else
                                        <x-status-badge variant="info">{{ $hw->due_date->diffForHumans() }}</x-status-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                    {{ $hw->submissions->count() }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                    <a href="{{ route('homework.submissions', $hw->id) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                                        Submissions
                                    </a>
                                    @if($hw->due_date < now()->startOfDay())
                                        <span class="text-xs font-semibold text-slate-300" title="Cannot edit past due date">Locked</span>
                                    @else
                                        <button wire:click="edit({{ $hw->id }})" class="text-xs font-semibold text-amber-600 hover:text-amber-700">Edit</button>
                                    @endif
                                    <button wire:click="delete({{ $hw->id }})" wire:confirm="Delete this homework?" class="text-xs font-semibold text-red-500 hover:text-red-600">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="mx-auto mb-3 h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-bold text-slate-700">No homework assignments yet</div>
                                <div class="mt-1 text-xs text-slate-400">Click "New Homework" to get started</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        @if($homework->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $homework->links() }}</div>
        @endif
    </div>

    <style>
        @keyframes fade-in { from { opacity:0; transform:scale(.97) translateY(-8px); } to { opacity:1; transform:scale(1) translateY(0); } }
        .animate-fade-in { animation: fade-in .2s ease-out; }
    </style>

    @script
        $wire.on('content-formatted', (event) => {
            window.showNotification('Content formatted!', 'success');
            const ta = document.querySelector('textarea[wire\\:model="content"]');
            if (ta && event.content) { ta.value = event.content; ta.dispatchEvent(new Event('input', { bubbles: true })); }
        });
        $wire.on('content-generated', (event) => {
            window.showNotification('Assignment generated!', 'success');
            const ta = document.querySelector('textarea[wire\\:model="content"]');
            if (ta && event.content) { ta.value = event.content; ta.dispatchEvent(new Event('input', { bubbles: true })); }
        });
    @endscript
</div>
