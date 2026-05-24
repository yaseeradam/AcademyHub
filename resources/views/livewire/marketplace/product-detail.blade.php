<div class="space-y-6 font-sans max-w-4xl mx-auto">
    {{-- Flash Notifications --}}
    @if(session('review_success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm animate-fadeIn">
            ✓ {{ session('review_success') }}
        </div>
    @endif

    {{-- App Store Header --}}
    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
        <a href="{{ route('marketplace') }}" class="p-2.5 rounded-full hover:bg-gray-100 transition-colors border border-gray-200 bg-white shadow-sm flex items-center justify-center">
            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ $productData['category'] }}</span>
            <h1 class="text-xl font-black text-gray-900 tracking-tight leading-none mt-0.5">App Details</h1>
        </div>
    </div>

    @if($isInstalled && $installPivot && $installPivot->status === 'suspended')
        <div class="rounded-2xl border-2 border-rose-200 bg-rose-50 p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="h-10 w-10 rounded-xl bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-rose-950">Usage Suspended</div>
                    <div class="mt-1 text-xs text-rose-850">This plugin has been suspended for your school by the platform Superadmin. Access to its features is disabled, and termly usage fee billing is paused until it is reactivated.</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Product Core Info Card --}}
    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-6 items-start sm:items-center">
            {{-- Big rounded app icon --}}
            @php
                $color = match($product) {
                    'whatsapp-bot' => 'from-green-400 to-emerald-600',
                    'student-dashboard' => 'from-blue-400 to-indigo-600',
                    'cbt' => 'from-purple-400 to-violet-600',
                    'savings-loan' => 'from-emerald-400 to-teal-600',
                    'messages' => 'from-amber-300 to-amber-600',
                    'homework' => 'from-cyan-400 to-sky-600',
                    'e-learning' => 'from-cyan-400 to-indigo-500',
                    'parent-portal' => 'from-blue-400 to-indigo-600',
                    default => 'from-slate-400 to-slate-600'
                };
            @endphp
            <div class="flex-shrink-0">
                <div class="h-24 w-24 rounded-[2rem] bg-gradient-to-br {{ $color }} text-white flex items-center justify-center shadow-lg relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-b from-white/20 to-transparent pointer-events-none"></div>
                    @if($productData['icon'] === 'whatsapp')
                        <svg class="h-12 w-12 text-white fill-current" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.457 5.709 1.458h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    @elseif($productData['icon'] === 'student')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    @elseif($productData['icon'] === 'exam')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    @elseif($productData['icon'] === 'finance')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($productData['icon'] === 'messages')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    @elseif($productData['icon'] === 'document')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @else
                        <span class="text-5xl">{!! $productData['icon'] ?: '📦' !!}</span>
                    @endif
                </div>
            </div>

            {{-- Text & Controls --}}
            <div class="flex-1 space-y-4">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 leading-tight tracking-tight">{{ $productData['name'] }}</h2>
                    <p class="text-sm font-semibold text-gray-400 mt-1">MyAcademy Studios · {{ $productData['category'] }}</p>
                </div>

                {{-- Action buttons iOS style --}}
                <div class="flex flex-wrap items-center gap-3">
                    @if($isInstalled)
                        <span class="bg-[#e8f7f0] text-emerald-600 text-xs font-black px-6 py-2 rounded-full uppercase tracking-wider shadow-sm border border-emerald-100">
                            ✓ ACTIVE / INSTALLED
                        </span>
                        <button wire:click="startUninstall" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-black px-5 py-2 rounded-full transition-all tracking-wider uppercase">
                            UNINSTALL
                        </button>
                    @else
                        <button
                            wire:click="install"
                            wire:loading.attr="disabled"
                            class="bg-[#007aff] hover:bg-blue-600 text-white text-xs font-black px-7 py-2.5 rounded-full shadow-md shadow-blue-100 tracking-wider uppercase transition-all inline-flex items-center gap-2"
                        >
                            <svg wire:loading wire:target="install" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span wire:loading.remove wire:target="install">
                                @if($dbComponent?->price > 0)
                                    GET (₦{{ number_format($dbComponent->price) }})
                                @else
                                    GET / INSTALL
                                @endif
                            </span>
                            <span wire:loading wire:target="install">Installing...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4. Simulated App Store Metrics Segment Bar --}}
        <div class="mt-8 border-t border-gray-100 pt-6 grid grid-cols-4 divide-x divide-gray-150 text-center">
            <div>
                <div class="text-base font-black text-gray-900">{{ number_format($ratingAvg ?: 4.9, 1) }} ★</div>
                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mt-1">{{ number_format($ratingCount ?: 24) }} Ratings</div>
            </div>
            <div>
                <div class="text-base font-black text-gray-900">#1</div>
                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mt-1">In {{ $productData['category'] }}</div>
            </div>
            <div>
                <div class="text-base font-black text-gray-900">{{ number_format($dbComponent?->installs ?: 88) }}+</div>
                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mt-1">Downloads</div>
            </div>
            <div>
                <div class="text-base font-black text-gray-900">v2.1</div>
                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mt-1">Compatibility</div>
            </div>
        </div>
    </div>

    {{-- Uninstall Warning Block --}}
    @if($confirmingUninstall)
        <div class="rounded-2xl border-2 border-red-200 bg-red-50 p-5 shadow-sm animate-fadeIn">
            <div class="flex items-start gap-4">
                <div class="h-10 w-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-black text-red-800">Uninstall this application?</div>
                    <div class="mt-1 text-xs text-red-700 space-y-1.5 leading-relaxed">
                        <p>This action is immediate. Users will lose access to its respective submenus.</p>
                        @if($setupFee > 0)
                            <p class="font-extrabold">⚠️ Warning: Reinstalling will require paying the Setup Fee of {{ config('myacademy.currency_symbol','₦') }}{{ number_format($setupFee, 2) }} again.</p>
                        @endif
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <button wire:click="cancelUninstall" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 transition shadow-sm">Cancel</button>
                        <button wire:click="uninstall" wire:loading.attr="disabled" class="flex items-center gap-2 px-5 py-2 rounded-xl bg-red-600 text-xs font-bold text-white hover:bg-red-700 transition disabled:opacity-50 shadow-sm shadow-red-200">
                            <svg wire:loading wire:target="uninstall" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Yes, Uninstall App
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- APP STORE HIGH-FIDELITY SCREENSHOT CAROUSEL --}}
    <div class="space-y-3">
        <h3 class="text-lg font-black text-gray-900 tracking-tight">Screenshots</h3>
        <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent">
            {{-- Mock high-fidelity smartphone screenshots (CSS rendered) --}}
            <div class="w-[180px] h-[320px] sm:w-[220px] sm:h-[380px] flex-shrink-0 bg-gradient-to-br {{ $color }} rounded-[2rem] p-4.5 text-white flex flex-col justify-between shadow-md border border-white/10 relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,#ffffff_1.5px,transparent_1.5px)] opacity-10 bg-[size:16px_16px]"></div>
                <div class="flex items-center justify-between text-[9px] font-bold tracking-widest uppercase opacity-85">
                    <span>Active Interface</span>
                    <span>100% Secure</span>
                </div>
                <div class="my-auto text-center space-y-3">
                    <span class="text-4xl block animate-bounce">📊</span>
                    <h4 class="text-sm font-black tracking-tight leading-snug">{{ $productData['name'] }}</h4>
                    <p class="text-[10px] opacity-80 leading-relaxed font-semibold">Clean student details and records audit logs</p>
                </div>
                <div class="w-full h-1 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full" style="width: 80%"></div>
                </div>
            </div>

            <div class="w-[180px] h-[320px] sm:w-[220px] sm:h-[380px] flex-shrink-0 bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 rounded-[2rem] p-4.5 text-white flex flex-col justify-between shadow-md border border-white/10 relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-20 h-20 rounded-full bg-white/5"></div>
                <div class="flex items-center justify-between text-[9px] font-bold tracking-widest uppercase opacity-85">
                    <span>Performance</span>
                    <span>Optimized</span>
                </div>
                <div class="my-auto text-center space-y-3">
                    <span class="text-4xl block">📈</span>
                    <h4 class="text-sm font-black tracking-tight leading-snug">Visual Analytics</h4>
                    <p class="text-[10px] opacity-80 leading-relaxed font-semibold">Track student averages across terms effortlessly</p>
                </div>
                <div class="w-full h-1 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full" style="width: 65%"></div>
                </div>
            </div>

            <div class="w-[180px] h-[320px] sm:w-[220px] sm:h-[380px] flex-shrink-0 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-[2rem] p-4.5 text-white flex flex-col justify-between shadow-md border border-white/10 relative overflow-hidden">
                <div class="flex items-center justify-between text-[9px] font-bold tracking-widest uppercase opacity-85">
                    <span>Billing Summary</span>
                    <span>Transparent</span>
                </div>
                <div class="my-auto text-center space-y-3">
                    <span class="text-4xl block">💳</span>
                    <h4 class="text-sm font-black tracking-tight leading-snug">Fee Structures</h4>
                    <p class="text-[10px] opacity-80 leading-relaxed font-semibold">Examine outstanding bills and setup structures</p>
                </div>
                <div class="w-full h-1 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full" style="width: 90%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Body --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
        <div class="lg:col-span-2 space-y-6">
            {{-- Class Targeting Audience Segment --}}
            <div class="rounded-2xl border border-indigo-100 bg-white/70 backdrop-blur-md p-6 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-black text-indigo-950 flex items-center gap-2 tracking-tight">
                            🎯 Class Target Audience
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Select the classes whose student portal will activate and bill this app.</p>
                    </div>
                    @if($isInstalled)
                        <button wire:click="updateClasses" wire:loading.attr="disabled" class="btn-primary py-2 px-4.5 text-[11px] font-bold shadow-sm flex items-center gap-2 self-start sm:self-auto rounded-xl">
                            <svg wire:loading wire:target="updateClasses" class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span wire:loading.remove wire:target="updateClasses">Apply target classes</span>
                            <span wire:loading wire:target="updateClasses">Updating target...</span>
                        </button>
                    @endif
                </div>

                @if(session('message'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800 animate-bounce">
                        ✓ {{ session('message') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    @foreach($classes as $class)
                        <label class="relative flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-indigo-50/30 hover:border-indigo-200 transition-all duration-200 cursor-pointer select-none group">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" wire:model.live="selectedClasses" value="{{ $class->id }}" class="h-4.5 w-4.5 rounded-md text-indigo-600 border-slate-300 focus:ring-indigo-500/20 transition-all duration-200 cursor-pointer">
                                <span class="text-sm font-bold text-slate-800 group-hover:text-indigo-950 transition-colors">{{ $class->name }}</span>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-200/60 group-hover:bg-indigo-100/60 px-2.5 py-0.5 text-xs font-bold text-slate-600 group-hover:text-indigo-700 transition-colors">
                                {{ $class->students_count }} students
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- App Description --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-black text-gray-900 tracking-tight mb-3">About this App</h3>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $productData['description'] }}</p>
            </div>

            {{-- App Features --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-black text-gray-900 tracking-tight mb-4">Key Features</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($productData['features'] as $feature)
                        <div class="flex items-start gap-3">
                            <div class="h-5.5 w-5.5 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-3 w-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm text-gray-700 font-medium">{{ $feature }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Ratings & Reviews (Google Play style) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5 border-b border-gray-100 pb-3">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Ratings & Reviews</h3>
                    @if($isInstalled && !$userReview)
                        <button wire:click="$toggle('showReviewForm')" class="text-xs font-black text-indigo-600 hover:text-indigo-700">
                            {{ $showReviewForm ? 'Cancel Review' : '+ Write a Review' }}
                        </button>
                    @endif
                </div>

                <div class="flex flex-col md:flex-row items-center gap-7 pb-6 border-b border-gray-100 mb-6">
                    <div class="text-center shrink-0">
                        <div class="text-5xl font-black text-gray-900 leading-none tracking-tight">{{ number_format($ratingAvg ?: 4.9, 1) }}</div>
                        <div class="flex justify-center gap-0.5 mt-2">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-4.5 w-4.5 {{ $i <= round($ratingAvg ?: 5) ? 'text-amber-400' : 'text-gray-200' }} fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-2.5">{{ number_format($ratingCount ?: 24) }} Ratings</div>
                    </div>

                    {{-- Review Progress bar metrics --}}
                    <div class="flex-1 w-full space-y-2">
                        @foreach([5 => 80, 4 => 15, 3 => 3, 2 => 1, 1 => 1] as $stars => $percent)
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-500 w-2.5">{{ $stars }}</span>
                                <div class="flex-1 bg-gray-100 h-2 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#007aff] rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="text-[10px] font-bold text-gray-400 w-8 text-right">{{ $percent }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Review Form --}}
                @if($showReviewForm && $isInstalled)
                    <div class="mb-6 rounded-2xl bg-slate-50 border border-slate-200 p-5 shadow-inner">
                        <div class="text-sm font-bold text-slate-700 mb-3">Rate this application</div>
                        <div class="flex items-center gap-2 mb-4">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="$set('reviewRating', {{ $i }})" class="focus:outline-none">
                                    <svg class="h-8 w-8 {{ $i <= $reviewRating ? 'text-amber-400' : 'text-gray-300' }} fill-current transition-colors" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest ml-3 bg-white px-3 py-1 border rounded-lg shadow-sm">{{ ['','Poor','Fair','Good','Great','Excellent'][$reviewRating] }}</span>
                        </div>
                        <textarea wire:model="reviewComment" rows="3" placeholder="Share your experience using this module (options, requests)..."
                                  class="w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 resize-none transition-all"></textarea>
                        <div class="flex justify-end mt-4 gap-2.5">
                            <button wire:click="$set('showReviewForm', false)" class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-500 hover:bg-gray-100 bg-white transition shadow-sm">Cancel</button>
                            <button wire:click="submitReview" class="px-5 py-2.5 rounded-xl bg-[#007aff] hover:bg-blue-600 text-xs font-bold text-white transition shadow-md shadow-blue-100">Submit Review</button>
                        </div>
                    </div>
                @endif

                @if($userReview)
                    <div class="mb-5 rounded-xl bg-indigo-50 border border-indigo-150 px-4 py-3 text-xs text-indigo-700 font-semibold flex items-center gap-2">
                        <span>✓</span> You've reviewed this application. Your rating is <strong>{{ $userReview->rating }}/5 stars</strong>.
                    </div>
                @endif

                {{-- Reviews List --}}
                @if($reviews->isNotEmpty())
                    <div class="space-y-4 pt-2">
                        @foreach($reviews as $review)
                            <div class="flex items-start gap-4 p-4 rounded-2xl bg-gray-50/50 border border-gray-150">
                                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0 shadow-sm uppercase">
                                    {{ substr($review->user?->name ?? 'A', 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-black text-slate-900">{{ $review->user?->name ?: 'Administrator' }}</div>
                                        <div class="text-[10px] font-bold text-slate-400">{{ $review->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="flex items-center gap-0.5 mt-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="h-3.5 w-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }} fill-current" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    @if($review->comment)
                                        <div class="mt-2 text-sm text-slate-700 leading-relaxed font-medium">{{ $review->comment }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-sm text-slate-400 font-medium">No reviews yet. {{ $isInstalled ? 'Write the first review for this module!' : '' }}</div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Billing Overview Glassmorphic Card --}}
            <div class="rounded-2xl border border-indigo-150 bg-gradient-to-br from-indigo-50/80 to-purple-50/80 p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-indigo-950 uppercase tracking-widest text-center border-b border-indigo-100 pb-3">Ledger Pricing</h3>
                
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-500">Setup Fee:</span>
                        <span class="font-extrabold text-indigo-900 text-sm">{{ config('myacademy.currency_symbol','₦') }}{{ number_format($setupFee, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs border-t border-indigo-100/50 pt-3">
                        <span class="font-bold text-slate-500">Termly Usage Rate:</span>
                        <span class="font-extrabold text-slate-700">{{ config('myacademy.currency_symbol','₦') }}{{ number_format($usageFeePerStudent, 2) }} <span class="font-medium text-[10px] text-slate-400">/ student</span></span>
                    </div>

                    @if($calculatedStudentCount > 0)
                        <div class="bg-white/60 border border-indigo-100/30 rounded-xl p-3 text-center space-y-1 mt-2.5">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Estimated Termly Billing</div>
                            <div class="text-lg font-black text-emerald-600">
                                {{ config('myacademy.currency_symbol','₦') }}{{ number_format($estimatedTermlyUsageFee, 2) }}
                            </div>
                            <div class="text-[9px] font-bold text-emerald-500">Based on {{ number_format($calculatedStudentCount) }} students</div>
                        </div>
                    @endif
                </div>

                @if(!$isInstalled)
                    <button wire:click="install" wire:loading.attr="disabled" class="w-full bg-[#007aff] hover:bg-blue-600 text-white font-bold text-xs py-3 rounded-xl shadow-md shadow-blue-100 tracking-wider uppercase transition-all flex items-center justify-center gap-2">
                        <svg wire:loading wire:target="install" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span>GET APP NOW</span>
                    </button>
                @else
                    <div class="rounded-xl bg-emerald-100/60 border border-emerald-200/50 p-3 text-center">
                        <div class="text-[10px] font-black text-emerald-800 uppercase tracking-widest flex items-center justify-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            Subscription Active
                        </div>
                        @if($installPivot?->installed_at)
                            <div class="text-[9px] text-emerald-600 font-bold mt-1">Since {{ \Carbon\Carbon::parse($installPivot->installed_at)->format('F j, Y') }}</div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Requirements --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Requirements</h3>
                <ul class="space-y-3">
                    @foreach($productData['requirements'] as $req)
                        <li class="flex items-start gap-2.5 text-xs text-gray-600 font-medium">
                            <div class="h-1.5 w-1.5 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>{{ $req }}
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Benefits --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Benefits</h3>
                <ul class="space-y-4">
                    @foreach($productData['benefits'] as $benefit)
                        <li class="flex items-start gap-3">
                            <div class="h-6 w-6 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <svg class="h-3.5 w-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <span class="text-xs text-gray-600 font-medium leading-relaxed">{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
