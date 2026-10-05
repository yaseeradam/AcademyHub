@php
    $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
    $isHomeworkInstalled = $tenant && $tenant->activeMarketplaceComponents()->where('slug', 'homework')->exists();
    $isELearningInstalled = $tenant && $tenant->activeMarketplaceComponents()->where('slug', 'e-learning')->exists();

    $secondTab = [
        'label'  => 'Homework',
        'route'  => route('student.homework'),
        'active' => request()->routeIs('student.homework'),
        'icon'   => 'pencil',
    ];

    if (! $isHomeworkInstalled && $isELearningInstalled) {
        $secondTab = [
            'label'  => 'E-Learning',
            'route'  => route('student.e-learning'),
            'active' => request()->routeIs('student.e-learning'),
            'icon'   => 'book-open',
        ];
    } elseif (! $isHomeworkInstalled && ! $isELearningInstalled) {
        $secondTab = [
            'label'  => 'Exams',
            'route'  => route('student.exams'),
            'active' => request()->routeIs('student.exams'),
            'icon'   => 'clipboard',
        ];
    }

    $tabs = [
        [
            'label'  => 'Home',
            'route'  => route('student.dashboard'),
            'active' => request()->routeIs('student.dashboard'),
            'icon'   => 'home',
        ],
        $secondTab,
        [
            'label'  => 'Results',
            'route'  => route('student.results'),
            'active' => request()->routeIs('student.results') || request()->routeIs('student.report-card'),
            'icon'   => 'chart-bar',
        ],
        [
            'label'  => 'Attendance',
            'route'  => route('student.attendance'),
            'active' => request()->routeIs('student.attendance'),
            'icon'   => 'check-circle',
        ],
        [
            'label'  => 'Menu',
            'action' => 'openDrawer',
            'active' => false,
            'icon'   => 'grid',
        ],
    ];
@endphp

{{-- Native Student Mobile Tab Bar --}}
<nav aria-label="Student Mobile Navigation"
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
                        <span class="absolute top-1 w-1 h-1 rounded-full bg-violet-600 shadow-sm"></span>
                    @endif

                    <div class="flex items-center justify-center w-10 h-8 rounded-xl transition-all duration-200 {{ $tab['active'] ? 'bg-violet-50 text-violet-600 shadow-sm' : 'text-slate-400 group-hover:text-slate-600' }}">
                        @if($tab['icon'] === 'home')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        @elseif($tab['icon'] === 'pencil')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        @elseif($tab['icon'] === 'book-open')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        @elseif($tab['icon'] === 'clipboard')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        @elseif($tab['icon'] === 'chart-bar')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        @elseif($tab['icon'] === 'check-circle')
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 {{ $tab['active'] ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        @endif
                    </div>
                    <span class="text-[10px] {{ $tab['active'] ? 'font-extrabold text-violet-600' : 'font-semibold text-slate-400 group-hover:text-slate-600' }} tracking-tight mt-0.5 leading-none">{{ $tab['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</nav>
