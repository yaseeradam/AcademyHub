<div class="space-y-6">
    <!-- Header -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-pink-500 via-rose-500 to-red-500 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Parent Portal</h1>
                    <p class="mt-2 text-base text-pink-50">Welcome back, {{ auth()->user()->name }}. Track your children's progress.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm">
                        {{ $this->children->count() }} {{ Str::plural('Child', $this->children->count()) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Children Selection -->
    @if($this->children->count() > 1)
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Select Child</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->children as $child)
                    <button 
                        wire:click="selectChild({{ $child->id }})"
                        class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all {{ $selectedChildId === $child->id ? 'border-pink-500 bg-pink-50' : 'border-gray-200 hover:border-pink-300' }}"
                    >
                        @if($child->passport_photo_url)
                            <img src="{{ $child->passport_photo_url }}" class="h-12 w-12 rounded-full object-cover" />
                        @else
                            <div class="h-12 w-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-700 font-bold">
                                {{ substr($child->first_name, 0, 1) }}
                            </div>
                        @endif
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">{{ $child->full_name }}</div>
                            <div class="text-sm text-gray-500">{{ $child->schoolClass?->name ?? 'Unassigned' }}</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if($this->selectedChild)
        <!-- Selected Child Info -->
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="flex items-center gap-4 mb-6">
                @if($this->selectedChild->passport_photo_url)
                    <img src="{{ $this->selectedChild->passport_photo_url }}" class="h-16 w-16 rounded-full object-cover shadow-sm" />
                @else
                    <div class="h-16 w-16 rounded-full bg-pink-100 flex items-center justify-center text-pink-700 font-bold text-xl">
                        {{ substr($this->selectedChild->first_name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $this->selectedChild->full_name }}</h2>
                    <p class="text-gray-600">{{ $this->selectedChild->admission_number }} • {{ $this->selectedChild->schoolClass?->name ?? 'Unassigned' }} {{ $this->selectedChild->section?->name ?? '' }}</p>
                </div>
            </div>

            <!-- Session/Term Filters -->
            <div class="flex flex-wrap gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                    <input wire:model.live="session" type="text" placeholder="2025/2026" class="mt-1 input-compact min-w-32" />
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                    <select wire:model.live="term" class="mt-1 select min-w-20">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Academic Performance -->
            <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100">Average Score</p>
                        <p class="text-3xl font-bold">{{ $this->childPerformanceStats['average'] }}%</p>
                        <p class="text-sm text-blue-100">Grade: {{ $this->childPerformanceStats['grade'] }}</p>
                    </div>
                    <svg class="h-12 w-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Attendance -->
            <div class="rounded-2xl bg-gradient-to-br from-green-500 to-green-600 p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100">Attendance</p>
                        <p class="text-3xl font-bold">{{ $this->childAttendance['percentage'] }}%</p>
                        <p class="text-sm text-green-100">{{ $this->childAttendance['present'] }}/{{ $this->childAttendance['total'] }} days</p>
                    </div>
                    <svg class="h-12 w-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>

            <!-- Fees Status -->
            <div class="rounded-2xl bg-gradient-to-br from-yellow-500 to-yellow-600 p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100">Outstanding Fees</p>
                        <p class="text-3xl font-bold">₦{{ number_format($this->childFees['outstanding']) }}</p>
                        <p class="text-sm text-yellow-100">Paid: ₦{{ number_format($this->childFees['paid']) }}</p>
                    </div>
                    <svg class="h-12 w-12 text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>

            <!-- Subjects Count -->
            <div class="rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100">Subjects</p>
                        <p class="text-3xl font-bold">{{ $this->childPerformanceStats['subjects'] }}</p>
                        <p class="text-sm text-purple-100">Total: {{ $this->childPerformanceStats['total'] }} marks</p>
                    </div>
                    <svg class="h-12 w-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Recent Scores -->
        @if($this->childScores->isNotEmpty())
            <div class="rounded-2xl bg-white p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Recent Scores</h3>
                    <a href="{{ route('results.report-card', ['student' => $this->selectedChild, 'term' => $term, 'session' => $session]) }}" 
                       target="_blank" 
                       class="btn-primary text-sm">
                        Download Report Card
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Subject</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">CA</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Exam</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Total</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($this->childScores as $score)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $score->subject->name }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $score->ca ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $score->exam ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ $score->total ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ ($score->total ?? 0) >= 70 ? 'bg-green-100 text-green-800' : 
                                               (($score->total ?? 0) >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $this->getGrade($score->total ?? 0) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">No scores available</h3>
                <p class="mt-2 text-gray-500">Scores for {{ $this->selectedChild->full_name }} will appear here once teachers enter them.</p>
            </div>
        @endif

    @elseif($this->children->isEmpty())
        <!-- No Children Linked -->
        <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <h3 class="mt-4 text-xl font-semibold text-gray-900">No children linked</h3>
            <p class="mt-2 text-gray-500">Please contact the school administrator to link your children to this parent account.</p>
            <div class="mt-6">
                <a href="{{ route('profile') }}" class="btn-primary">
                    Update Profile
                </a>
            </div>
        </div>
    @else
        <!-- Select a Child -->
        <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
            <svg class="mx-auto h-16 w-16 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 011 1v2a1 1 0 01-1 1h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V8H3a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
            </svg>
            <h3 class="mt-4 text-xl font-semibold text-gray-900">Select a child</h3>
            <p class="mt-2 text-gray-500">Choose one of your children above to view their academic progress and information.</p>
        </div>
    @endif
</div>