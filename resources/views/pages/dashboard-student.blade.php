@extends('layouts.app')

@section('content')
<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-700 p-4 sm:p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-25"></div>
        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-black/10"></div>
        
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2">
                <span class="h-2 w-2 rounded-full bg-emerald-300 animate-pulse"></span>
                <span class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-emerald-100">Student Portal</span>
            </div>
            <h1 class="text-xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight">Welcome back, {{ session('student_name') }}!</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs sm:text-sm font-semibold text-emerald-100">
                <span class="rounded-lg bg-white/15 px-2.5 py-0.5 backdrop-blur-sm">{{ session('student_admission') }}</span>
                <span>•</span>
                <span class="rounded-lg bg-white/15 px-2.5 py-0.5 backdrop-blur-sm">{{ session('student_class') }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Stats (2-Column Mobile App Grid) -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-5">
        <div class="rounded-2xl sm:rounded-3xl bg-white p-3.5 sm:p-5 shadow-sm border border-slate-100 transition-all active:scale-[0.98] flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Attendance</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">95%</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-2 sm:p-2.5 text-emerald-600 shrink-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-[10px] sm:text-xs font-semibold text-emerald-600">On Track</p>
        </div>

        <div class="rounded-2xl sm:rounded-3xl bg-white p-3.5 sm:p-5 shadow-sm border border-slate-100 transition-all active:scale-[0.98] flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Average Score</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">85%</p>
                </div>
                <div class="rounded-xl bg-blue-50 p-2 sm:p-2.5 text-blue-600 shrink-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-[10px] sm:text-xs font-semibold text-blue-600">Term Average</p>
        </div>

        <div class="col-span-2 md:col-span-1 rounded-2xl sm:rounded-3xl bg-white p-3.5 sm:p-5 shadow-sm border border-slate-100 transition-all active:scale-[0.98] flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Class Rank</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">#5</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-2 sm:p-2.5 text-amber-600 shrink-0">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-[10px] sm:text-xs font-semibold text-amber-600">Top 10% in Class</p>
        </div>
    </div>

    <!-- Features Overview -->
    <div class="rounded-2xl sm:rounded-3xl bg-white p-4 sm:p-8 text-center shadow-sm border border-slate-100">
        <div class="mx-auto mb-3 sm:mb-4 h-12 w-12 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md shadow-emerald-500/20">
            <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="text-base sm:text-xl font-bold text-gray-900 mb-1 sm:mb-2">Student Portal Hub</h3>
        <p class="text-xs sm:text-sm text-gray-500 mb-4 sm:mb-6 max-w-lg mx-auto">Access your learning records, attendance history, homework submissions, and progress cards.</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-4 text-left max-w-2xl mx-auto">
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div class="h-8 w-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs sm:text-sm font-semibold text-gray-700">Academic Scores &amp; Results</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div class="h-8 w-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="text-xs sm:text-sm font-semibold text-gray-700">Daily Attendance Log</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div class="h-8 w-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span class="text-xs sm:text-sm font-semibold text-gray-700">Report Cards &amp; Transcripts</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div class="h-8 w-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="text-xs sm:text-sm font-semibold text-gray-700">Online CBT Assessments</span>
            </div>
        </div>
        
        <div class="mt-6">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 px-5 py-2.5 text-xs sm:text-sm font-bold text-slate-700 transition active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection