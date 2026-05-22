{{-- School branding --}}
<div class="flex items-center gap-2.5 px-4 py-4 border-b border-slate-800">
    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-700">
        @if($schoolLogo)
            <img src="{{ asset('uploads/'.str_replace('\\','/',$schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-0.5"/>
        @else
            <svg class="h-4 w-4 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17a2 2 0 01-1.1 1.79l-7.4 3.7a2 2 0 01-1.8 0l-7.4-3.7A2 2 0 012 17V9"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V9"/>
            </svg>
        @endif
    </div>
    <div class="min-w-0">
        <div class="truncate text-xs font-bold text-white leading-tight">{{ $schoolName }}</div>
        <div class="text-[10px] font-medium text-slate-400">Student Portal</div>
    </div>
</div>

{{-- Nav items --}}
<nav class="flex-1 overflow-y-auto sidebar-scroll py-3 px-2 space-y-0.5">
    @foreach($navItems as $item)
        @php $active = request()->routeIs($item['route']); @endphp
        <a href="{{ route($item['route']) }}" wire:navigate
           class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition-all
                  {{ $active
                       ? 'bg-white text-slate-900 shadow-sm font-bold'
                       : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium' }}">
            <svg class="h-4 w-4 flex-shrink-0 {{ $active ? 'text-slate-900' : 'text-slate-500' }}"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                {!! $item['icon'] !!}
            </svg>
            <span class="truncate text-xs">{{ $item['label'] }}</span>
            @if($active)
                <div class="ml-auto h-1.5 w-1.5 rounded-full bg-slate-900 flex-shrink-0"></div>
            @endif
        </a>
    @endforeach
</nav>

{{-- Student info + logout --}}
<div class="border-t border-slate-800 p-3">
    <div class="flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-2 mb-2">
        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-slate-600 text-white text-xs font-bold">
            {{ $studentInitial }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="truncate text-xs font-bold text-white">{{ $studentName }}</div>
            <div class="truncate text-[10px] text-slate-400">{{ $studentAdmission }}</div>
        </div>
    </div>
    <form method="POST" action="{{ route('student.logout') }}">
        @csrf
        <button type="submit" class="w-full flex items-center justify-center gap-1.5 rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-red-600 hover:text-white transition-all">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Logout
        </button>
    </form>
</div>
