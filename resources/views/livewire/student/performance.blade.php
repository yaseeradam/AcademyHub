<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Performance Tracking</h1>
            <p class="text-gray-600 mt-1 text-sm">Track your academic progress and identify areas for improvement</p>
        </div>
        <select wire:model.live="selectedTerm" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 self-start sm:self-auto">
            @foreach($this->availableTerms as $term)
                <option value="{{ $term['value'] }}">{{ $term['label'] }}</option>
            @endforeach
        </select>
    </div>

    @if($this->student && !empty($this->performanceData))
        {{-- Overview Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600">Average Score</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $this->performanceData['overview']['average_score'] }}</p>
                        <p class="text-xs text-gray-500">{{ $this->performanceData['overview']['percentage'] }}%</p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-full hidden sm:block">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $this->performanceData['overview']['grade'] === 'A' ? 'green' : ($this->performanceData['overview']['grade'] === 'F' ? 'red' : 'yellow') }}-100 text-{{ $this->performanceData['overview']['grade'] === 'A' ? 'green' : ($this->performanceData['overview']['grade'] === 'F' ? 'red' : 'yellow') }}-800">
                        Grade {{ $this->performanceData['overview']['grade'] }}
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600">Subjects Passed</p>
                        <p class="text-2xl font-bold text-green-600">{{ $this->performanceData['overview']['subjects_passed'] }}</p>
                        <p class="text-xs text-gray-500">of {{ $this->performanceData['overview']['total_subjects'] }}</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-full hidden sm:block">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600">Attendance Rate</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $this->performanceData['attendance_impact']['attendance_rate'] }}%</p>
                        <p class="text-xs text-gray-500">{{ $this->performanceData['attendance_impact']['present_days'] }}/{{ $this->performanceData['attendance_impact']['total_days'] }} days</p>
                    </div>
                    <div class="p-2 bg-purple-100 rounded-full hidden sm:block">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-600">Homework Rate</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $this->performanceData['homework_performance']['completion_rate'] }}%</p>
                        <p class="text-xs text-gray-500">{{ $this->performanceData['homework_performance']['submitted'] }}/{{ $this->performanceData['homework_performance']['total_assignments'] }} submitted</p>
                    </div>
                    <div class="p-2 bg-orange-100 rounded-full hidden sm:block">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-xl shadow">
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="flex -mb-px min-w-max">
                    <button wire:click="setTab('overview')" class="px-4 py-3 text-sm font-medium whitespace-nowrap {{ $activeTab === 'overview' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">Overview</button>
                    <button wire:click="setTab('subjects')" class="px-4 py-3 text-sm font-medium whitespace-nowrap {{ $activeTab === 'subjects' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">Subjects</button>
                    <button wire:click="setTab('trends')" class="px-4 py-3 text-sm font-medium whitespace-nowrap {{ $activeTab === 'trends' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">Trends</button>
                    <button wire:click="setTab('improvement')" class="px-4 py-3 text-sm font-medium whitespace-nowrap {{ $activeTab === 'improvement' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">Improvement</button>
                </nav>
            </div>

            <div class="p-4 sm:p-6">
                {{-- Overview Tab --}}
                @if($activeTab === 'overview')
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Your Strengths
                            </h3>
                            @if($this->performanceData['strengths_weaknesses']['strengths']->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($this->performanceData['strengths_weaknesses']['strengths'] as $strength)
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex justify-between items-start">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $strength['subject'] }}</p>
                                                <p class="text-sm text-gray-600 mt-1">Grade {{ $strength['grade'] }}</p>
                                            </div>
                                            <div class="text-right ml-4">
                                                <p class="text-xl font-bold text-green-600">{{ $strength['score'] }}</p>
                                                <p class="text-xs text-gray-600">{{ $strength['percentage'] }}%</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">Keep working hard to identify your strengths!</p>
                            @endif
                        </div>

                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                Areas Needing Attention
                            </h3>
                            @if($this->performanceData['strengths_weaknesses']['weaknesses']->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($this->performanceData['strengths_weaknesses']['weaknesses'] as $weakness)
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex justify-between items-start">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $weakness['subject'] }}</p>
                                                <p class="text-sm text-gray-600 mt-1">Grade {{ $weakness['grade'] }}</p>
                                            </div>
                                            <div class="text-right ml-4">
                                                <p class="text-xl font-bold text-red-600">{{ $weakness['score'] }}</p>
                                                <p class="text-xs text-gray-600">{{ $weakness['percentage'] }}%</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">Great job! No weak areas identified.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-base font-semibold text-gray-900 mb-2">Attendance Impact</h3>
                        <p class="text-gray-700 text-sm">{{ $this->performanceData['attendance_impact']['correlation'] }}</p>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-center sm:grid-cols-4">
                            <div><p class="text-xl font-bold text-blue-600">{{ $this->performanceData['attendance_impact']['present_days'] }}</p><p class="text-xs text-gray-600">Present</p></div>
                            <div><p class="text-xl font-bold text-red-600">{{ $this->performanceData['attendance_impact']['absent_days'] }}</p><p class="text-xs text-gray-600">Absent</p></div>
                            <div><p class="text-xl font-bold text-yellow-600">{{ $this->performanceData['attendance_impact']['late_days'] }}</p><p class="text-xs text-gray-600">Late</p></div>
                            <div><p class="text-xl font-bold text-green-600">{{ $this->performanceData['attendance_impact']['attendance_rate'] }}%</p><p class="text-xs text-gray-600">Rate</p></div>
                        </div>
                    </div>
                @endif

                {{-- Subject Performance Tab --}}
                @if($activeTab === 'subjects')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">CA1</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">CA2</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Exam</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Grade</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">%</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pos</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($this->performanceData['subject_performance'] as $subject)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $subject['subject'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['ca1'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['ca2'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['exam'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-semibold text-gray-900">{{ $subject['total'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $subject['grade'] === 'A' ? 'green' : ($subject['grade'] === 'F' ? 'red' : 'yellow') }}-100 text-{{ $subject['grade'] === 'A' ? 'green' : ($subject['grade'] === 'F' ? 'red' : 'yellow') }}-800">{{ $subject['grade'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['percentage'] }}%</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-700">{{ $subject['position'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Progress Trends Tab --}}
                @if($activeTab === 'trends')
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-3">Term-by-Term Comparison</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                @foreach($this->performanceData['term_comparison'] as $term)
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                                        <p class="text-sm font-medium text-gray-600">Term {{ $term['term'] }}</p>
                                        <p class="text-3xl font-bold text-blue-600 mt-1">{{ $term['average_score'] }}</p>
                                        <p class="text-sm text-gray-700 mt-1">{{ $term['percentage'] }}% • Grade {{ $term['grade'] }}</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $term['subjects_count'] }} subjects</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($this->performanceData['progress_trend']->isNotEmpty())
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 mb-3">Performance Trend</h3>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-end justify-around h-48">
                                        @foreach($this->performanceData['progress_trend'] as $trend)
                                            <div class="flex flex-col items-center">
                                                <div class="bg-blue-500 rounded-t-lg w-10 sm:w-14" style="height: {{ $trend['percentage'] * 1.5 }}px;"></div>
                                                <p class="text-xs font-medium text-gray-900 mt-2">{{ $trend['average'] }}</p>
                                                <p class="text-xs text-gray-600">{{ $trend['term'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($this->performanceData['cbt_performance']['total_exams'] > 0)
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <h3 class="text-base font-semibold text-gray-900 mb-3">CBT Exam Performance</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center"><p class="text-2xl font-bold text-purple-600">{{ $this->performanceData['cbt_performance']['total_exams'] }}</p><p class="text-xs text-gray-600">Total Exams</p></div>
                                    <div class="text-center"><p class="text-2xl font-bold text-blue-600">{{ $this->performanceData['cbt_performance']['average_percent'] }}%</p><p class="text-xs text-gray-600">Average</p></div>
                                    <div class="text-center"><p class="text-2xl font-bold text-green-600">{{ $this->performanceData['cbt_performance']['exams_passed'] }}</p><p class="text-xs text-gray-600">Passed</p></div>
                                    <div class="text-center"><p class="text-2xl font-bold text-red-600">{{ $this->performanceData['cbt_performance']['exams_failed'] }}</p><p class="text-xs text-gray-600">Failed</p></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Improvement Areas Tab --}}
                @if($activeTab === 'improvement')
                    <div class="space-y-3">
                        <h3 class="text-base font-semibold text-gray-900">Subject-wise Progress Analysis</h3>
                        @foreach($this->performanceData['improvement_areas'] as $area)
                            <div class="border rounded-lg p-4 {{ $area['needs_attention'] ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white' }}">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $area['subject'] }}</p>
                                        <div class="flex flex-wrap gap-3 mt-1 text-xs text-gray-600">
                                            <span>Prev: <span class="font-semibold">{{ $area['previous_score'] }}</span></span>
                                            <span>Current: <span class="font-semibold">{{ $area['current_score'] }}</span></span>
                                            <span>Change: <span class="font-semibold {{ $area['change'] > 0 ? 'text-green-600' : ($area['change'] < 0 ? 'text-red-600' : 'text-gray-600') }}">{{ $area['change'] > 0 ? '+' : '' }}{{ $area['change'] }}</span></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 sm:flex-col sm:items-end sm:gap-1">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $area['trend'] === 'Improving' ? 'bg-green-100 text-green-800' : ($area['trend'] === 'Declining' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">{{ $area['trend'] }}</span>
                                        @if($area['needs_attention'])
                                            <p class="text-xs text-red-600 font-semibold">Needs Attention</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-yellow-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-gray-700">No performance data available for the selected term.</p>
        </div>
    @endif
</div>
