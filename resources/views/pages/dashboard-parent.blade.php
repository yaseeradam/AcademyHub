@php
    use App\Models\Announcement;
    use App\Models\AttendanceSheet;
    use App\Models\SchoolEvent;
    use App\Models\Transaction;
    use App\Models\Score;

    $user = auth()->user();
    $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
    $children = $user->students()->with(['schoolClass', 'section'])->get();

    // Latest 3 announcements
    $announcements = Announcement::latest()->limit(3)->get();

    // Per-child: attendance & fee data
    $childData = [];
    foreach ($children as $child) {
        // Attendance (last 30 days)
        $attendanceDays = \App\Models\AttendanceMark::where('student_id', $child->id)
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->count();
        $presentDays = \App\Models\AttendanceMark::where('student_id', $child->id)
            ->where('status', 'present')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->count();

        // Fee status: sum of all transactions vs fee structure
        $paid = (float) Transaction::where('student_id', $child->id)
            ->where('type', 'Income')->where('is_void', false)->sum('amount_paid');
        $due = (float) \App\Models\FeeStructure::where('class_id', $child->class_id)->sum('amount_due');
        $feeStatus = $due <= 0 ? 'unknown' : ($paid >= $due ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'));

        // Pending homework
        $pendingHw = \App\Models\Homework::where('class_id', $child->class_id)
            ->where('due_date', '>=', today())->orderBy('due_date')->limit(2)->get();

        $childData[$child->id] = [
            'attendanceDays' => $attendanceDays,
            'presentDays'    => $presentDays,
            'feeStatus'      => $feeStatus,
            'paid'           => $paid,
            'due'            => $due,
            'pendingHw'      => $pendingHw,
        ];
    }
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-2xl shadow-lg" style="background: linear-gradient(135deg, #3730a3 0%, #4f46e5 50%, #6366f1 100%);">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px),radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="absolute right-0 top-0 h-56 w-56 -translate-y-20 translate-x-20 rounded-full bg-white/10"></div>
        <div class="relative px-8 py-7">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="h-2 w-2 rounded-full bg-indigo-300 animate-pulse"></span>
                        <span class="text-xs font-semibold uppercase tracking-widest text-indigo-200">Parent Portal</span>
                    </div>
                    <h1 class="text-3xl font-black text-white tracking-tight">Hello, {{ $user->name }}!</h1>
                    <p class="mt-1 text-sm font-medium text-indigo-200">{{ $schoolName }} — {{ now()->format('l, F j, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex flex-col items-center justify-center rounded-2xl bg-white/15 backdrop-blur px-5 py-3 border border-white/20">
                        <div class="text-2xl font-black text-white">{{ $children->count() }}</div>
                        <div class="text-xs font-semibold text-indigo-200">{{ Str::plural('Child', $children->count()) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Announcements Strip --}}
    @if($announcements->isNotEmpty())
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="h-5 w-5 rounded-md bg-amber-100 flex items-center justify-center">
                    <svg class="h-3 w-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <span class="text-sm font-semibold text-slate-900">School Announcements</span>
            </div>
            <span class="text-xs text-slate-400">Latest updates</span>
        </div>
        <div class="divide-y divide-slate-50">
            @foreach($announcements as $ann)
            <div class="flex items-start gap-3 px-5 py-3">
                <div class="h-1.5 w-1.5 rounded-full bg-indigo-400 mt-2 flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-slate-800 truncate">{{ $ann->title ?? $ann->subject ?? 'Announcement' }}</div>
                    <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ Str::limit(strip_tags($ann->body ?? $ann->message ?? ''), 100) }}</div>
                </div>
                <div class="text-xs text-slate-400 flex-shrink-0">{{ $ann->created_at->diffForHumans() }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Children Cards --}}
    <div>
        <div class="text-sm font-semibold text-slate-900 mb-3">My Children</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse($children as $child)
            @php $cd = $childData[$child->id]; @endphp
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden hover:shadow-md transition-shadow">
                {{-- Child Header --}}
                <div class="flex items-center gap-4 p-5 border-b border-slate-50">
                    @if($child->passport_photo_url)
                        <img src="{{ $child->passport_photo_url }}" class="h-14 w-14 rounded-xl object-cover shadow-sm" />
                    @else
                        <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white font-black text-xl shadow-sm">
                            {{ substr($child->first_name, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="text-base font-bold text-slate-900 truncate">{{ $child->full_name }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $child->admission_number }}</div>
                        <span class="inline-flex mt-1.5 items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                            {{ $child->schoolClass?->name ?? 'Unassigned' }} {{ $child->section?->name ?? '' }}
                        </span>
                    </div>
                    {{-- Fee badge --}}
                    @if($cd['feeStatus'] === 'paid')
                        <span class="flex-shrink-0 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Fees Paid
                        </span>
                    @elseif($cd['feeStatus'] === 'partial')
                        <span class="flex-shrink-0 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Partial
                        </span>
                    @elseif($cd['feeStatus'] === 'unpaid')
                        <span class="flex-shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Unpaid
                        </span>
                    @endif
                </div>

                <div class="p-5 space-y-4">
                    {{-- Attendance Bar --}}
                    @if($cd['attendanceDays'] > 0)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-slate-600">Attendance (30 days)</span>
                            <span class="text-xs font-bold text-slate-800">{{ $cd['presentDays'] }}/{{ $cd['attendanceDays'] }} days</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            @php $pct = $cd['attendanceDays'] > 0 ? round($cd['presentDays'] / $cd['attendanceDays'] * 100) : 0; @endphp
                            <div class="h-full rounded-full transition-all duration-500
                                {{ $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                                style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="text-right mt-0.5 text-xs text-slate-500">{{ $pct }}% present</div>
                    </div>
                    @endif

                    {{-- Pending Homework --}}
                    @if($cd['pendingHw']->isNotEmpty())
                    <div>
                        <div class="text-xs font-semibold text-slate-600 mb-2">Upcoming Homework</div>
                        <div class="space-y-1.5">
                            @foreach($cd['pendingHw'] as $hw)
                            <div class="flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-2">
                                <svg class="h-3.5 w-3.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <span class="text-xs font-semibold text-amber-800 truncate flex-1">{{ $hw->title }}</span>
                                <span class="text-xs text-amber-600 flex-shrink-0">Due {{ $hw->due_date->format('M j') }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('results.report-card', $child) }}" target="_blank"
                           class="flex items-center justify-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Result PDF
                        </a>
                        <a href="{{ route('messages.index') }}"
                           class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            Message School
                        </a>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-span-full rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-12 text-center">
                    <div class="mx-auto h-14 w-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="text-sm font-bold text-slate-700">No children linked</div>
                    <div class="mt-1 text-xs text-slate-500">Please contact the school administrator to link your children to this account.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
