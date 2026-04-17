<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Student Performance Analytics</h1>
            <p class="text-gray-600 mt-1">Analyze individual student performance and identify improvement areas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Student Selection Panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Student</h2>
                
                {{-- Filters --}}
                <div class="space-y-3 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                        <select wire:model.live="selectedClass" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Classes</option>
                            @foreach($this->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm" 
                               placeholder="Name or admission number..."
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                        <select wire:model.live="selectedTerm" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($this->availableTerms as $term)
                                <option value="{{ $term['value'] }}">{{ $term['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Student List --}}
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($this->students as $student)
                        <button wire:click="selectStudent({{ $student->id }})" 
                                class="w-full text-left p-3 rounded-lg border transition-colors {{ $selectedStudent === $student->id ? 'bg-blue-50 border-blue-500' : 'bg-gray-50 border-gray-200 hover:bg-gray-100' }}">
                            <p class="font-medium text-gray-900">{{ $student->full_name }}</p>
                            <p class="text-sm text-gray-600">{{ $student->admission_number }}</p>
                            <p class="text-xs text-gray-500">{{ $student->schoolClass?->name }}</p>
                        </button>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No students found</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Performance Display Panel --}}
        <div class="lg:col-span-2">
            @if($this->selectedStudentModel)
                @php
                    $perfData = $this->performanceData;
                    $hasScores = isset($perfData['overview']) && $perfData['overview']['total_subjects'] > 0;
                @endphp

                @if($hasScores)
                <div class="space-y-6">
                    {{-- Student Info Header --}}
                    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow p-6 text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold">{{ $this->selectedStudentModel->full_name }}</h2>
                                <p class="text-blue-100 mt-1">{{ $this->selectedStudentModel->admission_number }} • {{ $this->selectedStudentModel->schoolClass?->name }}</p>
                            </div>
                            <button wire:click="clearSelection" class="text-white hover:text-blue-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Performance Metrics --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg shadow p-4">
                            <p class="text-sm text-gray-600">Average</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $perfData['overview']['average_score'] }}</p>
                            <p class="text-xs text-gray-500">{{ $perfData['overview']['percentage'] }}%</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <p class="text-sm text-gray-600">Grade</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $perfData['overview']['grade'] }}</p>
                            <p class="text-xs text-gray-500">Current</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <p class="text-sm text-gray-600">Passed</p>
                            <p class="text-2xl font-bold text-green-600">{{ $perfData['overview']['subjects_passed'] }}</p>
                            <p class="text-xs text-gray-500">of {{ $perfData['overview']['total_subjects'] }}</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <p class="text-sm text-gray-600">Attendance</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $perfData['attendance_impact']['attendance_rate'] }}%</p>
                            <p class="text-xs text-gray-500">{{ $perfData['attendance_impact']['present_days'] }}/{{ $perfData['attendance_impact']['total_days'] }}</p>
                        </div>
                    </div>

                    {{-- Strengths & Weaknesses --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Strengths
                            </h3>
                            @if($perfData['strengths_weaknesses']['strengths']->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($perfData['strengths_weaknesses']['strengths'] as $strength)
                                        <div class="bg-green-50 border border-green-200 rounded p-3">
                                            <div class="flex justify-between">
                                                <span class="font-medium text-gray-900">{{ $strength['subject'] }}</span>
                                                <span class="text-green-600 font-bold">{{ $strength['score'] }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">Grade {{ $strength['grade'] }} • {{ $strength['percentage'] }}%</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">No strong subjects identified yet.</p>
                            @endif
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Needs Attention
                            </h3>
                            @if($perfData['strengths_weaknesses']['weaknesses']->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($perfData['strengths_weaknesses']['weaknesses'] as $weakness)
                                        <div class="bg-red-50 border border-red-200 rounded p-3">
                                            <div class="flex justify-between">
                                                <span class="font-medium text-gray-900">{{ $weakness['subject'] }}</span>
                                                <span class="text-red-600 font-bold">{{ $weakness['score'] }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">Grade {{ $weakness['grade'] }} • {{ $weakness['percentage'] }}%</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">No weak areas identified.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Subject Performance Table --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Subject Performance</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">CA1</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">CA2</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Exam</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">%</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($perfData['subject_performance'] as $subject)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $subject['subject'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['ca1'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['ca2'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['exam'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-gray-900">{{ $subject['total'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $subject['grade'] === 'A' ? 'green' : ($subject['grade'] === 'F' ? 'red' : 'yellow') }}-100 text-{{ $subject['grade'] === 'A' ? 'green' : ($subject['grade'] === 'F' ? 'red' : 'yellow') }}-800">
                                                    {{ $subject['grade'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['percentage'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Improvement Areas --}}
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress Analysis</h3>
                        <div class="space-y-3">
                            @foreach($perfData['improvement_areas'] as $area)
                                <div class="border rounded-lg p-4 {{ $area['needs_attention'] ? 'border-red-300 bg-red-50' : 'border-gray-200' }}">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $area['subject'] }}</p>
                                            <div class="flex gap-4 mt-1 text-sm text-gray-600">
                                                <span>Previous: {{ $area['previous_score'] }}</span>
                                                <span>Current: {{ $area['current_score'] }}</span>
                                                <span class="{{ $area['change'] > 0 ? 'text-green-600' : ($area['change'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                                    {{ $area['change'] > 0 ? '+' : '' }}{{ $area['change'] }}
                                                </span>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            {{ $area['trend'] === 'Improving' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $area['trend'] === 'Declining' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $area['trend'] === 'Stable' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ $area['trend'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Attendance Correlation --}}
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Attendance Impact Analysis</h3>
                        <p class="text-gray-700 mb-4">{{ $perfData['attendance_impact']['correlation'] }}</p>
                        <div class="grid grid-cols-4 gap-4 text-center">
                            <div>
                                <p class="text-xl font-bold text-blue-600">{{ $perfData['attendance_impact']['present_days'] }}</p>
                                <p class="text-sm text-gray-600">Present</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-red-600">{{ $perfData['attendance_impact']['absent_days'] }}</p>
                                <p class="text-sm text-gray-600">Absent</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-yellow-600">{{ $perfData['attendance_impact']['late_days'] }}</p>
                                <p class="text-sm text-gray-600">Late</p>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-green-600">{{ $perfData['attendance_impact']['attendance_rate'] }}%</p>
                                <p class="text-sm text-gray-600">Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    {{-- No Performance Data --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-12 text-center">
                        <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Performance Data Available</h3>
                        <p class="text-gray-600 mb-2">{{ $this->selectedStudentModel->full_name }} doesn't have any scores recorded for the selected term.</p>
                        <p class="text-sm text-gray-500">Please ensure scores have been entered in the Score Entry module.</p>
                        <div class="mt-6">
                            <a href="{{ route('results.entry') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Go to Score Entry
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Select a Student</h3>
                    <p class="text-gray-600">Choose a student from the list to view their performance analytics</p>
                </div>
            @endif
        </div>
    </div>
</div>
