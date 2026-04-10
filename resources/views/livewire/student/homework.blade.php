<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">My Homework</h2>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-2 border-b border-gray-200">
        <button wire:click="setFilter('pending')" 
                class="px-4 py-2 text-sm font-medium {{ $filter === 'pending' ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-600 hover:text-gray-900' }}">
            Pending
        </button>
        <button wire:click="setFilter('overdue')" 
                class="px-4 py-2 text-sm font-medium {{ $filter === 'overdue' ? 'text-red-600 border-b-2 border-red-600' : 'text-gray-600 hover:text-gray-900' }}">
            Overdue
        </button>
        <button wire:click="setFilter('submitted')" 
                class="px-4 py-2 text-sm font-medium {{ $filter === 'submitted' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
            Submitted
        </button>
        <button wire:click="setFilter('all')" 
                class="px-4 py-2 text-sm font-medium {{ $filter === 'all' ? 'text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
            All
        </button>
    </div>

    <!-- Homework List -->
    @if($homework->isEmpty())
        <div class="rounded-2xl bg-white p-12 text-center shadow-lg">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">No homework found</h3>
            <p class="mt-2 text-sm text-gray-600">
                @if($filter === 'pending')
                    You don't have any pending homework. Great job!
                @elseif($filter === 'overdue')
                    You don't have any overdue homework.
                @elseif($filter === 'submitted')
                    You haven't submitted any homework yet.
                @else
                    No homework has been assigned yet.
                @endif
            </p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach($homework as $hw)
                <div class="rounded-2xl bg-white p-6 shadow-lg hover:shadow-xl transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ $hw->title }}</h3>
                                @if($hw->submissions->isNotEmpty())
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                        Submitted
                                    </span>
                                @elseif($hw->due_date < now())
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        Overdue
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 mb-3">
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    {{ $hw->subject->name }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Due: {{ $hw->due_date->format('M d, Y') }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $hw->teacher->name }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-700 line-clamp-2">{{ $hw->content }}</p>
                        </div>

                        <div class="ml-4">
                            @if($hw->submissions->isEmpty())
                                <button wire:click="selectHomework({{ $hw->id }})" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                                    Submit
                                </button>
                            @else
                                <button wire:click="selectHomework({{ $hw->id }})" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                    View
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Submission Modal -->
    @if($selectedHomework)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay with animation -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" 
                     wire:click="$set('selectedHomework', null)"
                     x-data x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
                     x-data x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <!-- Header with gradient -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-8 py-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-white mb-2">{{ $selectedHomework->title }}</h3>
                                <div class="flex items-center gap-3 text-sm text-purple-100">
                                    <span class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        {{ $selectedHomework->subject->name }}
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Due: {{ $selectedHomework->due_date->format('M d, Y') }}
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ $selectedHomework->teacher->name }}
                                    </span>
                                </div>
                            </div>
                            <button wire:click="$set('selectedHomework', null)" 
                                    class="ml-4 rounded-full p-2 text-white hover:bg-white/20 transition">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-8 py-6 max-h-[calc(100vh-300px)] overflow-y-auto">
                        <!-- Assignment Details -->
                        <div class="mb-6">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="h-8 w-1 bg-gradient-to-b from-purple-600 to-indigo-600 rounded-full"></div>
                                <h4 class="text-lg font-bold text-gray-900">Assignment Details</h4>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                                <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $selectedHomework->content }}</p>
                            </div>
                        </div>

                        @if($selectedHomework->submissions->isNotEmpty())
                            <!-- Submitted Work -->
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-green-900">Submitted Successfully!</h4>
                                        <p class="text-sm text-green-700">{{ $selectedHomework->submissions->first()->submitted_at->format('l, F d, Y \a\t h:i A') }}</p>
                                    </div>
                                </div>
                                
                                <div class="bg-white rounded-xl p-5 border border-green-200">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">Your Answer:</p>
                                    <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $selectedHomework->submissions->first()->submission }}</p>
                                    
                                    @if($selectedHomework->submissions->first()->attachment)
                                        <div class="mt-4 pt-4 border-t border-green-100">
                                            <a href="{{ asset('storage/' . $selectedHomework->submissions->first()->attachment) }}" 
                                               target="_blank"
                                               class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition font-medium">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                </svg>
                                                View Attachment
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Submission Form -->
                            <form wire:submit.prevent="submitHomework" class="space-y-6">
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="h-8 w-1 bg-gradient-to-b from-purple-600 to-indigo-600 rounded-full"></div>
                                        <label class="text-lg font-bold text-gray-900">Your Answer</label>
                                        <span class="text-red-500">*</span>
                                    </div>
                                    <textarea wire:model="submission" 
                                              rows="8" 
                                              class="w-full rounded-xl border-2 border-gray-200 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition p-4 text-gray-800"
                                              placeholder="Write your detailed answer here...\n\nTip: Be clear and thorough in your response."
                                              required></textarea>
                                    @error('submission') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="h-8 w-1 bg-gradient-to-b from-purple-600 to-indigo-600 rounded-full"></div>
                                        <label class="text-lg font-bold text-gray-900">Attachment</label>
                                        <span class="text-sm text-gray-500 font-normal">(Optional)</span>
                                    </div>
                                    <div class="relative">
                                        <input type="file" 
                                               wire:model="attachment" 
                                               id="attachment-upload"
                                               class="hidden">
                                        <label for="attachment-upload" 
                                               class="flex items-center justify-center gap-3 w-full px-6 py-4 border-2 border-dashed border-gray-300 rounded-xl hover:border-purple-400 hover:bg-purple-50 transition cursor-pointer group">
                                            <svg class="h-8 w-8 text-gray-400 group-hover:text-purple-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <div class="text-center">
                                                <p class="text-sm font-semibold text-gray-700 group-hover:text-purple-700">Click to upload a file</p>
                                                <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG (Max 10MB)</p>
                                            </div>
                                        </label>
                                    </div>
                                    @if($attachment)
                                        <div class="mt-3 flex items-center gap-2 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-sm text-purple-900 font-medium flex-1">{{ $attachment->getClientOriginalName() }}</span>
                                            <button type="button" wire:click="$set('attachment', null)" class="text-purple-600 hover:text-purple-800">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                    @error('attachment') 
                                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                                    <button type="button" 
                                            wire:click="$set('selectedHomework', null)"
                                            class="px-6 py-3 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition">
                                        Cancel
                                    </button>
                                    <button type="submit" 
                                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-semibold rounded-xl hover:from-purple-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition transform hover:scale-105">
                                        <span class="flex items-center gap-2">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Submit Homework
                                        </span>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
