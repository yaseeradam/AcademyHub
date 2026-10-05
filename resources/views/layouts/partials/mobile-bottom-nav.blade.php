@php
    $user = auth()->user();
    $role = $user?->role ?? 'user';
    $tenant = $user?->tenant;
    
    // Role-specific accent colors
    $accentColor = match($role) {
        'admin'      => 'text-violet-600',
        'bursar'     => 'text-emerald-600',
        'teacher'    => 'text-sky-600',
        'parent'     => 'text-pink-600',
        'proprietor' => 'text-indigo-600',
        default      => 'text-violet-600',
    };

    $activeBg = match($role) {
        'admin'      => 'bg-violet-50 text-violet-600',
        'bursar'     => 'bg-emerald-50 text-emerald-600',
        'teacher'    => 'bg-sky-50 text-sky-600',
        'parent'     => 'bg-pink-50 text-pink-600',
        'proprietor' => 'bg-indigo-50 text-indigo-600',
        default      => 'bg-violet-50 text-violet-600',
    };

    $dotColor = match($role) {
        'admin'      => 'bg-violet-600',
        'bursar'     => 'bg-emerald-600',
        'teacher'    => 'bg-sky-600',
        'parent'     => 'bg-pink-600',
        'proprietor' => 'bg-indigo-600',
        default      => 'bg-violet-600',
    };

    // Build tabs based on role
    $tabs = [];

    if ($role === 'parent') {
        $tabs = [
            [
                'label'  => 'Home',
                'route'  => route('dashboard'),
                'active' => request()->routeIs('dashboard') || request()->routeIs('parents.dashboard'),
                'icon'   => 'home',
            ],
            [
                'label'  => 'Children',
                'route'  => route('students.index'),
                'active' => request()->routeIs('students.*'),
                'icon'   => 'heart',
            ],
            [
                'label'  => 'Timetable',
                'route'  => route('timetable'),
                'active' => request()->routeIs('timetable*'),
                'icon'   => 'calendar',
            ],
            [
                'label'  => 'Fees',
                'route'  => ($tenant && $tenant->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists()) ? route('parent.pay') : route('profile'),
                'active' => request()->routeIs('parent.pay') || request()->routeIs('profile'),
                'icon'   => 'wallet',
            ],
            [
                'label'  => 'Menu',
                'action' => 'openDrawer',
                'active' => false,
                'icon'   => 'grid',
            ],
        ];
    } elseif ($role === 'teacher') {
        $tabs = [
            [
                'label'  => 'Home',
                'route'  => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
                'icon'   => 'home',
            ],
            [
                'label'  => 'Attendance',
                'route'  => route('attendance'),
                'active' => request()->routeIs('attendance'),
                'icon'   => 'check-circle',
            ],
            [
                'label'  => 'Scores',
                'route'  => route('results.entry'),
                'active' => request()->routeIs('results.entry'),
                'icon'   => 'clipboard-list',
            ],
            [
                'label'  => 'Classes',
                'route'  => route('classes.index'),
                'active' => request()->routeIs('classes.*'),
                'icon'   => 'academic-cap',
            ],
            [
                'label'  => 'More',
                'action' => 'openDrawer',
                'active' => false,
                'icon'   => 'grid',
            ],
        ];
    } elseif ($role === 'bursar') {
        $tabs = [
            [
                'label'  => 'Home',
                'route'  => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
                'icon'   => 'home',
            ],
            [
                'label'  => 'Billing',
                'route'  => route('billing.index'),
                'active' => request()->routeIs('billing.*') && !request()->has('tab'),
                'icon'   => 'receipt',
            ],
            [
                'label'  => 'Debtors',
                'route'  => route('billing.index', ['tab' => 'debtors']),
                'active' => request()->routeIs('billing.*') && request()->get('tab') === 'debtors',
                'icon'   => 'wallet',
            ],
            [
                'label'  => 'Timesheet',
                'route'  => route('attendance.staff-timesheet'),
                'active' => request()->routeIs('attendance.staff-timesheet'),
                'icon'   => 'clock',
            ],
            [
                'label'  => 'More',
                'action' => 'openDrawer',
                'active' => false,
                'icon'   => 'grid',
            ],
        ];
    } elseif ($role === 'proprietor') {
        $tabs = [
            [
                'label'  => 'Home',
                'route'  => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
                'icon'   => 'home',
            ],
            [
                'label'  => 'Broadsheet',
                'route'  => route('results.broadsheet'),
                'active' => request()->routeIs('results.broadsheet'),
                'icon'   => 'chart-bar',
            ],
            [
                'label'  => 'Debtors',
                'route'  => route('billing.index', ['tab' => 'debtors']),
                'active' => request()->routeIs('billing.*'),
                'icon'   => 'wallet',
            ],
            [
                'label'  => 'Timesheet',
                'route'  => route('attendance.staff-timesheet'),
                'active' => request()->routeIs('attendance.staff-timesheet'),
                'icon'   => 'clock',
            ],
            [
                'label'  => 'More',
                'action' => 'openDrawer',
                'active' => false,
                'icon'   => 'grid',
            ],
        ];
    } else {
        // Default / Admin
        $tabs = [
            [
                'label'  => 'Home',
                'route'  => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
                'icon'   => 'home',
            ],
            [
                'label'  => 'Students',
                'route'  => route('students.index'),
                'active' => request()->routeIs('students.*'),
                'icon'   => 'academic-cap',
            ],
            [
                'label'  => 'Attendance',
                'route'  => route('attendance'),
                'active' => request()->routeIs('attendance') || request()->routeIs('attendance.teachers'),
                'icon'   => 'check-circle',
            ],
            [
                'label'  => 'Broadsheet',
                'route'  => route('results.broadsheet'),
                'active' => request()->routeIs('results.broadsheet'),
                'icon'   => 'chart-bar',
            ],
            [
                'label'  => 'Menu',
                'action' => 'openDrawer',
                'active' => false,
                'icon'   => 'grid',
            ],
        ];
    }
