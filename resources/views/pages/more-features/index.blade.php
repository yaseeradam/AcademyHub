	@php
	    $user = auth()->user();
	    $features = [
	        ['route' => 'examination', 'title' => 'Examinations', 'desc' => 'Manage exam schedules and settings', 'icon' => 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11', 'color' => 'teal', 'roles' => ['admin']],
	        ['route' => 'marketplace', 'title' => 'Marketplace', 'desc' => 'Unlock premium modules (CBT, Savings/Loan)', 'icon' => 'M6 2l1.5 4h9L18 2M3 6h18l-1 16H4L3 6z', 'color' => 'purple', 'roles' => ['admin']],
	        ['route' => 'users.index', 'title' => 'User Management', 'desc' => 'Create accounts, assign roles, activate/deactivate', 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'color' => 'indigo', 'roles' => ['admin']],
	        ['route' => 'imports.index', 'title' => 'Imports', 'desc' => 'Bulk import students, teachers, and subjects (CSV)', 'icon' => 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3', 'color' => 'cyan', 'roles' => ['admin']],

	        ['route' => 'analytics.dashboard', 'title' => 'Analytics Dashboard', 'desc' => 'School-wide performance and attendance analytics', 'icon' => 'M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3h8v6h-8V3zM3 21h8v-6H3v6z', 'color' => 'blue', 'roles' => ['admin', 'teacher']],
	        ['route' => 'analytics.student-performance', 'title' => 'Student Performance Analytics', 'desc' => 'Track individual student progress and identify improvement areas', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'emerald', 'roles' => ['admin', 'teacher']],

	        ['route' => 'announcements', 'title' => 'Announcements', 'desc' => 'School-wide announcements', 'icon' => 'M3 11l18-5v12L3 13v-2zm4 10a2 2 0 0 1-2-2v-2l4 1v3a2 2 0 0 1-2 2z', 'color' => 'orange', 'roles' => ['admin', 'teacher', 'bursar']],
	        ['route' => 'notifications', 'title' => 'Notifications', 'desc' => 'In-app notifications', 'icon' => 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7zm-6 13a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2z', 'color' => 'slate', 'roles' => ['admin', 'teacher', 'bursar']],

	        ['route' => 'promotions', 'title' => 'Promote Students', 'desc' => 'Move students between classes/grades', 'icon' => 'M12 2l4 4h-3v7h-2V6H8l4-4zm-7 9h2v9h10v-9h2v11H5V11z', 'color' => 'emerald', 'roles' => ['admin']],
	        ['route' => 'promotions', 'title' => 'Bulk Promotion', 'desc' => 'Promote multiple students at once', 'icon' => 'M16 7h5v5h-2V9h-3V7zM3 12h10v2H3v-2zm0 5h10v2H3v-2zM3 7h10v2H3V7z', 'color' => 'indigo', 'roles' => ['admin']],
	        ['route' => 'events', 'title' => 'Event Scheduling', 'desc' => 'Plan and manage school events', 'icon' => 'M7 2v2H5a2 2 0 0 0-2 2v2h18V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm14 8H3v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V10z', 'color' => 'purple', 'roles' => ['admin']],
	        ['route' => 'academic-sessions', 'title' => 'Academic Year Management', 'desc' => 'Sessions and active academic year', 'icon' => 'M4 19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7H4v12zm16-14V3h-2v2H6V3H4v2a2 2 0 0 0-2 2v2h20V7a2 2 0 0 0-2-2z', 'color' => 'cyan', 'roles' => ['admin']],
	        ['route' => 'timetable', 'title' => 'Timetable', 'desc' => 'Class scheduling and time slots', 'icon' => 'M7 11h5v5H7v-5zm7 0h5v5h-5v-5zM7 4v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-2V4h-2v2H9V4H7z', 'color' => 'blue', 'roles' => ['admin']],
	        ['route' => 'certificates', 'title' => 'Certificates', 'desc' => 'Certificate generation and printing', 'icon' => 'M6 2h9l3 3v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm8 0v4h4', 'color' => 'orange', 'roles' => ['admin']],
	    ];

        // Fetch active marketplace components / plugins dynamically
        $tenantComponents = $user?->tenant?->activeMarketplaceComponents()->get() ?? collect();
        foreach ($tenantComponents as $component) {
            // Avoid double displaying if already statically listed or handled elsewhere
            if (in_array($component->slug, ['homework', 'messages'], true)) {
                continue;
            }

            $routeExists = Route::has($component->slug . '.index');
            if ($routeExists) {
                // Predefined SVGs for key plugins, fallback to a solid package icon
                $svgIcon = 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4'; // Default box icon
                $iconColor = 'indigo';

                if ($component->slug === 'cbt') {
                    $svgIcon = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'; // Clipboard check icon
                    $iconColor = 'purple';
                } elseif ($component->slug === 'e-learning') {
                    $svgIcon = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'; // Open book icon
                    $iconColor = 'emerald';
                } elseif ($component->slug === 'whatsapp-bot') {
                    $svgIcon = 'M3 21l1.9-5.7A8.5 8.5 0 113.4 18z'; // Chat bubble icon
                    $iconColor = 'green';
                }

                $features[] = [
                    'route' => $component->slug . '.index',
                    'title' => $component->name,
                    'desc' => $component->short_description ?: $component->description,
                    'icon' => $svgIcon,
                    'color' => $iconColor,
                    'roles' => ['admin', 'teacher']
                ];
            }
        }
	    
	    $colors = [
	        'green' => ['bg' => 'from-green-50 to-emerald-50/60', 'icon' => 'from-green-500 to-emerald-600', 'shadow' => 'shadow-green-500/30'],
	        'blue' => ['bg' => 'from-blue-50 to-cyan-50/60', 'icon' => 'from-blue-500 to-cyan-600', 'shadow' => 'shadow-blue-500/30'],
	        'emerald' => ['bg' => 'from-emerald-50 to-teal-50/60', 'icon' => 'from-emerald-500 to-teal-600', 'shadow' => 'shadow-emerald-500/30'],
	        'purple' => ['bg' => 'from-purple-50 to-pink-50/60', 'icon' => 'from-purple-500 to-pink-600', 'shadow' => 'shadow-purple-500/30'],
	        'orange' => ['bg' => 'from-orange-50 to-amber-50/60', 'icon' => 'from-orange-500 to-amber-600', 'shadow' => 'shadow-orange-500/30'],
	        'indigo' => ['bg' => 'from-indigo-50 to-blue-50/60', 'icon' => 'from-indigo-500 to-blue-600', 'shadow' => 'shadow-indigo-500/30'],
	        'cyan' => ['bg' => 'from-cyan-50 to-sky-50/60', 'icon' => 'from-cyan-500 to-sky-600', 'shadow' => 'shadow-cyan-500/30'],
	        'slate' => ['bg' => 'from-slate-50 to-gray-50/60', 'icon' => 'from-slate-600 to-gray-700', 'shadow' => 'shadow-slate-500/30'],
	        'teal' => ['bg' => 'from-teal-50 to-cyan-50/60', 'icon' => 'from-teal-500 to-cyan-600', 'shadow' => 'shadow-teal-500/30'],
    ];
@endphp

@extends('layouts.app')

@section('content')
	    <div class="space-y-6">
	        <x-page-header title="More Features" subtitle="All modules and utilities grouped in one place." accent="more" />

            @if ($errors->has('premium'))
                <div class="card-padded border border-orange-200 bg-orange-50/60 text-sm text-orange-900">
                    {{ $errors->first('premium') }}
                </div>
            @endif

	        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
	            @foreach($features as $feature)
	                @if(in_array($user?->role, $feature['roles']))
	                    @php $scheme = $colors[$feature['color']]; @endphp
	                    @php
	                        $isAvailable = isset($feature['route']) && $feature['route'];
	                        $isComingSoon = (bool) ($feature['coming_soon'] ?? false);
	                        $cardClass = $isAvailable
	                            ? 'group rounded-3xl border border-gray-100 bg-gradient-to-br '.$scheme['bg'].' p-6 shadow-lg transition-all hover:shadow-2xl hover:-translate-y-1'
	                            : 'rounded-3xl border border-gray-100 bg-gradient-to-br '.$scheme['bg'].' p-6 shadow-lg opacity-70 cursor-not-allowed';
	                    @endphp

	                    @if($isAvailable)
	                        <a href="{{ route($feature['route']) }}" class="{{ $cardClass }}">
	                            <div class="flex items-start gap-4">
	                                <div class="icon-3d grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br {{ $scheme['icon'] }} text-white shadow-xl {{ $scheme['shadow'] }} transition-transform group-hover:scale-110 group-hover:rotate-6">
	                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
	                                        <path d="{{ $feature['icon'] }}"/>
	                                    </svg>
	                                </div>
	                                <div class="flex-1 min-w-0">
	                                    <div class="text-lg font-black text-gray-900">{{ $feature['title'] }}</div>
	                                    <div class="mt-1.5 text-sm font-semibold text-gray-600">{{ $feature['desc'] }}</div>
	                                </div>
	                            </div>
	                        </a>
	                    @else
	                        <div class="{{ $cardClass }}" aria-disabled="true">
	                            <div class="flex items-start gap-4">
	                                <div class="icon-3d grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br {{ $scheme['icon'] }} text-white shadow-xl {{ $scheme['shadow'] }}">
	                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
	                                        <path d="{{ $feature['icon'] }}"/>
	                                    </svg>
	                                </div>
	                                <div class="flex-1 min-w-0">
	                                    <div class="flex items-center gap-2">
	                                        <div class="text-lg font-black text-gray-900">{{ $feature['title'] }}</div>
	                                        @if($isComingSoon)
	                                            <span class="rounded-full bg-white/70 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-700 ring-1 ring-white/60">Coming soon</span>
	                                        @endif
	                                    </div>
	                                    <div class="mt-1.5 text-sm font-semibold text-gray-600">{{ $feature['desc'] }}</div>
	                                </div>
	                            </div>
	                        </div>
	                    @endif
	                @endif
	            @endforeach
	        </div>
	    </div>
	@endsection
