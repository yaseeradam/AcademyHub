<?php
$user      = auth()->user();
$cbtLocked = false;
$isMobile  = isset($mobile) && $mobile;
$navigate  = $isMobile ? '' : 'wire:navigate';

$navLink = function(string $href, string $label, string $iconBg, string $iconColor, string $iconPath, bool $active, string $badge = '') use ($navigate, $isMobile): string {
    $pill    = $active ? 'bg-violet-500 shadow-lg shadow-violet-200/60' : 'hover:bg-white hover:shadow-sm';
    $iconBox = $active ? 'bg-white/20' : $iconBg;
    $iconClr = $active ? 'text-white' : $iconColor;
    $textClr = $active ? 'text-white font-extrabold flex-1 text-sm leading-none whitespace-nowrap overflow-hidden' : 'text-slate-700 font-bold flex-1 text-sm leading-none whitespace-nowrap overflow-hidden';
    
    $arrowHtml = $active
        ? '<div class="ml-auto flex h-6 w-6 items-center justify-center rounded-full bg-white/20 flex-shrink-0"><svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></div>'
        : ($badge ?: '');
        
    $aDirectives = $isMobile ? 'class="flex items-center gap-3 rounded-2xl px-3 py-2.5 transition-all duration-300 '.$pill.'"' : 'class="flex items-center gap-3 rounded-2xl py-2.5 transition-all duration-300 '.$pill.'" x-bind:class="sidebarCollapsed ? \'justify-center px-1\' : \'px-3\'"';
    $textDirectives = $isMobile ? '' : 'x-show="!sidebarCollapsed" x-transition:enter="transition-opacity ease-out duration-300 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"';
    $arrowDirectives = $isMobile ? '' : 'x-show="!sidebarCollapsed" x-transition.opacity';

    return <<<HTML
<a href="{$href}" {$navigate} title="{$label}"
   {$aDirectives}>
    <div class="nav-icon-box {$iconBox} rounded-xl transition-all duration-300 flex-shrink-0">
        <svg class="h-5 w-5 {$iconClr}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{$iconPath}</svg>
    </div>
    <span class="{$textClr}" {$textDirectives}>{$label}</span>
    <div {$arrowDirectives}>
        {$arrowHtml}
    </div>
</a>
HTML;
};
?>

