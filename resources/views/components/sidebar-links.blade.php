@php($user = auth()->user())
@php
    $baseClass = 'mb-1 group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition-all duration-200';
    $activeClass = 'bg-slate-100/80 text-slate-900 shadow-sm ring-1 ring-slate-200/50';
    $inactiveClass = 'text-slate-500 hover:bg-slate-50 hover:text-slate-900';
    
    $iconBase = 'h-5 w-5 flex-shrink-0 transition-colors duration-200';
    $iconActive = 'text-slate-800';
    $iconInactive = 'text-slate-400 group-hover:text-slate-500';
@endphp

<a href="{{ route('dashboard') }}" wire:navigate
    class="{{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
    <svg class="{{ $iconBase }} {{ request()->routeIs('dashboard') ? $iconActive : $iconInactive }}"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="7" height="7" rx="1.5" />
        <rect x="14" y="3" width="7" height="7" rx="1.5" />
        <rect x="14" y="14" width="7" height="7" rx="1.5" />
        <rect x="3" y="14" width="7" height="7" rx="1.5" />
    </svg>
    <span class="sidebar-text">Dashboard</span>
</a>

@if ($user?->role !== 'parent')
    <a href="{{ route('students.index') }}" wire:navigate
        class="{{ request()->routeIs('students.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('students.*') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2l9 5-9 5-9-5 9-5z"/>
            <path d="M12 22l9-5-9-5-9 5 9 5z"/>
            <path d="M12 12l9-5"/>
        </svg>
        <span class="sidebar-text">Students</span>
    </a>
@endif

@if ($user?->role === 'parent')
    <a href="{{ route('students.index') }}" wire:navigate
        class="{{ request()->routeIs('students.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('students.*') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <span class="sidebar-text">My Children</span>
    </a>
@endif

@if ($user?->role === 'admin')
    <a href="{{ route('teachers') }}" wire:navigate
        class="{{ request()->routeIs('teachers') || request()->routeIs('teachers.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('teachers') || request()->routeIs('teachers.*') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="3" width="20" height="13" rx="2"/>
            <polyline points="8 21 12 17 16 21"/>
            <line x1="7" y1="9" x2="17" y2="9"/>
            <line x1="7" y1="13" x2="12" y2="13"/>
        </svg>
        <span class="sidebar-text">Teachers</span>
    </a>

    <a href="{{ route('parents.index') }}" wire:navigate
        class="{{ request()->routeIs('parents.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('parents.*') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span class="sidebar-text">Parents</span>
    </a>

    <a href="{{ route('results.broadsheet') }}" wire:navigate
        class="{{ request()->routeIs('results.broadsheet') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('results.broadsheet') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>
        </svg>
        <span class="sidebar-text">Broadsheet</span>
    </a>
@endif

@if ($user?->role === 'admin' || $user?->role === 'teacher')
    <a href="{{ route('classes.index') }}" wire:navigate
        class="{{ request()->routeIs('classes.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('classes.*') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 21h18M5 21V7l7-4 7 4v14M10 21v-6h4v6"/>
            <path d="M10 11h.01M14 11h.01M10 15h.01M14 15h.01"/>
        </svg>
        <span class="sidebar-text">Classes</span>
    </a>

    <a href="{{ route('subjects.index') }}" wire:navigate
        class="{{ request()->routeIs('subjects.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('subjects.*') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
        </svg>
        <span class="sidebar-text">Subjects</span>
    </a>

    <a href="{{ route('results.entry') }}" wire:navigate
        class="{{ request()->routeIs('results.entry') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('results.entry') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
        <span class="sidebar-text">Score Entry</span>
    </a>

    <a href="{{ route('attendance') }}" wire:navigate
        class="{{ request()->routeIs('attendance') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('attendance') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <polyline points="16 11 18 13 22 9" />
        </svg>
        <span class="sidebar-text">Attendance</span>
    </a>

    <a href="{{ route('homework.index') }}" wire:navigate
        class="{{ request()->routeIs('homework.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('homework.*') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
            <rect x="8" y="2" width="8" height="4" rx="1"/>
            <line x1="16" y1="11" x2="8" y2="11"/>
            <line x1="16" y1="15" x2="12" y2="15"/>
        </svg>
        <span class="sidebar-text">Homework</span>
    </a>

    <a href="{{ route('messages') }}" wire:navigate
        class="{{ request()->routeIs('messages') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('messages') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
        </svg>
        <span class="sidebar-text">Messages</span>
        <span class="ml-auto flex items-center">
            <livewire:messages.unread-badge />
        </span>
    </a>

    @php
        $cbtLocked = false; // Add this definition properly if missing, or inherit it
        $cbtHref = $cbtLocked ? route('more-features') : route('cbt.index');
        $cbtIsActive = !$cbtLocked && request()->routeIs('cbt.*');
    @endphp
    <a href="{{ $cbtHref }}" wire:navigate
        class="{{ $cbtIsActive ? $activeClass : $inactiveClass }} {{ $cbtLocked ? 'opacity-60' : '' }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ $cbtIsActive ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="12" rx="2" ry="2" />
            <path d="M8 20h8" />
            <path d="M10 10l2 2 4-4" />
        </svg>
        <span class="sidebar-text">CBT</span>
        @if ($cbtLocked)
            <span class="ml-auto rounded-full bg-slate-100 px-2 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500">Locked</span>
        @endif
    </a>
@endif

@if (in_array($user?->role, ['admin', 'teacher', 'bursar'], true))
    <a href="{{ route('more-features') }}" wire:navigate
        class="{{ request()->routeIs('more-features') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('more-features') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
        </svg>
        <span class="sidebar-text">More</span>
    </a>
@endif

@if ($user?->role === 'admin')
    <a href="{{ route('settings.index') }}" wire:navigate
        class="{{ request()->routeIs('settings.*') && !request()->routeIs('settings.subscription') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('settings.*') && !request()->routeIs('settings.subscription') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <span class="sidebar-text">Settings</span>
    </a>

    <a href="{{ route('settings.subscription') }}" wire:navigate
        class="{{ request()->routeIs('settings.subscription') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('settings.subscription') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2" />
            <line x1="2" y1="10" x2="22" y2="10" />
        </svg>
        <span class="sidebar-text">Billing</span>
    </a>
@endif

@if ($user?->is_super_admin)
    <a href="{{ route('superadmin.dashboard') }}" wire:navigate
        class="{{ request()->routeIs('superadmin.*') ? 'bg-slate-800 text-slate-100 shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }} mt-4 mb-1 group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition-all duration-200">
        <svg class="{{ $iconBase }} {{ request()->routeIs('superadmin.*') ? 'text-slate-100' : 'text-slate-400 group-hover:text-slate-500' }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span class="sidebar-text">Dev Area</span>
    </a>
@endif
