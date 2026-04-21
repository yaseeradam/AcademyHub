@php
$user      = auth()->user();
$cbtLocked = false;
$navigate  = isset($mobile) && $mobile ? '' : 'wire:navigate';

// Helper: build a nav item
// active: bool, iconBg, iconColor, icon SVG path, label, href, badge slot
$navLink = function(string $href, string $label, string $iconBg, string $iconColor, string $iconPath, bool $active, string $badge = '') use ($navigate): string {
    $pill   = $active
        ? 'bg-violet-500 shadow-lg shadow-violet-200'
        : 'hover:bg-white hover:shadow-sm';
    $iconBox = $active ? 'bg-white/20' : $iconBg;
    $iconClr = $active ? 'text-white' : $iconColor;
    $textClr = $active ? 'text-white' : 'text-slate-700';
    $arrow   = $active
        ? '<div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20"><svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></div>'
        : ($badge ?: '');

    return <<<HTML
<a href="{$href}" {$navigate}
   class="flex items-center gap-3 rounded-2xl px-3 py-2.5 transition-all {$pill}">
    <div class="nav-icon-box {$iconBox}">
        <svg class="h-5 w-5 {$iconClr}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{$iconPath}</svg>
    </div>
    <span class="flex-1 text-sm font-bold {$textClr}">{$label}</span>
    {$arrow}
</a>
HTML;
};
@endphp

{{-- Dashboard --}}
{!! $navLink(route('dashboard'), 'Dashboard',
    'bg-indigo-100', 'text-indigo-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    request()->routeIs('dashboard')) !!}

{{-- Students / My Children --}}
@if($user?->role !== 'parent')
{!! $navLink(route('students.index'), 'Students',
    'bg-blue-100', 'text-blue-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
    request()->routeIs('students.*')) !!}
@else
{!! $navLink(route('students.index'), 'My Children',
    'bg-pink-100', 'text-pink-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
    request()->routeIs('students.*')) !!}
@endif

{{-- Admin-only: Teachers + Parents --}}
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

{{-- Admin + Teacher --}}
@if(in_array($user?->role, ['admin', 'teacher']))
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
@endif

@if($user?->role === 'admin')
{!! $navLink(route('results.broadsheet'), 'Broadsheet',
    'bg-emerald-100', 'text-emerald-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18"/><rect x="3" y="3" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>',
    request()->routeIs('results.broadsheet')) !!}
@endif

@if(in_array($user?->role, ['admin', 'teacher']))
{!! $navLink(route('attendance'), 'Attendance',
    'bg-teal-100', 'text-teal-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
    request()->routeIs('attendance')) !!}

{!! $navLink(route('homework.index'), 'Homework',
    'bg-purple-100', 'text-purple-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
    request()->routeIs('homework.*')) !!}

@php
$unreadBadge = '<livewire:messages.unread-badge />';
@endphp
{!! $navLink(route('messages'), 'Messages',
    'bg-sky-100', 'text-sky-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
    request()->routeIs('messages')) !!}

@php $cbtHref = $cbtLocked ? route('more-features') : route('cbt.index'); @endphp
{!! $navLink($cbtHref, 'CBT Exams',
    'bg-violet-100', 'text-violet-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
    !$cbtLocked && request()->routeIs('cbt.*')) !!}
@endif

{{-- Bursar: Billing --}}
@if($user?->role === 'bursar')
{!! $navLink(route('billing.index'), 'Billing',
    'bg-emerald-100', 'text-emerald-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
    request()->routeIs('billing.*')) !!}
@endif

{{-- More Features --}}
@if(in_array($user?->role, ['admin', 'teacher', 'bursar']))
{!! $navLink(route('more-features'), 'More Features',
    'bg-amber-100', 'text-amber-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
    request()->routeIs('more-features')) !!}
@endif

{{-- Admin: Settings + Billing --}}
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

{{-- Super admin --}}
@if($user?->is_super_admin)
{!! $navLink(route('superadmin.dashboard'), 'Dev Dashboard',
    'bg-slate-800', 'text-sky-400',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
    request()->routeIs('superadmin.*')) !!}
@endif

{{-- Profile --}}
{!! $navLink(route('profile'), 'My Profile',
    'bg-violet-100', 'text-violet-500',
    '<path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    request()->routeIs('profile')) !!}
