@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Hero Card --}}
    <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative px-4 py-5 sm:px-8 sm:py-8">
            <div class="flex items-center gap-2 mb-2 sm:mb-3">
                <span class="h-2 w-2 rounded-full bg-orange-400 animate-pulse"></span>
                <span class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest" style="color: #93c5fd;">System Configuration</span>
            </div>
            <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">Settings</h2>
            <p class="mt-1 text-xs sm:text-base font-medium" style="color: #93c5fd;">Manage your school system preferences</p>
            <div class="mt-3 sm:mt-4">
                <span class="inline-flex items-center gap-2 rounded-lg px-3 py-1 sm:px-4 sm:py-1.5 text-xs sm:text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    {{ config('academyhub.school_name', 'School') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('status'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 px-4 py-2.5 sm:px-5 sm:py-3 ring-1 ring-emerald-100">
            <svg class="h-4 w-4 shrink-0 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-xs sm:text-sm font-semibold text-emerald-700">{{ session('status') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-2xl bg-red-50 px-4 py-3 sm:px-5 sm:py-4 ring-1 ring-red-100">
            <div class="text-xs sm:text-sm font-semibold text-red-700">Please fix the following:</div>
            <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs sm:text-sm text-red-600">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Quick Nav Cards (2-Column Mobile App Tiles) --}}
    @php
        $navItems = [
            ['route'=>'settings.audit-logs',   'label'=>'Audit Logs',    'sub'=>'System activity',   'from'=>'from-amber-400',  'to'=>'to-orange-500',  'icon'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
            ['route'=>'settings.results',      'label'=>'Scoring',       'sub'=>'Grade config',      'from'=>'from-blue-500',   'to'=>'to-indigo-600',  'icon'=>'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
            ['route'=>'settings.certificates', 'label'=>'Certificates',  'sub'=>'Award templates',   'from'=>'from-pink-500',   'to'=>'to-rose-600',    'icon'=>'<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>'],
            ['route'=>'settings.templates',    'label'=>'Templates',     'sub'=>'Report designs',    'from'=>'from-violet-500', 'to'=>'to-purple-600',  'icon'=>'<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>'],
            ['route'=>'settings.attendance',   'label'=>'Shifts & Hours', 'sub'=>'Attendance rules',  'from'=>'from-emerald-500','to'=>'to-teal-600',    'icon'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
            ['route'=>'settings.custom-fields','label'=>'Custom Fields', 'sub'=>'Extra data fields', 'from'=>'from-cyan-400',   'to'=>'to-teal-500',    'icon'=>'<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>'],
            ['route'=>'settings.whatsapp',     'label'=>'WhatsApp Bot',  'sub'=>'Free gateway & QR', 'from'=>'from-emerald-600','to'=>'to-green-500',   'icon'=>'<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>'],
        ];
    @endphp
    <div class="grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="group relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $item['from'] }} {{ $item['to'] }} p-3.5 sm:p-5 text-white shadow-md transition-all duration-200 hover:shadow-xl active:scale-[0.98] sm:hover:-translate-y-0.5">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
                <div class="absolute right-2 bottom-2 h-10 w-10 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="grid h-9 w-9 sm:h-10 sm:w-10 place-items-center rounded-xl bg-white/20 transition group-hover:scale-110">
                        <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $item['icon'] !!}</svg>
                    </div>
                    <div class="mt-2.5 sm:mt-3 text-xs sm:text-sm font-bold truncate">{{ $item['label'] }}</div>
                    <div class="text-[10px] sm:text-xs text-white/75 truncate">{{ $item['sub'] }}</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- School Info Card --}}
    <div class="overflow-hidden rounded-2xl sm:rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">

        {{-- Card Header --}}
        <div class="flex items-center gap-3 sm:gap-4 border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">
            <div class="grid h-9 w-9 sm:h-11 sm:w-11 place-items-center rounded-xl bg-orange-50 text-orange-400 shrink-0">
                <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm sm:text-base font-bold text-slate-800 truncate">School Information</div>
                <div class="text-xs text-slate-400 truncate">Update your school's public profile</div>
            </div>
            @if(config('academyhub.school_logo'))
                <img src="{{ asset('uploads/' . str_replace('\\', '/', config('academyhub.school_logo'))) }}"
                    class="h-8 w-8 sm:h-10 sm:w-10 rounded-xl object-contain ring-1 ring-slate-200 shrink-0" />
            @endif
        </div>

        <form method="POST" action="{{ route('settings.update-school') }}" enctype="multipart/form-data" x-data="{ preview: null }">
            @csrf
            <div class="p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">

                    {{-- Fields --}}
                    <div class="flex-1 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">School Name</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                </div>
                                <input name="school_name" placeholder="e.g. Greenfield Academy"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-800 placeholder-slate-400 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100"
                                    value="{{ old('school_name', config('academyhub.school_name')) }}" required />
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Address</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <input name="school_address" placeholder="123 School Street, City"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100"
                                    value="{{ old('school_address', config('academyhub.school_address')) }}" />
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">School Motto</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                                    </svg>
                                </div>
                                <input name="school_motto" placeholder="e.g. Discipline • Knowledge • Excellence"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100"
                                    value="{{ old('school_motto', config('academyhub.school_motto')) }}" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </div>
                                <input name="school_phone" placeholder="+1 234 567 8900"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100"
                                    value="{{ old('school_phone', config('academyhub.school_phone')) }}" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                </div>
                                <input name="school_email" type="email" placeholder="info@school.edu"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100"
                                    value="{{ old('school_email', config('academyhub.school_email')) }}" />
                            </div>
                        </div>

                    </div>

                    {{-- Logo Upload --}}
                    <div class="shrink-0">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Logo</label>
                        <label class="relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 transition hover:border-orange-300 hover:bg-orange-50/20"
                            style="width:110px;height:140px;"
                            @click="$refs.logoInput.click()">

                            <img x-show="preview" :src="preview" class="absolute inset-0 h-full w-full object-cover rounded-2xl" />

                            @if(config('academyhub.school_logo'))
                                <img x-show="!preview"
                                    src="{{ asset('uploads/' . str_replace('\\', '/', config('academyhub.school_logo'))) }}"
                                    class="absolute inset-0 h-full w-full object-cover rounded-2xl" />
                            @endif

                            <div class="absolute inset-0 flex flex-col items-center justify-center rounded-2xl bg-black/30 opacity-0 transition hover:opacity-100">
                                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                                <span class="mt-1 text-[10px] font-bold text-white">Change</span>
                            </div>

                            @if(!config('academyhub.school_logo'))
                                <div x-show="!preview" class="flex flex-col items-center gap-1">
                                    <svg class="h-7 w-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    <span class="text-[10px] font-semibold text-slate-400">Upload</span>
                                    <span class="text-[9px] text-slate-300">PNG / JPG</span>
                                </div>
                            @endif

                            <input x-ref="logoInput" name="school_logo" type="file" accept="image/*" class="hidden"
                                @change="preview = URL.createObjectURL($event.target.files[0])" />
                        </label>
                        <p class="mt-2 text-center text-[10px] text-slate-400">120×150px</p>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5">
                    <p class="text-xs text-slate-400">Changes apply immediately after saving.</p>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:from-orange-500 hover:to-amber-600 active:scale-95">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection
