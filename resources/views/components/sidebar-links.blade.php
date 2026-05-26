@php($user = auth()->user())
@php
    $baseClass = 'mb-1 group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition-all duration-200';
    $activeClass = 'bg-slate-100/80 text-slate-900 shadow-sm ring-1 ring-slate-200/50';
    $inactiveClass = 'text-slate-500 hover:bg-slate-50 hover:text-slate-900';
    
    $iconBase = 'h-5 w-5 flex-shrink-0 transition-colors duration-200';
    $iconActive = 'text-slate-800';
    $iconInactive = 'text-slate-400 group-hover:text-slate-500';

    // Dropdown Group Actives based on request routes
    $isRegistryActive = request()->routeIs('students.*') || request()->routeIs('teachers') || request()->routeIs('teachers.*') || request()->routeIs('parents.*');
    
    $isAcademicsActive = request()->routeIs('classes.*') || request()->routeIs('subjects.*') || request()->routeIs('results.entry') || request()->routeIs('results.broadsheet') || request()->routeIs('attendance');

    $tenantComponents = auth()->user()?->tenant?->activeMarketplaceComponents()->get() ?? collect();
    $sidebarPlugins = $tenantComponents->whereNotIn('slug', ['whatsapp-bot', 'homework', 'messages'])
        ->filter(fn($c) => $c->route_name && \Illuminate\Support\Facades\Route::has($c->route_name));

    $isAddonsActive = request()->routeIs('homework.*') || request()->routeIs('messages') || $sidebarPlugins->contains(fn($p) => request()->routeIs(explode('.', $p->route_name)[0] . '.*'));

    $isAdminActive = request()->routeIs('settings.*') || request()->routeIs('more-features') || request()->routeIs('superadmin.*');
@endphp

{{-- Root Link: Dashboard --}}
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

@if ($user?->role === 'parent')
    {{-- Simple layout for Parents (minimal options) --}}
    <a href="{{ route('students.index') }}" wire:navigate
        class="{{ request()->routeIs('students.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
        <svg class="{{ $iconBase }} {{ request()->routeIs('students.*') ? $iconActive : $iconInactive }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <span class="sidebar-text">My Children</span>
    </a>
@else
    {{-- Dropdowns for School Administrators & Teachers --}}
    
    {{-- Group 1: Registry & Users --}}
    @if ($user?->role === 'admin' || $user?->role === 'teacher')
        <div x-data="{ open: {{ $isRegistryActive ? 'true' : 'false' }} }" class="mb-2">
            <button @click="open = !open" 
                class="w-full flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 {{ $isRegistryActive ? 'bg-slate-50 text-slate-900 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-3">
                    <svg class="{{ $iconBase }} {{ $isRegistryActive ? 'text-indigo-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <span class="sidebar-text">Registry &amp; Users</span>
                </span>
                <svg :class="open ? 'rotate-180 text-slate-600' : 'text-slate-400'" class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open" x-collapse x-cloak class="pl-4 pr-1 mt-1 space-y-1 border-l-2 border-slate-100 ml-5 transition-all duration-300">
                <a href="{{ route('students.index') }}" wire:navigate
                    class="{{ request()->routeIs('students.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                    <span class="sidebar-text">Students</span>
                </a>
                @if ($user?->role === 'admin')
                    <a href="{{ route('teachers') }}" wire:navigate
                        class="{{ request()->routeIs('teachers') || request()->routeIs('teachers.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                        <span class="sidebar-text">Teachers</span>
                    </a>
                    <a href="{{ route('parents.index') }}" wire:navigate
                        class="{{ request()->routeIs('parents.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                        <span class="sidebar-text">Parents</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- Group 2: Academics & Scoring --}}
    @if ($user?->role === 'admin' || $user?->role === 'teacher')
        <div x-data="{ open: {{ $isAcademicsActive ? 'true' : 'false' }} }" class="mb-2">
            <button @click="open = !open" 
                class="w-full flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 {{ $isAcademicsActive ? 'bg-slate-50 text-slate-900 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-3">
                    <svg class="{{ $iconBase }} {{ $isAcademicsActive ? 'text-indigo-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
                    </svg>
                    <span class="sidebar-text">Academics</span>
                </span>
                <svg :class="open ? 'rotate-180 text-slate-600' : 'text-slate-400'" class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open" x-collapse x-cloak class="pl-4 pr-1 mt-1 space-y-1 border-l-2 border-slate-100 ml-5 transition-all duration-300">
                <a href="{{ route('classes.index') }}" wire:navigate
                    class="{{ request()->routeIs('classes.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                    <span class="sidebar-text">Classes</span>
                </a>
                <a href="{{ route('subjects.index') }}" wire:navigate
                    class="{{ request()->routeIs('subjects.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                    <span class="sidebar-text">Subjects</span>
                </a>
                <a href="{{ route('results.entry') }}" wire:navigate
                    class="{{ request()->routeIs('results.entry') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                    <span class="sidebar-text">Score Entry</span>
                </a>
                @if ($user?->role === 'admin')
                    <a href="{{ route('results.broadsheet') }}" wire:navigate
                        class="{{ request()->routeIs('results.broadsheet') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                        <span class="sidebar-text">Broadsheet</span>
                    </a>
                @endif
                <a href="{{ route('attendance') }}" wire:navigate
                    class="{{ request()->routeIs('attendance') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                    <span class="sidebar-text">Attendance</span>
                </a>
            </div>
        </div>
    @endif

    {{-- Group 3: Apps & Plugins --}}
    @php
        $hasHomework = auth()->user()?->tenant?->activeMarketplaceComponents()->where('slug', 'homework')->exists();
        $hasMessages = auth()->user()?->tenant?->activeMarketplaceComponents()->where('slug', 'messages')->exists();
    @endphp
    @if (($user?->role === 'admin' || $user?->role === 'teacher') && ($hasHomework || $hasMessages || $sidebarPlugins->isNotEmpty()))
        <div x-data="{ open: {{ $isAddonsActive ? 'true' : 'false' }} }" class="mb-2">
            <button @click="open = !open" 
                class="w-full flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 {{ $isAddonsActive ? 'bg-slate-50 text-slate-900 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-3">
                    <svg class="{{ $iconBase }} {{ $isAddonsActive ? 'text-indigo-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <span class="sidebar-text">Plugins &amp; Apps</span>
                </span>
                <svg :class="open ? 'rotate-180 text-slate-600' : 'text-slate-400'" class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open" x-collapse x-cloak class="pl-4 pr-1 mt-1 space-y-1 border-l-2 border-slate-100 ml-5 transition-all duration-300">
                @if($hasHomework)
                    <a href="{{ route('homework.index') }}" wire:navigate
                        class="{{ request()->routeIs('homework.*') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                        <span class="sidebar-text">Homework</span>
                    </a>
                @endif

                @if($hasMessages)
                    <a href="{{ route('messages') }}" wire:navigate
                        class="{{ request()->routeIs('messages') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                        <span class="sidebar-text">Messages</span>
                        <span class="ml-auto flex items-center">
                            <livewire:messages.unread-badge />
                        </span>
                    </a>
                @endif

                @foreach($sidebarPlugins as $component)
                    @php
                        $href = route($component->route_name);
                        $isActive = request()->routeIs(explode('.', $component->route_name)[0] . '.*');
                    @endphp
                    <a href="{{ $href }}" wire:navigate
                        class="{{ $isActive ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                        <span class="flex items-center gap-3">
                            @if(str_contains($component->icon, '<svg'))
                                <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }} sidebar-svg-container">
                                    {!! $component->icon !!}
                                </span>
                            @else
                                <span style="font-size:16px;">{{ $component->icon ?: '📦' }}</span>
                            @endif
                            <span class="sidebar-text">{{ $component->name }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Group 4: Administration & Setup --}}
    @if (in_array($user?->role, ['admin', 'teacher', 'bursar'], true))
        <div x-data="{ open: {{ $isAdminActive ? 'true' : 'false' }} }" class="mb-2">
            <button @click="open = !open" 
                class="w-full flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 {{ $isAdminActive ? 'bg-slate-50 text-slate-900 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-3">
                    <svg class="{{ $iconBase }} {{ $isAdminActive ? 'text-indigo-600' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    <span class="sidebar-text">Control Panel</span>
                </span>
                <svg :class="open ? 'rotate-180 text-slate-600' : 'text-slate-400'" class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open" x-collapse x-cloak class="pl-4 pr-1 mt-1 space-y-1 border-l-2 border-slate-100 ml-5 transition-all duration-300">
                @if ($user?->role === 'admin')
                    <a href="{{ route('settings.index') }}" wire:navigate
                        class="{{ request()->routeIs('settings.*') && !request()->routeIs('settings.subscription') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                        <span class="sidebar-text">Settings</span>
                    </a>
                    <a href="{{ route('settings.subscription') }}" wire:navigate
                        class="{{ request()->routeIs('settings.subscription') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                        <span class="sidebar-text">Billing</span>
                    </a>
                @endif

                <a href="{{ route('more-features') }}" wire:navigate
                    class="{{ request()->routeIs('more-features') ? $activeClass : $inactiveClass }} {{ $baseClass }}">
                    <span class="sidebar-text">More Features</span>
                </a>

                @if ($user?->is_super_admin)
                    <a href="{{ route('superadmin.dashboard') }}" wire:navigate
                        class="{{ request()->routeIs('superadmin.*') ? 'bg-slate-800 text-slate-100 shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }} mt-4 mb-1 group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition-all duration-200">
                        <span class="sidebar-text">Dev Area</span>
                    </a>
                @endif
            </div>
        </div>
    @endif
@endif

<style>
    [x-cloak] {
        display: none !important;
    }
    .sidebar-svg-container svg {
        width: 1.25rem !important; /* w-5 (20px) */
        height: 1.25rem !important; /* h-5 (20px) */
        max-width: 100% !important;
        max-height: 100% !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }
</style>
