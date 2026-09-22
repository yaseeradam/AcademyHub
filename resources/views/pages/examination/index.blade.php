@php
    use App\Models\Score;

    $scoreCount = Score::query()->count();
    $latestScoreAt = Score::query()->latest('updated_at')->value('updated_at');
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 p-8 shadow-2xl">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjEiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-30"></div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-black text-white">Examination</h1>
                        <p class="mt-2 text-green-100">Enter scores, generate broadsheets, and download report cards</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('results.entry') }}" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-green-600 shadow-lg hover:bg-green-50">Enter Results</a>
                        <a href="{{ route('results.broadsheet') }}" class="rounded-xl bg-white/20 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur-sm hover:bg-white/30">Broadsheet</a>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-green-100">Score Records</div>
                                <div class="mt-2 text-3xl font-black text-white">{{ number_format((int) $scoreCount) }}</div>
                            </div>
                            <div class="grid h-14 w-14 place-items-center rounded-xl bg-white/20 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-green-100">Last Updated</div>
                                <div class="mt-2 text-lg font-black text-white">{{ $latestScoreAt ? \Illuminate\Support\Carbon::parse($latestScoreAt)->diffForHumans() : '—' }}</div>
                            </div>
                            <div class="grid h-14 w-14 place-items-center rounded-xl bg-white/20 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-green-100">Report Cards</div>
                                <div class="mt-2 text-lg font-black text-white">Per Student</div>
                            </div>
                            <div class="grid h-14 w-14 place-items-center rounded-xl bg-white/20 text-white">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-50 to-emerald-100/60 p-6 shadow-lg ring-1 ring-green-300/40 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                <div class="absolute right-0 top-0 h-32 w-32 -translate-y-8 translate-x-8 rounded-full bg-green-500 opacity-10"></div>
                <div class="relative">
                    <div class="icon-3d grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-green-400 to-green-600 text-white shadow-xl shadow-green-500/30 transition-transform duration-500 group-hover:rotate-12 group-hover:scale-110">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div class="mt-5">
                        <div class="text-lg font-black text-gray-900">Score Entry</div>
                        <div class="mt-2 text-sm text-gray-700">
                            Capture CA1, CA2, and Exam scores. Grades are calculated automatically.
                        </div>
                        <div class="mt-5">
                            <a href="{{ route('results.entry') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:from-green-600 hover:to-emerald-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                                Open Score Entry
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50 to-indigo-100/60 p-6 shadow-lg ring-1 ring-blue-300/40 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                <div class="absolute right-0 top-0 h-32 w-32 -translate-y-8 translate-x-8 rounded-full bg-blue-500 opacity-10"></div>
                <div class="relative">
                    <div class="icon-3d grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-xl shadow-blue-500/30 transition-transform duration-500 group-hover:rotate-12 group-hover:scale-110">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <div class="mt-5">
                        <div class="text-lg font-black text-gray-900">Broadsheet</div>
                        <div class="mt-2 text-sm text-gray-700">
                            View class performance by subject, term, and session.
                        </div>
                        <div class="mt-5">
                            <a href="{{ route('results.broadsheet') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:from-blue-600 hover:to-indigo-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                                Open Broadsheet
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-50 to-violet-100/60 p-6 shadow-lg ring-1 ring-purple-300/40 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                <div class="absolute right-0 top-0 h-32 w-32 -translate-y-8 translate-x-8 rounded-full bg-purple-500 opacity-10"></div>
                <div class="relative">
                    <div class="icon-3d grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-purple-400 to-purple-600 text-white shadow-xl shadow-purple-500/30 transition-transform duration-500 group-hover:rotate-12 group-hover:scale-110">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="mt-5">
                        <div class="text-lg font-black text-gray-900">Report Cards</div>
                        <div class="mt-2 text-sm text-gray-700">
                            Download printable report cards from any student profile (Results tab).
                        </div>
                        <div class="mt-5">
                            @php($user = auth()->user())
                            @if ($user?->hasPermission('results.publish'))
                                <a href="{{ route('results.bulk-report-cards') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-purple-500 to-violet-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:from-purple-600 hover:to-violet-700">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Bulk Download
                                </a>
                            @else
                                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-bold text-gray-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Per Student
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
