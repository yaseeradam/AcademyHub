@php
    $isAdmin = $me?->role === 'admin';
@endphp

<div class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 p-6 shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">CBT Exams</h1>
                <p class="mt-1 text-sm text-amber-50">Create an exam, add questions, and it's live instantly.</p>
            </div>
            <button wire:click="{{ $creating ? 'cancelCreate' : 'startCreate' }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-amber-600 hover:bg-amber-50">
                {{ $creating ? 'Close' : '+ New Exam' }}
            </button>
        </div>
    </div>

    @if ($creating)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-lg">
            <div class="text-sm font-semibold text-gray-900 mb-4">New Exam</div>
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold uppercase text-gray-500">Title</label>
                    <input wire:model="title" class="mt-1 input w-full" placeholder="e.g. Mathematics Quiz 1" autofocus />
                    @error('title') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-gray-500">Class</label>
                    <select wire:model.live="classId" class="mt-1 select w-full">
                        <option value="">Select</option>
                        @foreach ($this->classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('classId') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-gray-500">Subject</label>
                    <select wire:model.live="subjectId" @disabled(! $classId) class="mt-1 select w-full">
                        <option value="">Select</option>
                        @foreach ($this->subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subjectId') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-gray-500">Duration (min)</label>
                    <input wire:model="durationMinutes" type="number" min="1" class="mt-1 input w-full" />
                    @error('durationMinutes') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-gray-500">Term</label>
                    <select wire:model.live="term" class="mt-1 select w-full">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold uppercase text-gray-500">Session</label>
                    <input wire:model="session" class="mt-1 input w-full" placeholder="2025/2026" />
                    @error('session') <div class="mt-1 text-xs text-orange-700">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button wire:click="createExam" class="btn-primary">Create &amp; Add Questions →</button>
                <button wire:click="cancelCreate" class="btn-outline">Cancel</button>
            </div>
            @if ($isAdmin)
                <p class="mt-3 text-xs text-amber-700">Admin exams go live immediately with an access code.</p>
            @endif
        </div>
    @endif

    <div class="rounded-2xl bg-white p-4 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-900">{{ $exams->count() }} Exams</div>
            <select wire:model.live="statusFilter" class="select w-40">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="live">Live</option>
                <option value="ended">Ended</option>
            </select>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($exams as $exam)
            @php
                $variant = match ($exam->status) {
                    'live' => 'success',
                    'ended' => 'info',
                    default => 'neutral',
                };
            @endphp
            <div class="rounded-2xl border bg-white p-4 shadow-lg">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-gray-900">{{ $exam->title }}</div>
                        <div class="mt-1 text-xs text-gray-500">{{ $exam->schoolClass?->name }} • {{ $exam->subject?->name }}</div>
                        @if ($exam->status === 'live' && $exam->note)
                            <div class="mt-2 rounded bg-amber-50 px-2 py-1 text-xs text-amber-700">{{ $exam->note }}</div>
                        @endif
                    </div>
                    <x-status-badge variant="{{ $variant }}">{{ ucfirst($exam->status) }}</x-status-badge>
                </div>

                <div class="mt-3 flex items-center gap-4 text-xs text-gray-600">
                    <div><span class="font-semibold">{{ (int) $exam->questions_count }}</span> Questions</div>
                    <div><span class="font-semibold">{{ (int) $exam->attempts_count }}</span> Attempts</div>
                    @if ($exam->status === 'live' && $exam->access_code)
                        <div class="font-mono font-semibold text-amber-600">{{ $exam->access_code }}</div>
                    @endif
                </div>

                <div class="mt-3 flex items-center justify-between border-t pt-3">
                    <div class="text-xs text-gray-500">{{ $exam->creator?->name ?? 'User' }}</div>
                    <a href="{{ route('cbt.exams.edit', $exam) }}" class="rounded-lg bg-gray-100 px-3 py-1 text-xs font-semibold hover:bg-gray-200">
                        {{ $exam->status === 'draft' ? 'Edit' : 'Open' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="lg:col-span-2 rounded-2xl border-2 border-dashed bg-gray-50 p-10 text-center text-sm text-gray-600">No exams yet</div>
        @endforelse
    </div>
</div>