@endphp

{{-- Native Mobile Tab Bar --}}
<nav aria-label="Mobile Navigation"
     class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/80 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] pb-safe select-none">
    <div class="grid grid-cols-5 h-16 items-center px-1 max-w-lg mx-auto">
        @foreach($tabs as $tab)
            @if(isset($tab['action']) && $tab['action'] === 'openDrawer')
                <button type="button"
                        @click="mobileSidebarOpen = true"
                        class="tab-bar-item flex flex-col items-center justify-center py-1.5 px-0.5 tap-bounce group relative focus:outline-none">
                    <div class="flex items-center justify-center w-10 h-8 rounded-xl transition-all duration-200 text-slate-500 group-active:scale-90 group-hover:bg-slate-100">
                        <svg class="h-6 w-6 stroke-[1.8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 tracking-tight mt-0.5 leading-none">{{ $tab['label'] }}</span>
                </button>
            @else
                <a href="{{ $tab['route'] }}"
                   class="tab-bar-item flex flex-col items-center justify-center py-1.5 px-0.5 tap-bounce group relative focus:outline-none">
                    {{-- Active top indicator dot --}}
                    @if($tab['active'])
                        <span class="absolute top-1 w-1 h-1 rounded-full {{ $dotColor }} shadow-sm"></span>
                    @endif

                    <div class="flex items-center justify-center w-10 h-8 rounded-xl transition-all duration-200 {{ $tab['active'] ? $activeBg . ' shadow-sm' : 'text-slate-400 group-hover:text-slate-600' }}">
                        @if($tab['icon'] === 'home')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        @elseif($tab['icon'] === 'heart')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        @elseif($tab['icon'] === 'calendar')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                            </svg>
                        @elseif($tab['icon'] === 'wallet' || $tab['icon'] === 'receipt')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                        @elseif($tab['icon'] === 'check-circle')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif($tab['icon'] === 'clipboard-list')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        @elseif($tab['icon'] === 'academic-cap')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                            </svg>
                        @elseif($tab['icon'] === 'chart-bar')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        @elseif($tab['icon'] === 'clock')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        @endif
                    </div>
                    <span class="text-[10px] {{ $tab['active'] ? 'font-extrabold ' . $accentColor : 'font-semibold text-slate-400 group-hover:text-slate-600' }} tracking-tight mt-0.5 leading-none">{{ $tab['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</nav>
