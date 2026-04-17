<div>
    <x-page-header title="Homework" subtitle="Manage homework assignments for your classes." accent="teachers">
        <x-slot:actions>
            @if(!$showModal)
                <button wire:click="create" class="btn-primary">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Homework
                </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (session()->has('message'))
        <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-start gap-3">
            <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if($showModal)
        <!-- Create/Edit Form -->
        <div class="card-padded mb-6">
            <form wire:submit="save">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editMode ? 'Edit Homework' : 'Create Homework' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Class *</label>
                        <select wire:model.live="class_id" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if($this->sections->isNotEmpty())
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Section (Optional)</label>
                            <select wire:model="section_id" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                                <option value="">All Sections</option>
                                @foreach($this->sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subject *</label>
                        <select wire:model="subject_id" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Due Date *</label>
                        <input type="date" wire:model="due_date" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        @error('due_date') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                    <input type="text" wire:model="title" placeholder="e.g., Photosynthesis, World War 2, Fractions" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                    @error('title') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    <p class="mt-2 text-xs text-gray-500">💡 Enter a topic here, then click "Generate Assignment" below to create full homework content</p>
                </div>

                <div class="mt-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Content *</label>
                    <textarea 
                        wire:model="content"
                        rows="8" 
                        placeholder="Content will be generated here, or you can write/paste your own..." 
                        class="block w-full px-4 py-3 text-sm rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition resize-none"
                    ></textarea>
                    @error('content') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <button type="button" wire:click="formatWithAI" wire:loading.attr="disabled" wire:target="formatWithAI,generateWithAI" class="text-xs font-semibold text-purple-600 hover:text-purple-700 flex items-center gap-1 disabled:opacity-50">
                            <svg wire:loading.remove wire:target="formatWithAI,generateWithAI" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <svg wire:loading wire:target="formatWithAI,generateWithAI" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="formatWithAI,generateWithAI">Format Text</span>
                            <span wire:loading wire:target="formatWithAI">Formatting...</span>
                            <span wire:loading wire:target="generateWithAI">Generating...</span>
                        </button>
                        <span class="text-gray-300">|</span>
                        <button type="button" wire:click="generateWithAI" wire:loading.attr="disabled" wire:target="formatWithAI,generateWithAI" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 disabled:opacity-50">
                            <svg wire:loading.remove wire:target="formatWithAI,generateWithAI" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <span wire:loading.remove wire:target="formatWithAI,generateWithAI">Generate Assignment</span>
                            <span wire:loading wire:target="formatWithAI">Formatting...</span>
                            <span wire:loading wire:target="generateWithAI">Generating...</span>
                        </button>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        <span class="font-semibold">Format:</span> Paste messy text here to structure it
                        <span class="mx-2">•</span>
                        <span class="font-semibold">Generate:</span> Enter topic in Title field above, then click Generate
                    </div>
                    <div class="mt-2 flex items-start gap-2 rounded-lg bg-blue-50 p-3 text-xs text-blue-800">
                        <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <span class="font-semibold">AI Feature:</span> Powered by Google Gemini (with Groq fallback). Generated content is always editable.
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeModal" class="btn-outline">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        {{ $editMode ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="card-padded">
        <div class="mb-4 flex items-center justify-between">
            <div class="relative w-full max-w-sm">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search homework..." class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                Found {{ $homework->total() }} results
            </div>
        </div>

        <x-table>
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3">Title</th>
                    <th class="px-5 py-3">Class</th>
                    <th class="px-5 py-3">Subject</th>
                    <th class="px-5 py-3">Due Date</th>
                    <th class="px-5 py-3">Submissions</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($homework as $hw)
                    <tr class="bg-white hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $hw->title }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ Str::limit($hw->content, 50) }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700">
                            {{ $hw->class?->name }}
                            @if($hw->section)
                                <span class="text-gray-500">/ {{ $hw->section->name }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700">{{ $hw->subject?->name }}</td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-gray-700 mb-1">{{ $hw->due_date->format('M j, Y') }}</div>
                            <div>
                                @if($hw->due_date->isPast())
                                    <x-status-badge variant="warning">Overdue</x-status-badge>
                                @else
                                    <x-status-badge variant="info">{{ $hw->due_date->diffForHumans() }}</x-status-badge>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $hw->submissions->count() }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                <a href="{{ route('homework.submissions', $hw->id) }}" class="text-sm font-semibold text-green-600 hover:text-green-700">
                                    View ({{ $hw->submissions->count() }})
                                </a>
                                @if($hw->due_date < now()->startOfDay())
                                    <span class="text-sm font-semibold text-gray-400 cursor-not-allowed" title="Cannot edit past due date">
                                        <svg class="h-4 w-4 inline mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> 
                                        Locked
                                    </span>
                                @else
                                    <button wire:click="edit({{ $hw->id }})" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                                        Edit
                                    </button>
                                @endif
                                <button wire:click="delete({{ $hw->id }})" wire:confirm="Are you sure you want to delete this homework?" class="text-sm font-semibold text-red-600 hover:text-red-700">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">
                            No homework assignments yet. Click "Create Homework" to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-4">
            {{ $homework->links() }}
        </div>
    </div>



    @script
    <script>
        $wire.on('content-formatted', (event) => {
            window.showNotification('Content formatted successfully!', 'success');
            // Manually update textarea value
            const textarea = document.querySelector('textarea[wire\\:model="content"]');
            if (textarea && event.content) {
                textarea.value = event.content;
                // Trigger input event to sync with Livewire
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
        
        $wire.on('content-generated', (event) => {
            window.showNotification('Assignment generated successfully!', 'success');
            // Manually update textarea value
            const textarea = document.querySelector('textarea[wire\\:model="content"]');
            if (textarea && event.content) {
                textarea.value = event.content;
                // Trigger input event to sync with Livewire
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    </script>
    @endscript
</div>
