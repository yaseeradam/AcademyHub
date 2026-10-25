<div class="space-y-6" x-data="{}">
    <!-- Header -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Analytics Dashboard</h1>
                    <p class="mt-2 text-base text-indigo-50">Comprehensive insights into school performance</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <select wire:model.live="selectedPeriod" class="rounded-xl bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm">
                        <option value="current_week">This Week</option>
                        <option value="current_month">This Month</option>
                        <option value="current_term">Current Term</option>
                    </select>
                    <select wire:model.live="selectedClass" class="rounded-xl bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm">
                        <option value="">All Classes</option>
                        @foreach($this->classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Students Card -->
        <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100">Total Students</p>
                    <p class="text-3xl font-bold">{{ number_format($this->studentStats['total']) }}</p>
                    <p class="text-sm text-blue-100">{{ $this->studentStats['active_percentage'] }}% Active</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Academic Performance Card -->
        <div class="rounded-2xl bg-gradient-to-br from-green-500 to-green-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100">Avg Score</p>
                    <p class="text-3xl font-bold">{{ $this->academicPerformance['average_score'] }}</p>
                    <p class="text-sm text-green-100">{{ $this->academicPerformance['total_assessments'] }} Assessments</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Attendance Card -->
        <div class="rounded-2xl bg-gradient-to-br from-yellow-500 to-orange-500 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100">Attendance</p>
                    <p class="text-3xl font-bold">{{ $this->attendanceStats['attendance_rate'] }}%</p>
                    <p class="text-sm text-yellow-100">{{ number_format($this->attendanceStats['present']) }} Present</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Financial Card -->
        @if(in_array(auth()->user()->role, ['admin', 'bursar', 'proprietor'], true))
        <div class="rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100">Revenue</p>
                    <p class="text-3xl font-bold">{{ config('academyhub.currency_symbol', '₦') }}{{ number_format($this->financialStats['total_revenue']) }}</p>
                    <p class="text-sm text-purple-100">{{ $this->financialStats['transaction_count'] }} Transactions</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Grade Distribution Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Grade Distribution</h3>
                <a href="{{ route('analytics.export.performance', ['class_id' => $selectedClass, 'session' => $this->currentSession?->name, 'term' => $this->currentTerm?->term_number]) }}"
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">Export CSV</a>
            </div>
            <div class="h-64" wire:ignore>
                <canvas id="gradeChart"></canvas>
            </div>
        </div>

        <!-- Attendance Trend Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Attendance Trend (7 Days)</h3>
                <a href="{{ route('analytics.export.attendance', ['class_id' => $selectedClass]) }}"
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">Export CSV</a>
            </div>
            <div class="h-64" wire:ignore>
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- Subject Performance Chart -->
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Performing Subjects</h3>
            <div class="h-64" wire:ignore>
                <canvas id="subjectChart"></canvas>
            </div>
        </div>

        <!-- Financial Trend Chart -->
        @if(in_array(auth()->user()->role, ['admin', 'bursar', 'proprietor'], true))
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Revenue Trend (6 Months)</h3>
                <a href="{{ route('analytics.export.financial') }}"
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">Export CSV</a>
            </div>
            <div class="h-64" wire:ignore>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        @endif
    </div>

    <!-- Class Performance Comparison -->
    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Class Performance Comparison</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assessments</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->classPerformanceComparison as $class)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $class['class_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $class['student_count'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class['average_score'] >= 70 ? 'bg-green-100 text-green-800' : ($class['average_score'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $class['average_score'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $class['attendance_rate'] }}%"></div>
                                </div>
                                <span class="text-sm text-gray-500">{{ $class['attendance_rate'] }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $class['total_assessments'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- CBT Performance -->
    @if($this->cbtStats['total_attempts'] > 0)
    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">CBT Performance Overview</h3>
            <a href="{{ route('analytics.export.cbt', ['class_id' => $selectedClass]) }}" 
               class="text-sm text-blue-600 hover:text-blue-800 font-medium">Export CSV</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $this->cbtStats['completion_rate'] }}%</div>
                <div class="text-sm text-gray-500">Completion Rate</div>
                <div class="text-xs text-gray-400">{{ $this->cbtStats['completed_attempts'] }}/{{ $this->cbtStats['total_attempts'] }} attempts</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $this->cbtStats['average_percent'] }}%</div>
                <div class="text-sm text-gray-500">Average Score</div>
                <div class="text-xs text-gray-400">{{ $this->cbtStats['average_score'] }} points avg</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ $this->cbtStats['total_attempts'] }}</div>
                <div class="text-sm text-gray-500">Total Attempts</div>
                <div class="text-xs text-gray-400">All exams combined</div>
            </div>
        </div>
        
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Performance Distribution</h4>
            <div class="space-y-2">
                @foreach($this->cbtStats['performance_distribution'] as $range => $count)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $range }}</span>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $this->cbtStats['total_attempts'] > 0 ? ($count / $this->cbtStats['total_attempts']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Reactive Chart Dataset (Preserved across Livewire component morphs) --}}
    <div id="analyticsChartData" class="hidden"
         data-grade-labels='@json(array_keys($this->academicPerformance['grade_distribution']))'
         data-grade-values='@json(array_values($this->academicPerformance['grade_distribution']))'
         data-attendance-labels='@json(array_column($this->attendanceStats['daily_trend'], 'date'))'
         data-attendance-values='@json(array_column($this->attendanceStats['daily_trend'], 'attendance_rate'))'
         data-subject-labels='@json(array_column($this->academicPerformance['subject_performance'], 'subject'))'
         data-subject-values='@json(array_column($this->academicPerformance['subject_performance'], 'average'))'
         @if(in_array(auth()->user()->role, ['admin', 'bursar', 'proprietor'], true))
         data-revenue-labels='@json(array_column($this->financialStats['monthly_trend'], 'month'))'
         data-revenue-values='@json(array_column($this->financialStats['monthly_trend'], 'revenue'))'
         @endif
    ></div>

    {{-- Load Chart.js with local asset and CDN fallback --}}
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}?v=4.4"></script>
    <script>
        (function() {
            window.__analyticsCharts = window.__analyticsCharts || {};

            function ensureChartJs(callback) {
                if (window.Chart) {
                    callback();
                    return;
                }
                if (!document.getElementById('chartjs-script')) {
                    const script = document.createElement('script');
                    script.id = 'chartjs-script';
                    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                    script.onload = () => callback();
                    document.head.appendChild(script);
                } else {
                    const timer = setInterval(() => {
                        if (window.Chart) {
                            clearInterval(timer);
                            callback();
                        }
                    }, 60);
                    setTimeout(() => clearInterval(timer), 5000);
                }
            }

            function getDataset() {
                const el = document.getElementById('analyticsChartData');
                if (!el) {
                    return {
                        grade: { labels: [], data: [] },
                        attendance: { labels: [], data: [] },
                        subject: { labels: [], data: [] },
                        revenue: null
                    };
                }
                const parseSafe = (val, fallback = []) => {
                    try { return val ? JSON.parse(val) : fallback; } catch (e) { return fallback; }
                };
                return {
                    grade: {
                        labels: parseSafe(el.dataset.gradeLabels),
                        data:   parseSafe(el.dataset.gradeValues)
                    },
                    attendance: {
                        labels: parseSafe(el.dataset.attendanceLabels),
                        data:   parseSafe(el.dataset.attendanceValues)
                    },
                    subject: {
                        labels: parseSafe(el.dataset.subjectLabels),
                        data:   parseSafe(el.dataset.subjectValues)
                    },
                    revenue: el.dataset.revenueLabels ? {
                        labels: parseSafe(el.dataset.revenueLabels),
                        data:   parseSafe(el.dataset.revenueValues)
                    } : null
                };
            }

            function destroyChart(id) {
                if (window.__analyticsCharts[id]) {
                    try {
                        window.__analyticsCharts[id].destroy();
                    } catch (e) {}
                    delete window.__analyticsCharts[id];
                }
            }

            function initAnalyticsCharts() {
                if (typeof Chart === 'undefined') {
                    ensureChartJs(initAnalyticsCharts);
                    return;
                }

                const cd = getDataset();

                // 1. Grade Distribution
                destroyChart('gradeChart');
                const gradeCtx = document.getElementById('gradeChart');
                if (gradeCtx) {
                    const hasGradeData = cd.grade.labels && cd.grade.labels.length > 0;
                    window.__analyticsCharts['gradeChart'] = new Chart(gradeCtx, {
                        type: 'doughnut',
                        data: {
                            labels: hasGradeData ? cd.grade.labels : ['No Assessments'],
                            datasets: [{
                                data: hasGradeData ? cd.grade.data : [1],
                                backgroundColor: hasGradeData ? ['#10B981','#3B82F6','#F59E0B','#EF4444','#8B5CF6'] : ['#e2e8f0'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: { enabled: hasGradeData }
                            }
                        }
                    });
                }

                // 2. Attendance Trend
                destroyChart('attendanceChart');
                const attCtx = document.getElementById('attendanceChart');
                if (attCtx) {
                    window.__analyticsCharts['attendanceChart'] = new Chart(attCtx, {
                        type: 'line',
                        data: {
                            labels: cd.attendance.labels || [],
                            datasets: [{
                                label: 'Attendance Rate (%)',
                                data: cd.attendance.data || [],
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59,130,246,0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, max: 100 }
                            }
                        }
                    });
                }

                // 3. Subject Performance
                destroyChart('subjectChart');
                const subCtx = document.getElementById('subjectChart');
                if (subCtx) {
                    const hasSubjectData = cd.subject.labels && cd.subject.labels.length > 0;
                    window.__analyticsCharts['subjectChart'] = new Chart(subCtx, {
                        type: 'bar',
                        data: {
                            labels: hasSubjectData ? cd.subject.labels : ['No Scores'],
                            datasets: [{
                                label: 'Average Score',
                                data: hasSubjectData ? cd.subject.data : [0],
                                backgroundColor: '#10B981',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }

                // 4. Revenue Trend (Available for admin, bursar, and proprietor)
                destroyChart('revenueChart');
                const revCtx = document.getElementById('revenueChart');
                if (revCtx && cd.revenue) {
                    window.__analyticsCharts['revenueChart'] = new Chart(revCtx, {
                        type: 'line',
                        data: {
                            labels: cd.revenue.labels || [],
                            datasets: [{
                                label: 'Revenue',
                                data: cd.revenue.data || [],
                                borderColor: '#8B5CF6',
                                backgroundColor: 'rgba(139,92,246,0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            }

            window.initAnalyticsCharts = initAnalyticsCharts;

            // 1. Initial page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => ensureChartJs(initAnalyticsCharts));
            } else {
                ensureChartJs(initAnalyticsCharts);
            }

            // 2. Livewire 3 SPA Navigation
            document.addEventListener('livewire:navigated', () => {
                ensureChartJs(initAnalyticsCharts);
            });

            // 3. Livewire 3 DOM Morph Updates (when filters change)
            if (window.Livewire) {
                Livewire.hook('morph.updated', () => {
                    ensureChartJs(initAnalyticsCharts);
                });
            } else {
                document.addEventListener('livewire:init', () => {
                    Livewire.hook('morph.updated', () => {
                        ensureChartJs(initAnalyticsCharts);
                    });
                });
            }
        })();
    </script>
</div>