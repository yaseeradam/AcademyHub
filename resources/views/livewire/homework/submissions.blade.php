<div>
    <x-page-header 
        title="Submissions: {{ $homework->title }}" 
        subtitle="{{ $homework->class->name }} - {{ $homework->subject->name }} (Due: {{ $homework->due_date->format('M j, Y') }})" 
        accent="teachers"
    >
        <x-slot:actions>
            <a href="{{ route('homework.index') }}" class="btn-outline">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Homework
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (session()->has('message'))
        <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="card-padded">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
                Submissions ({{ $submissions->count() }})
            </h3>
        </div>

        <x-table>
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3">Student</th>
                    <th class="px-5 py-3">Submitted At</th>
                    <th class="px-5 py-3">Grade</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($submissions as $submission)
                    <tr class="bg-white hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $submission->student->full_name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $submission->student->admission_number }}
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700">
                            {{ $submission->submitted_at->format('M j, Y g:i A') }}
                        </td>
                        <td class="px-5 py-4">
                            @if($submission->grade !== null)
                                <span class="text-sm font-semibold text-gray-900">{{ $submission->grade }}%</span>
                            @else
                                <span class="text-sm text-gray-400">Not graded</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($submission->graded_at)
                                <x-status-badge variant="success">Graded</x-status-badge>
                            @else
                                <x-status-badge variant="warning">Pending</x-status-badge>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button wire:click="gradeSubmission({{ $submission->id }})" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                                {{ $submission->graded_at ? 'Edit Grade' : 'Grade' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">
                            No submissions yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    </div>

    @if($showGradeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeGradeModal"></div>

                <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
                    @php
                        $submission = $submissions->firstWhere('id', $submissionId);
                    @endphp

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Grade Submission</h3>
                        <button wire:click="closeGradeModal" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    @if($submission)
                        <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                            <div class="text-sm font-semibold text-gray-900 mb-2">
                                {{ $submission->student->full_name }}
                            </div>
                            <div class="text-sm text-gray-700 whitespace-pre-wrap">
                                {{ $submission->submission }}
                            </div>
                            @if($submission->attachment)
                                <a href="{{ Storage::url($submission->attachment) }}" target="_blank" class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-700">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    View Attachment
                                </a>
                            @endif
                        </div>
                    @endif

                    <form wire:submit="saveGrade">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Grade (0-100)</label>
                                <input type="number" wire:model="grade" min="0" max="100" step="0.01" placeholder="Enter grade" class="block w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                                @error('grade') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Feedback (Optional)</label>
                                <textarea wire:model="feedback" rows="4" placeholder="Enter feedback for the student..." class="block w-full px-4 py-3 text-sm rounded-xl border border-gray-300 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition resize-none"></textarea>
                                @error('feedback') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" wire:click="closeGradeModal" class="btn-outline">
                                Cancel
                            </button>
                            <button type="submit" class="btn-primary">
                                Save Grade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
