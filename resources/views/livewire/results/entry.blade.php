@php
    $user = auth()->user();
    $submissionStatus = $this->submission?->status;
    $locked = $user?->role === 'teacher' && (in_array($submissionStatus, ['submitted', 'approved'], true) || $this->isPublished);
@endphp

<div class="space-y-6">
    <x-page-header title="Score Entry" subtitle="Enter CA and Exam scores for students" accent="results">
        <x-slot:actions>
            @if($user?->role === 'admin')
                <a href="{{ route('results.submissions') }}" class="btn-primary">Score Submissions</a>
            @endif
            <a href="{{ route('results.broadsheet') }}" class="btn-outline">Broadsheet</a>
            @if($classId && $subjectId)
                <button type="button" wire:click="downloadPrintableScoresheet" class="btn-outline">🖨️ Print Scoresheet</button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="card-padded">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class</label>
                <select wire:key="class-dropdown-{{ $classId ?: 'empty' }}" wire:model.live="classId"
                    class="mt-2 select">
                    <option value="">Select class</option>
                    @foreach ($this->classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Subject</label>
                <select wire:key="subject-dropdown-{{ $classId ?: 'empty' }}" wire:model.live="subjectId"
                    @disabled(!$classId) class="mt-2 select">
                    <option value="">Select subject</option>
                    @foreach ($this->subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

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
                <input wire:model.live.debounce.300ms="session" type="text" placeholder="2025/2026"
                    class="mt-2 input-compact" />
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="button" wire:click="save" @disabled(!$classId || !$subjectId || $locked) class="btn-primary">
                Save Scores
            </button>

            @if ($user?->role === 'teacher')
                <button type="button" wire:click="submitScores" @disabled(!$classId || !$subjectId || $locked)
                    class="btn-outline">
                    Submit to Admin
                </button>
            @endif

            @if ($this->isPublished)
                <x-status-badge variant="success">Published</x-status-badge>
            @endif

            @if ($user?->role === 'teacher' && $this->submission)
                @php
                    $status = $this->submission->status ?? 'submitted';
                    $variant = match ($status) {
                        'approved' => 'success',
                        'rejected' => 'warning',
                        'submitted' => 'info',
                        default => 'neutral',
                    };
                @endphp
                <x-status-badge variant="{{ $variant }}">{{ ucfirst($status) }}</x-status-badge>
                @if ($status === 'rejected' && $this->submission->note)
                    <span class="text-xs text-orange-700">Note: {{ $this->submission->note }}</span>
                @endif
            @endif
        </div>
    </div>

    @if (!$classId)
        <div class="card-padded text-center">
            <div class="text-lg font-semibold text-gray-900">Select a class</div>
            <div class="mt-2 text-sm text-gray-600">Choose a class to load students and subjects.</div>
        </div>
    @elseif (!$subjectId)
        <div class="card-padded text-center">
            <div class="text-lg font-semibold text-gray-900">Select a subject</div>
            <div class="mt-2 text-sm text-gray-600">Only allocated subjects are shown for teachers.</div>
        </div>
    @else
            @php
                $maxMarks = $this->maxMarks();
            @endphp
            <x-table>
                <thead class="bg-white text-xs font-bold uppercase tracking-wider text-gray-900">
                    <tr>
                        <th class="px-5 py-4 border-b-2 border-gray-300">Student</th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">CA1 /{{ $maxMarks['ca1'] }}</th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">CA2 /{{ $maxMarks['ca2'] }}</th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">Exam /{{ $maxMarks['exam'] }}</th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">Total</th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($this->students as $student)
                        @php
                            $row = $scores[$student->id] ?? ['ca1' => 0, 'ca2' => 0, 'exam' => 0];
                            $total = (int) ($row['ca1'] ?? 0) + (int) ($row['ca2'] ?? 0) + (int) ($row['exam'] ?? 0);
                            $grade = \App\Models\Score::gradeForTotal($total, $maxMarks['ca1'] + $maxMarks['ca2'] + $maxMarks['exam']);
                        @endphp
                        <tr class="bg-white hover:bg-indigo-50 transition-colors">
                            <td class="px-5 py-4 bg-gray-100">
                                <div class="text-sm font-semibold text-gray-900">{{ $student->full_name }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $student->admission_number }}</div>
                            </td>
                            <td class="px-5 py-4 text-right bg-blue-100">
                                <input wire:model.lazy="scores.{{ $student->id }}.ca1" type="number" min="0"
                                    max="{{ $maxMarks['ca1'] }}" step="1"
                                    oninput="this.value !== '' && (this.value = Math.max(+this.min, Math.min(+this.max, +this.value)))"
                                    class="w-20 rounded-lg border-2 border-blue-300 bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500" />
                            </td>
                            <td class="px-5 py-4 text-right bg-green-100">
                                <input wire:model.lazy="scores.{{ $student->id }}.ca2" type="number" min="0"
                                    max="{{ $maxMarks['ca2'] }}" step="1"
                                    oninput="this.value !== '' && (this.value = Math.max(+this.min, Math.min(+this.max, +this.value)))"
                                    class="w-20 rounded-lg border-2 border-green-300 bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500" />
                            </td>
                            <td class="px-5 py-4 text-right bg-amber-100">
                                <input wire:model.lazy="scores.{{ $student->id }}.exam" type="number" min="0"
                                    max="{{ $maxMarks['exam'] }}" step="1"
                                    oninput="this.value !== '' && (this.value = Math.max(+this.min, Math.min(+this.max, +this.value)))"
                                    class="w-20 rounded-lg border-2 border-amber-300 bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500" />
                            </td>
                            <td class="px-5 py-4 text-right bg-purple-100">
                                <span
                                    class="inline-flex items-center justify-center rounded-lg bg-purple-100 px-3 py-2 text-sm font-bold text-purple-900">{{ $total }}</span>
                            </td>
                            <td class="px-5 py-4 text-right bg-gray-50">
                                <x-status-badge variant="{{ in_array($grade, ['A', 'B'], true) ? 'success' : 'neutral' }}">
                                    {{ $grade }}
                                </x-status-badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No students in this class.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>

            <div class="mt-4 rounded-lg bg-blue-50 p-4">
                <div class="text-xs font-semibold text-blue-900 mb-2">⌨️ Keyboard Shortcuts:</div>
                <div class="grid grid-cols-2 gap-2 text-xs text-blue-700">
                    <div><kbd class="px-2 py-1 bg-white rounded shadow-sm">Enter</kbd> Move down</div>
                    <div><kbd class="px-2 py-1 bg-white rounded shadow-sm">Tab</kbd> Move right</div>
                    <div><kbd class="px-2 py-1 bg-white rounded shadow-sm">↑↓←→</kbd> Arrow keys</div>
                    <div><kbd class="px-2 py-1 bg-white rounded shadow-sm">Shift+Tab</kbd> Move left</div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const table = document.querySelector('tbody');
                    if (!table) return;

                    table.addEventListener('keydown', function (e) {
                        const input = e.target;
                        if (input.tagName !== 'INPUT' || input.type !== 'number') return;

                        const cell = input.closest('td');
                        const row = cell.closest('tr');
                        const cells = Array.from(row.querySelectorAll('td input[type="number"]'));
                        const rows = Array.from(table.querySelectorAll('tr:has(input)'));
                        const currentCellIndex = cells.indexOf(input);
                        const currentRowIndex = rows.indexOf(row);

                        let nextInput = null;
                        let shouldPrevent = false;

                        if (e.key === 'Enter') {
                            shouldPrevent = true;
                            if (currentRowIndex < rows.length - 1) {
                                const nextRow = rows[currentRowIndex + 1];
                                const nextRowInputs = nextRow.querySelectorAll('td input[type="number"]');
                                nextInput = nextRowInputs[currentCellIndex];
                            }
                        } else if (e.key === 'Tab' && !e.shiftKey) {
                            shouldPrevent = true;
                            if (currentCellIndex < cells.length - 1) {
                                nextInput = cells[currentCellIndex + 1];
                            } else if (currentRowIndex < rows.length - 1) {
                                nextInput = rows[currentRowIndex + 1].querySelector('td input[type="number"]');
                            }
                        } else if (e.key === 'Tab' && e.shiftKey) {
                            shouldPrevent = true;
                            if (currentCellIndex > 0) {
                                nextInput = cells[currentCellIndex - 1];
                            } else if (currentRowIndex > 0) {
                                const prevRow = rows[currentRowIndex - 1];
                                const prevCells = prevRow.querySelectorAll('td input[type="number"]');
                                nextInput = prevCells[prevCells.length - 1];
                            }
                        } else if (e.key === 'ArrowDown') {
                            shouldPrevent = true;
                            if (currentRowIndex < rows.length - 1) {
                                const nextRow = rows[currentRowIndex + 1];
                                const nextRowInputs = nextRow.querySelectorAll('td input[type="number"]');
                                nextInput = nextRowInputs[currentCellIndex];
                            }
                        } else if (e.key === 'ArrowUp') {
                            shouldPrevent = true;
                            if (currentRowIndex > 0) {
                                const prevRow = rows[currentRowIndex - 1];
                                const prevRowInputs = prevRow.querySelectorAll('td input[type="number"]');
                                nextInput = prevRowInputs[currentCellIndex];
                            }
                        } else if (e.key === 'ArrowRight') {
                            if (input.selectionStart === input.value.length) {
                                shouldPrevent = true;
                                if (currentCellIndex < cells.length - 1) {
                                    nextInput = cells[currentCellIndex + 1];
                                }
                            }
                        } else if (e.key === 'ArrowLeft') {
                            if (input.selectionStart === 0) {
                                shouldPrevent = true;
                                if (currentCellIndex > 0) {
                                    nextInput = cells[currentCellIndex - 1];
                                }
                            }
                        }

                        if (shouldPrevent && nextInput) {
                            e.preventDefault();
                            nextInput.focus();
                            nextInput.select();
                        }
                    });

                    table.addEventListener('focusin', function (e) {
                        if (e.target.tagName === 'INPUT') {
                            e.target.closest('td').style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.5)';
                            e.target.closest('td').style.position = 'relative';
                            e.target.closest('td').style.zIndex = '10';
                        }
                    });

                    table.addEventListener('focusout', function (e) {
                        if (e.target.tagName === 'INPUT') {
                            e.target.closest('td').style.boxShadow = '';
                            e.target.closest('td').style.zIndex = '';
                        }
                    });
                });
            </script>

            <script>
                document.addEventListener('livewire:init', () => {
                    Livewire.on('redirect-to-url', (event) => {
                        console.log('Redirecting to:', event.url);
                        window.open(event.url, '_blank');
                    });
                });
            </script>
    @endif
</div>