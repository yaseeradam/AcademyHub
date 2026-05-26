@php
$isMobile  = isset($mobile) && $mobile;
$navigate  = $isMobile ? '' : 'wire:navigate';

$navLink = function(string $href, string $label, string $iconBg, string $iconColor, string $iconPath, bool $active) use ($navigate, $isMobile): string {
    $pill    = $active ? 'bg-green-600 shadow-lg shadow-green-200/60 text-white font-extrabold' : 'hover:bg-white hover:shadow-sm text-slate-700 font-bold';
    $iconBox = $active ? 'bg-white/20' : $iconBg;
    $iconClr = $active ? 'text-white' : $iconColor;
    $textClr = $active ? 'text-white font-extrabold flex-1 text-xs leading-none whitespace-nowrap overflow-hidden' : 'text-slate-700 font-bold flex-1 text-xs leading-none whitespace-nowrap overflow-hidden';
    
    $arrowHtml = $active
        ? '<div class="ml-auto flex h-6 w-6 items-center justify-center rounded-full bg-white/20 flex-shrink-0"><svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></div>'
        : '';
        
    $aDirectives = $isMobile 
        ? 'class="flex items-center gap-3 rounded-2xl px-3 py-2.5 transition-all duration-300 '.$pill.'"' 
        : 'class="flex items-center gap-3 rounded-2xl py-2.5 transition-all duration-300 '.$pill.'" x-bind:class="sidebarCollapsed ? \'justify-center px-1\' : \'px-3\'"';
        
    $textDirectives = $isMobile ? '' : 'x-show="!sidebarCollapsed" x-transition:enter="transition-opacity ease-out duration-300 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"';
    $arrowDirectives = $isMobile ? '' : 'x-show="!sidebarCollapsed" x-transition.opacity';

    return <<<HTML
<a href="{$href}" {$navigate} title="{$label}" {$aDirectives}>
    <div class="nav-icon-box {$iconBox} rounded-xl transition-all duration-300 flex-shrink-0 flex items-center justify-center">
        <svg class="h-4.5 w-4.5 {$iconClr}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{$iconPath}</svg>
    </div>
    <span class="{$textClr}" {$textDirectives}>{$label}</span>
    <div {$arrowDirectives}>
        {$arrowHtml}
    </div>
</a>
HTML;
};
@endphp

{{-- Branding --}}
<div class="mx-3 mt-4 mb-2 rounded-2xl bg-white shadow-sm transition-all duration-300" x-bind:class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'p-2 mx-2' : 'p-4'">
    <div class="flex items-center" x-bind:class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'justify-center' : 'gap-3'">
        <div class="flex flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-green-50 ring-2 ring-green-100 transition-all duration-300" x-bind:class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'h-10 w-10' : 'h-12 w-12'">
            @if($schoolLogo)
                <img src="{{ asset('uploads/'.str_replace('\\','/',$schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-1"/>
            @else
                <svg class="h-5 w-5 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17a2 2 0 01-1.1 1.79l-7.4 3.7a2 2 0 01-1.8 0l-7.4-3.7A2 2 0 012 17V9"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V9"/>
                </svg>
            @endif
        </div>
        <div class="min-w-0 text-left" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition.opacity>
            <div class="truncate text-sm font-extrabold leading-tight text-slate-900">{{ $schoolName }}</div>
            <div class="mt-0.5 text-[11px] font-semibold text-green-600">Student Portal</div>
        </div>
    </div>
</div>

<div class="px-5 pb-1 pt-3 text-left" x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition.opacity>
    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Student Menu</span>
</div>

{{-- Navigation Links --}}
<nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-3 space-y-0.5 min-h-0 mt-2">
    {!! $navLink(route('student.dashboard'), 'Dashboard',
        'bg-indigo-50', 'text-indigo-500',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        request()->routeIs('student.dashboard')) !!}

    @if($isELearningInstalled)
        {!! $navLink(route('student.e-learning'), 'E-Learning',
            'bg-sky-50', 'text-sky-500',
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
            request()->routeIs('student.e-learning')) !!}
    @endif

    @if($isHomeworkInstalled)
        {!! $navLink(route('student.homework'), 'Homework',
            'bg-violet-50', 'text-violet-500',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
            request()->routeIs('student.homework')) !!}
    @endif

    {!! $navLink(route('student.exams'), 'Exams',
        'bg-rose-50', 'text-rose-500',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        request()->routeIs('student.exams')) !!}

    {!! $navLink(route('student.results'), 'Results',
        'bg-indigo-50', 'text-indigo-500',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        request()->routeIs('student.results') || request()->routeIs('student.report-card')) !!}

    {!! $navLink(route('student.attendance'), 'Attendance',
        'bg-teal-50', 'text-teal-500',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        request()->routeIs('student.attendance')) !!}

    {!! $navLink(route('student.performance'), 'Performance',
        'bg-amber-50', 'text-amber-500',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
        request()->routeIs('student.performance')) !!}

    {!! $navLink(route('student.notifications'), 'Notifications',
        'bg-yellow-50', 'text-yellow-600',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
        request()->routeIs('student.notifications')) !!}

    {!! $navLink(route('student.profile'), 'My Profile',
        'bg-slate-100', 'text-slate-500',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        request()->routeIs('student.profile')) !!}
</nav>

{{-- Student info badge + logout (unified like admin) --}}
<div class="p-3 transition-all duration-300" x-bind:class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'px-2' : 'p-3'">
    <form method="POST" action="{{ route('student.logout') }}" id="logoutForm">
        @csrf
        <button type="submit" title="Logout"
                class="w-full flex items-center rounded-2xl bg-slate-800 hover:bg-slate-900 transition-all duration-300 shadow-sm text-left" 
                x-bind:class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'p-2 justify-center' : 'px-4 py-3.5 gap-3'">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-red-500 shadow-md transition-all duration-300" 
                 x-bind:class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'h-10 w-10' : 'h-9 w-9'">
                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div x-show="!sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }}" x-transition.opacity class="text-left flex-1 whitespace-nowrap overflow-hidden">
                <div class="text-xs font-extrabold text-white truncate">{{ $studentName }}</div>
                <div class="text-[10px] font-semibold text-red-400">Logout 👋</div>
            </div>
        </button>
    </form>
</div>
