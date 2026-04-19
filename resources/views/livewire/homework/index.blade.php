<div class="space-y-6">

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative flex flex-col gap-4 px-8 py-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color:#93c5fd;">Assignments</span>
                </div>
                <h1 class="text-4xl font-bold text-white tracking-tight">Homework</h1>
                <p class="mt-1.5 text-base font-medium" style="color:#93c5fd;">Manage homework assignments for your classes</p>
            </div>
            @if(!$showModal)
                <button wire:click="create"
                        class="inline-flex items-center gap-2 self-start sm:self-auto rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Create Homework
                </button>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if (session()->has('message'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Create / Edit Form --}}
    @if($showModal)
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-amber-100 text-amber-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <rect x="8" y="2" width="8" height="4" rx="1"/>
                        </svg>
                    </div>
                    <div class="text-sm font-bold text-slate-900">{{ $editMode ? 'Edit Homework' : 'New Homework' }}</div>
                </div>
                <button type="button" wire:click="closeModal"
                        class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="save" class="p-6 space-y-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Class <span class="text-red-500">*</span></label>
                        <select wire:model.live="class_id"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    @if($this->sections->isNotEmpty())
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Section</label>
                            <select wire:model="section_id"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                                <option value="">All Sections</option>
                                @foreach($this->sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Subject <span class="text-red-500">*</span></label>
                        <select wire:model="subject_id"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Due Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="due_date"
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                        @error('due_date') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Title <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="title"
                           placeholder="e.g., Photosynthesis, World War 2, Fractions"
                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm placeholder-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                    @error('title') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    <p class="mt-1.5 text-xs text-slate-400">Enter a topic then click "Generate Assignment" to create full content</p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Content <span class="text-red-500">*</span></label>
                    <textarea wire:model="content" rows="8"
                              placeholder="Content will be generated here, or write/paste your own..."
                              class="w-full resize-none rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm placeholder-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"></textarea>
                    @error('content') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                    <div class="mt-3 flex flex-wrap items-center gap-4">
                        <button type="button" wire:click="formatWithAI"
                                wire:loading.attr="disabled" wire:target="formatWithAI,generateWithAI"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-100 transition-colors disabled:opacity-50">
                            <svg wire:loading.remove wire:target="formatWithAI,generateWithAI" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <svg wire:loading wire:target="formatWithAI,generateWithAI" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            <span wire:loading.remove wire:target="formatWithAI,generateWithAI">Format Text</span>
                            <span wire:loading wire:target="formatWithAI">Formatting...</span>
                            <span wire:loading wire:target="generateWithAI">Generating...</span>
                        </button>

                        <button type="button" wire:click="generateWithAI"
                                wire:loading.attr="disabled" wire:target="formatWithAI,generateWithAI"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition-colors disabled:opacity-50">
                            <svg wire:loading.remove wire:target="formatWithAI,generateWithAI" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <span wire:loading.remove wire:target="formatWithAI,generateWithAI">Generate Assignment</span>
                            <span wire:loading wire:target="formatWithAI">Formatting...</span>
                            <span wire:loading wire:target="generateWithAI">Generating...</span>
                        </button>
                    </div>


                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-5">
                    <button type="button" wire:click="closeModal"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-amber-500 px-5 py-2 text-sm font-bold text-white hover:bg-amber-600 transition-colors">
                        {{ $editMode ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Table --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search homework..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm text-slate-800 placeholder-slate-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100 sm:w-64">
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
                                @if($hw->section)
                                    <span class="text-slate-400">/ {{ $hw->section->name }}</span>
                                @endif
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
                                <span class="text-sm font-bold text-slate-900">{{ $hw->submissions->count() }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-4 whitespace-nowrap">
                                    <a href="{{ route('homework.submissions', $hw->id) }}"
                                       class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                                        Submissions ({{ $hw->submissions->count() }})
                                    </a>
                                    @if($hw->due_date < now()->startOfDay())
                                        <span class="flex items-center gap-1 text-xs font-semibold text-slate-300" title="Cannot edit past due date">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Locked
                                        </span>
                                    @else
                                        <button wire:click="edit({{ $hw->id }})"
                                                class="text-xs font-semibold text-amber-600 hover:text-amber-700">
                                            Edit
                                        </button>
                                    @endif
                                    <button wire:click="delete({{ $hw->id }})"
                                            wire:confirm="Are you sure you want to delete this homework?"
                                            class="text-xs font-semibold text-red-500 hover:text-red-600">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                        <rect x="8" y="2" width="8" height="4" rx="1"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-bold text-slate-700">No homework assignments yet</div>
                                <div class="mt-1 text-xs text-slate-400">Click "Create Homework" to get started</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        @if($homework->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $homework->links() }}
            </div>
        @endif
    </div>

    @script
    <script>
        $wire.on('content-formatted', (event) => {
            window.showNotification('Content formatted successfully!', 'success');
            const textarea = document.querySelector('textarea[wire\\:model="content"]');
            if (textarea && event.content) {
                textarea.value = event.content;
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        $wire.on('content-generated', (event) => {
            window.showNotification('Assignment generated successfully!', 'success');
            const textarea = document.querySelector('textarea[wire\\:model="content"]');
            if (textarea && event.content) {
                textarea.value = event.content;
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    </script>
    @endscript
</div>