{{-- Dashboard is always a root link --}}
{!! $navLink(route('dashboard'), 'Dashboard',
    'bg-indigo-100', 'text-indigo-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    request()->routeIs('dashboard')) !!}

@if($user?->role === 'parent')
    {{-- Simple layout for Parents (minimal options, no dropdowns needed) --}}
    {!! $navLink(route('students.index'), 'My Children',
        'bg-pink-100', 'text-pink-500',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
        request()->routeIs('students.*')) !!}

    @if($user?->tenant?->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists())
        {!! $navLink(route('parent.pay'), 'Pay Fees',
            'bg-emerald-100', 'text-emerald-500',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
            request()->routeIs('parent.pay')) !!}
    @endif

    {!! $navLink(route('profile'), 'My Profile',
        'bg-violet-100', 'text-violet-500',
        '<path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        request()->routeIs('profile')) !!}
@else
    {{-- Dropdowns for Administrators, Teachers, and Bursars --}}
    
    {{-- Dropdown 1: Registry & Users --}}
    @php
        $isRegistryActive = request()->routeIs('students.*') || request()->routeIs('teachers') || request()->routeIs('teachers.*') || request()->routeIs('parents.*');
        $hasRegistryAccess = in_array($user?->role, ['admin', 'teacher', 'bursar'], true);
    @endphp
    @if($hasRegistryAccess)
        <div x-data="{ open: {{ $isRegistryActive ? 'true' : 'false' }} }" class="mb-1" x-cloak>
            <button @click="open = !open" 
                class="w-full flex items-center justify-between rounded-2xl py-2.5 transition-all duration-300 text-slate-700 hover:bg-white hover:shadow-sm"
                x-bind:class="sidebarCollapsed ? 'justify-center px-1' : 'px-3'">
                <span class="flex items-center gap-3">
                    <div class="nav-icon-box bg-blue-100 rounded-xl flex-shrink-0 flex items-center justify-center">
                        <svg class="h-5 w-5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-slate-700 font-bold text-sm leading-none" x-show="!sidebarCollapsed" x-transition.opacity>Registry &amp; Users</span>
                </span>
                <svg x-show="!sidebarCollapsed" x-transition.opacity :class="open ? 'rotate-180 text-slate-600' : 'text-slate-400'" class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open || sidebarCollapsed" x-collapse class="mt-1 space-y-0.5" x-bind:class="sidebarCollapsed ? '' : 'pl-4 border-l border-slate-200/60 ml-4.5'">
                {!! $navLink(route('students.index'), 'Students',
                    'bg-blue-100', 'text-blue-500',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
                    request()->routeIs('students.*')) !!}
                
                @if($user?->role === 'admin')
                    {!! $navLink(route('teachers'), 'Teachers',
                        'bg-orange-100', 'text-orange-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                        request()->routeIs('teachers') || request()->routeIs('teachers.*')) !!}

                    {!! $navLink(route('parents.index'), 'Parents',
                        'bg-pink-100', 'text-pink-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 22V12h6v10"/>',
                        request()->routeIs('parents.*')) !!}
                @endif
            </div>
        </div>
    @endif

    {{-- Dropdown 2: Academics Portal --}}
    @php
        $isAcademicsActive = request()->routeIs('classes.*') || request()->routeIs('subjects.*') || request()->routeIs('results.entry') || request()->routeIs('results.broadsheet') || request()->routeIs('attendance');
        $hasAcademicsAccess = in_array($user?->role, ['admin', 'teacher'], true);
    @endphp
    @if($hasAcademicsAccess)
        <div x-data="{ open: {{ $isAcademicsActive ? 'true' : 'false' }} }" class="mb-1" x-cloak>
            <button @click="open = !open" 
                class="w-full flex items-center justify-between rounded-2xl py-2.5 transition-all duration-300 text-slate-700 hover:bg-white hover:shadow-sm"
                x-bind:class="sidebarCollapsed ? 'justify-center px-1' : 'px-3'">
                <span class="flex items-center gap-3">
                    <div class="nav-icon-box bg-slate-100 rounded-xl flex-shrink-0 flex items-center justify-center">
                        <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="text-slate-700 font-bold text-sm leading-none" x-show="!sidebarCollapsed" x-transition.opacity>Academics</span>
                </span>
                <svg x-show="!sidebarCollapsed" x-transition.opacity :class="open ? 'rotate-180 text-slate-600' : 'text-slate-400'" class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open || sidebarCollapsed" x-collapse class="mt-1 space-y-0.5" x-bind:class="sidebarCollapsed ? '' : 'pl-4 border-l border-slate-200/60 ml-4.5'">
                {!! $navLink(route('classes.index'), 'Classes',
                    'bg-slate-100', 'text-slate-500',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
                    request()->routeIs('classes.*')) !!}

                {!! $navLink(route('subjects.index'), 'Subjects',
                    'bg-indigo-100', 'text-indigo-500',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
                    request()->routeIs('subjects.*')) !!}

                {!! $navLink(route('results.entry'), 'Score Entry',
                    'bg-green-100', 'text-green-500',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
                    request()->routeIs('results.entry')) !!}

                @if($user?->role === 'admin')
                    {!! $navLink(route('results.broadsheet'), 'Broadsheet',
                        'bg-emerald-100', 'text-emerald-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18"/><rect x="3" y="3" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>',
                        request()->routeIs('results.broadsheet')) !!}
                @endif

                @if($user?->role === 'admin' || ($user?->role === 'teacher' && $user?->is_class_teacher))
                    {!! $navLink(route('attendance'), 'Attendance',
                        'bg-teal-100', 'text-teal-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
                        request()->routeIs('attendance')) !!}
                @endif
            </div>
        </div>
    @endif

    {{-- Dropdown 3: Plugins & Apps --}}
    @php
        $hasHomework = auth()->user()?->tenant?->activeMarketplaceComponents()->where('slug', 'homework')->exists();
        $hasMessages = auth()->user()?->tenant?->activeMarketplaceComponents()->where('slug', 'messages')->exists();
        $tenantComponents = auth()->user()?->tenant?->activeMarketplaceComponents()->get() ?? collect();
        $sidebarPlugins = $tenantComponents->whereNotIn('slug', ['whatsapp-bot', 'homework', 'messages', 'payment-gateway']);
        
        $isAddonsActive = request()->routeIs('homework.*') || request()->routeIs('messages') || $sidebarPlugins->contains(fn($p) => (Route::has($p->route_name) && request()->routeIs(explode('.', $p->route_name)[0] . '.*')) || (Route::has($p->slug . '.index') && request()->routeIs($p->slug . '.*')));
        $hasAddonsAccess = in_array($user?->role, ['admin', 'teacher'], true) && ($hasHomework || $hasMessages || $sidebarPlugins->isNotEmpty());
    @endphp
    @if($hasAddonsAccess)
        <div x-data="{ open: {{ $isAddonsActive ? 'true' : 'false' }} }" class="mb-1" x-cloak>
            <button @click="open = !open" 
                class="w-full flex items-center justify-between rounded-2xl py-2.5 transition-all duration-300 text-slate-700 hover:bg-white hover:shadow-sm"
                x-bind:class="sidebarCollapsed ? 'justify-center px-1' : 'px-3'">
                <span class="flex items-center gap-3">
                    <div class="nav-icon-box bg-purple-100 rounded-xl flex-shrink-0 flex items-center justify-center">
                        <svg class="h-5 w-5 text-purple-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <span class="text-slate-700 font-bold text-sm leading-none" x-show="!sidebarCollapsed" x-transition.opacity>Plugins &amp; Apps</span>
                </span>
                <svg x-show="!sidebarCollapsed" x-transition.opacity :class="open ? 'rotate-180 text-slate-600' : 'text-slate-400'" class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open || sidebarCollapsed" x-collapse class="mt-1 space-y-0.5" x-bind:class="sidebarCollapsed ? '' : 'pl-4 border-l border-slate-200/60 ml-4.5'">
                @if($hasHomework)
                    {!! $navLink(route('homework.index'), 'Homework',
                        'bg-purple-100', 'text-purple-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
                        request()->routeIs('homework.*')) !!}
                @endif

                @if($hasMessages)
                    {!! $navLink(route('messages'), 'Messages',
                        'bg-sky-100', 'text-sky-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                        request()->routeIs('messages')) !!}
                @endif

                @foreach($sidebarPlugins as $component)
                    @php
                        $routeExists = Route::has($component->route_name) || Route::has($component->slug . '.index');
                        $href       = Route::has($component->route_name) ? route($component->route_name) : (Route::has($component->slug . '.index') ? route($component->slug . '.index') : '#');
                        $isActive   = (Route::has($component->route_name) && request()->routeIs(explode('.', $component->route_name)[0] . '.*')) || (Route::has($component->slug . '.index') && request()->routeIs($component->slug . '.*'));

                        $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4"/>';
                        if ($component->slug === 'cbt') {
                            $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>';
                        } elseif ($component->slug === 'aptitude-test') {
                            $iconPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>';
                        }
                    @endphp

                    {!! $navLink($href, $component->name,
                        'bg-violet-100', 'text-violet-500',
                        $iconPath,
                        $isActive) !!}
                @endforeach
            </div>
        </div>
    @endif

    {{-- Dropdown 4: System Administration & Finance --}}
    @php
        $isSystemActive = request()->routeIs('settings.index') || request()->routeIs('settings.subscription') || request()->routeIs('more-features') || request()->routeIs('billing.*') || request()->routeIs('profile');
        $hasSystemAccess = in_array($user?->role, ['admin', 'teacher', 'bursar'], true);
    @endphp
    @if($hasSystemAccess)
        <div x-data="{ open: {{ $isSystemActive ? 'true' : 'false' }} }" class="mb-1" x-cloak>
            <button @click="open = !open" 
                class="w-full flex items-center justify-between rounded-2xl py-2.5 transition-all duration-300 text-slate-700 hover:bg-white hover:shadow-sm"
                x-bind:class="sidebarCollapsed ? 'justify-center px-1' : 'px-3'">
                <span class="flex items-center gap-3">
                    <div class="nav-icon-box bg-gray-100 rounded-xl flex-shrink-0 flex items-center justify-center">
                        <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                    <span class="text-slate-700 font-bold text-sm leading-none" x-show="!sidebarCollapsed" x-transition.opacity>Control Panel</span>
                </span>
                <svg x-show="!sidebarCollapsed" x-transition.opacity :class="open ? 'rotate-180 text-slate-600' : 'text-slate-400'" class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open || sidebarCollapsed" x-collapse class="mt-1 space-y-0.5" x-bind:class="sidebarCollapsed ? '' : 'pl-4 border-l border-slate-200/60 ml-4.5'">
                @if($user?->role === 'bursar')
                    {!! $navLink(route('billing.index'), 'Billing',
                        'bg-emerald-100', 'text-emerald-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
                        request()->routeIs('billing.*')) !!}
                @endif

                @if(($user?->role === 'bursar' || $user?->role === 'admin') && $user?->tenant?->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists())
                    {!! $navLink(route('payment-gateway.index'), 'Payment Gateway',
                        'bg-purple-100', 'text-purple-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.952 11.952 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                        request()->routeIs('payment-gateway.index')) !!}
                @endif
                
                {!! $navLink(route('more-features'), 'More Features',
                    'bg-amber-100', 'text-amber-500',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                    request()->routeIs('more-features')) !!}

                @if($user?->role === 'admin')
                    {!! $navLink(route('settings.index'), 'Settings',
                        'bg-gray-100', 'text-gray-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                        request()->routeIs('settings.*') && !request()->routeIs('settings.subscription')) !!}

                    {!! $navLink(route('settings.subscription'), 'Billing',
                        'bg-green-100', 'text-green-500',
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
                        request()->routeIs('settings.subscription')) !!}
                @endif

                {!! $navLink(route('profile'), 'My Profile',
                    'bg-violet-100', 'text-violet-500',
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    request()->routeIs('profile')) !!}
            </div>
        </div>
    @endif
@endif

<style>
    [x-cloak] {
        display: none !important;
    }
    .ml-4\.5 {
        margin-left: 1.125rem/* 18px */;
    }
</style>
