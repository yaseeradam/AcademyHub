<div class="space-y-6">

    {{-- Flash --}}
    @if(session('review_success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('review_success') }}</div>
    @endif

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('marketplace') }}" class="p-2 rounded-xl hover:bg-gray-100 transition-colors">
            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $productData['name'] }}</h1>
            <p class="text-sm text-gray-500">{{ $productData['category'] }}</p>
        </div>
        @if($isInstalled)
            @if($installPivot && $installPivot->status === 'suspended')
                <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1.5 text-xs font-bold text-rose-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Suspended
                </span>
            @else
                <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                    Installed
                </span>
            @endif
        @endif
    </div>

    @if($isInstalled && $installPivot && $installPivot->status === 'suspended')
        <div class="rounded-2xl border-2 border-rose-200 bg-rose-50 p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="h-10 w-10 rounded-xl bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-rose-950">Usage Suspended</div>
                    <div class="mt-1 text-xs text-rose-850">This plugin has been suspended for your school by the platform Superadmin. Access to its features is disabled, and termly usage fee billing is paused until it is reactivated. If you have questions, please reach out to customer support.</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Product Overview --}}
    <div class="card-padded">
        <div class="flex flex-col lg:flex-row gap-6">
            {{-- Icon --}}
            <div class="flex-shrink-0">
                <div class="h-24 w-24 rounded-3xl bg-gradient-to-br
                    @if($productData['color']==='green') from-green-500 to-emerald-600
                    @elseif($productData['color']==='blue') from-blue-500 to-indigo-600
                    @elseif($productData['color']==='purple') from-purple-500 to-violet-600
                    @elseif($productData['color']==='emerald') from-emerald-500 to-teal-600
                    @elseif($productData['color']==='amber') from-amber-400 to-amber-600
                    @elseif($productData['color']==='cyan') from-cyan-500 to-sky-600
                    @endif flex items-center justify-center shadow-lg">
                    @if(($productData['icon'] ?? '') === 'whatsapp')
                        <svg class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.457 5.709 1.458h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    @elseif(($productData['icon'] ?? '') === 'student')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 21h6"/>
                        </svg>
                    @elseif(($productData['icon'] ?? '') === 'exam')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    @elseif(($productData['icon'] ?? '') === 'finance')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif(($productData['icon'] ?? '') === 'messages')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    @elseif(($productData['icon'] ?? '') === 'document')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @else
                        @if(isset($productData['icon']) && str_contains($productData['icon'], '<svg'))
                            {!! $productData['icon'] !!}
                        @elseif(isset($dbComponent) && $dbComponent->icon && str_contains($dbComponent->icon, '<svg'))
                            {!! $dbComponent->icon !!}
                        @else
                            <span class="text-4xl">{!! ($productData['icon'] ?? '') ?: (($dbComponent->icon ?? '') ?: '🧩') !!}</span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $productData['name'] }}</h2>
                        <p class="text-gray-600 mt-1">{{ $productData['short_description'] }}</p>

                        {{-- Star Rating display --}}
                        <div class="flex items-center gap-3 mt-3">
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= round($ratingAvg) ? 'text-amber-400' : 'text-gray-200' }} fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm font-bold text-gray-700">{{ $ratingAvg > 0 ? number_format($ratingAvg, 1) : 'No ratings' }}</span>
                            @if($ratingCount > 0)
                                <span class="text-sm text-gray-400">({{ $ratingCount }} {{ Str::plural('review', $ratingCount) }})</span>
                            @endif
                        </div>
                    </div>

                    {{-- Pricing + Actions --}}
                    <div class="flex flex-col items-stretch sm:items-end gap-3 flex-shrink-0 w-full sm:w-auto">
                        {{-- Premium Glassmorphism Price Box --}}
                        <div class="rounded-2xl bg-gradient-to-br from-indigo-50/80 to-purple-50/80 backdrop-blur-md border border-indigo-100 px-5 py-4 text-left sm:text-right min-w-0 sm:min-w-[220px] w-full sm:w-auto shadow-sm">
                            <div class="space-y-3">
                                <div>
                                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Setup Fee (One-Time)</div>
                                    <div class="text-xl font-black text-indigo-900 mt-0.5">
                                        {{ config('myacademy.currency_symbol','₦') }}{{ number_format($setupFee, 2) }}
                                    </div>
                                </div>
                                
                                <div class="border-t border-indigo-100/50 pt-2">
                                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Usage Fee (Termly)</div>
                                    <div class="text-sm text-slate-600 mt-0.5 font-medium">
                                        {{ config('myacademy.currency_symbol','₦') }}{{ number_format($usageFeePerStudent, 2) }} <span class="text-slate-400 font-normal">/ student</span>
                                    </div>
                                    @if($calculatedStudentCount > 0)
                                        <div class="text-xs text-emerald-600 font-semibold mt-0.5">
                                            × {{ number_format($calculatedStudentCount) }} target {{ Str::plural('student', $calculatedStudentCount) }}
                                        </div>
                                        <div class="text-lg font-black text-emerald-600 mt-1 transition-all duration-300">
                                            {{ config('myacademy.currency_symbol','₦') }}{{ number_format($estimatedTermlyUsageFee, 2) }} <span class="text-xs text-slate-400 font-normal">/ term</span>
                                        </div>
                                    @else
                                        <div class="text-xs text-rose-500 font-semibold mt-1">
                                            No classes selected
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($isInstalled)
                            <button wire:click="startUninstall" class="w-full flex items-center justify-center gap-2 rounded-xl border-2 border-red-200 bg-red-50 px-5 py-2.5 text-sm font-bold text-red-600 hover:bg-red-100 transition shadow-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Uninstall Plugin
                            </button>
                        @else
                            <button wire:click="install" wire:loading.attr="disabled" class="w-full btn-primary py-3 flex items-center justify-center gap-2 shadow-md">
                                <svg wire:loading wire:target="install" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span wire:loading.remove wire:target="install">Install Plugin</span>
                                <span wire:loading wire:target="install">Installing...</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Uninstall 2-Step Confirmation --}}
    @if($confirmingUninstall)
    <div class="rounded-2xl border-2 border-red-200 bg-red-50 p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="h-10 w-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1">
                <div class="text-base font-bold text-red-800">Confirm Uninstall — {{ $productData['name'] }}</div>
                <ul class="mt-2 space-y-1">
                    <li class="text-sm text-red-700">⚠ The plugin will be deactivated immediately for all users.</li>
                    <li class="text-sm text-red-700">⚠ Existing data will be preserved but inaccessible until reinstalled.</li>
                    @if($setupFee > 0)
                    <li class="text-sm font-semibold text-red-800">
                        ⚠ Reinstalling will require paying the Setup Fee of <strong>{{ config('myacademy.currency_symbol','₦') }}{{ number_format($setupFee, 2) }}</strong> again.
                    </li>
                    @endif
                </ul>
                <div class="mt-4 flex items-center gap-3">
                    <button wire:click="cancelUninstall" class="px-4 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                    <button wire:click="uninstall" wire:loading.attr="disabled"
                            class="flex items-center gap-2 px-5 py-2 rounded-xl bg-red-600 text-sm font-bold text-white hover:bg-red-700 transition disabled:opacity-50">
                        <svg wire:loading wire:target="uninstall" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Yes, Uninstall
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Description & Features --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Class Selection Glassmorphic Panel --}}
            <div class="rounded-2xl border border-indigo-100 bg-white/70 backdrop-blur-md p-6 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-indigo-950 flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Plugin Class Target Audience
                        </h3>
                        <p class="text-sm text-slate-500 mt-1">Select the classes whose students will have access and be billed under this plugin.</p>
                    </div>
                    @if($isInstalled)
                        <button wire:click="updateClasses" wire:loading.attr="disabled" class="btn-primary py-2 px-4 text-xs font-bold shadow-sm flex items-center gap-2 self-start sm:self-auto">
                            <svg wire:loading wire:target="updateClasses" class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span wire:loading.remove wire:target="updateClasses">Save Changes</span>
                            <span wire:loading wire:target="updateClasses">Saving...</span>
                        </button>
                    @endif
                </div>

                @if(session('message'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800 animate-bounce">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                    @foreach($classes as $class)
                        <label class="relative flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-indigo-50/30 hover:border-indigo-200 transition-all duration-200 cursor-pointer select-none group">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" wire:model.live="selectedClasses" value="{{ $class->id }}" class="h-4.5 w-4.5 rounded-md text-indigo-600 border-slate-300 focus:ring-indigo-500/20 transition-all duration-200 cursor-pointer">
                                <span class="text-sm font-bold text-slate-800 group-hover:text-indigo-950 transition-colors">{{ $class->name }}</span>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-200/60 group-hover:bg-indigo-100/60 px-2.5 py-0.5 text-xs font-semibold text-slate-600 group-hover:text-indigo-700 transition-colors">
                                {{ $class->students_count }} {{ Str::plural('student', $class->students_count) }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="card-padded">
                <h3 class="text-lg font-bold text-gray-900 mb-4">About this plugin</h3>
                <p class="text-gray-700 leading-relaxed">{{ $productData['description'] }}</p>
            </div>
            <div class="card-padded">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Key Features</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($productData['features'] as $feature)
                        <div class="flex items-center gap-3">
                            <div class="h-5 w-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <svg class="h-3 w-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm text-gray-700">{{ $feature }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Reviews Section --}}
            <div class="card-padded">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Reviews</h3>
                    @if($isInstalled && !$userReview)
                        <button wire:click="$toggle('showReviewForm')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                            {{ $showReviewForm ? 'Cancel' : '+ Leave a Review' }}
                        </button>
                    @endif
                </div>

                {{-- Review Form --}}
                @if($showReviewForm && $isInstalled)
                <div class="mb-5 rounded-2xl bg-slate-50 border border-slate-200 p-4">
                    <div class="text-sm font-semibold text-slate-700 mb-3">Your Rating</div>
                    {{-- Star picker --}}
                    <div class="flex items-center gap-2 mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="$set('reviewRating', {{ $i }})" class="focus:outline-none">
                                <svg class="h-7 w-7 {{ $i <= $reviewRating ? 'text-amber-400' : 'text-gray-300' }} fill-current transition-colors" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        @endfor
                        <span class="text-sm font-semibold text-slate-600 ml-2">{{ ['','Poor','Fair','Good','Great','Excellent'][$reviewRating] }}</span>
                    </div>
                    <textarea wire:model="reviewComment" rows="3" placeholder="Share your experience with this plugin (optional)..."
                              class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 resize-none"></textarea>
                    <div class="flex justify-end mt-3 gap-2">
                        <button wire:click="$set('showReviewForm', false)" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                        <button wire:click="submitReview" class="px-5 py-2 rounded-xl bg-indigo-600 text-xs font-bold text-white hover:bg-indigo-700 transition">Submit Review</button>
                    </div>
                </div>
                @endif

                @if($userReview)
                    <div class="mb-4 rounded-xl bg-indigo-50 border border-indigo-200 px-4 py-3 text-xs text-indigo-700 font-semibold">
                        ✓ You've already reviewed this plugin. Your rating: {{ $userReview->rating }}/5
                    </div>
                @endif

                @if($reviews->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                        <div class="flex items-start gap-3 border-b border-slate-100 pb-4 last:border-0">
                            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                {{ substr($review->user?->name ?? 'A', 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-slate-800">{{ ucfirst($review->user?->role ?? 'User') }}</div>
                                    <div class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="flex items-center gap-0.5 mt-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="h-3.5 w-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }} fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                @if($review->comment)
                                    <div class="mt-1 text-sm text-slate-600">{{ $review->comment }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-sm text-slate-400">No reviews yet. {{ $isInstalled ? 'Be the first to review!' : 'Install to leave a review.' }}</div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="card-padded">
                <h3 class="text-base font-bold text-gray-900 mb-4">Requirements</h3>
                <ul class="space-y-2">
                    @foreach($productData['requirements'] as $req)
                        <li class="flex items-start gap-2 text-sm text-gray-700">
                            <div class="h-1.5 w-1.5 rounded-full bg-gray-400 mt-2 flex-shrink-0"></div>{{ $req }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-padded">
                <h3 class="text-base font-bold text-gray-900 mb-4">Benefits</h3>
                <ul class="space-y-3">
                    @foreach($productData['benefits'] as $benefit)
                        <li class="flex items-start gap-3">
                            <div class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <svg class="h-3 w-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <span class="text-sm text-gray-700">{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Install CTA --}}
            @if(!$isInstalled)
            <div class="card-padded bg-gradient-to-br from-indigo-50/80 to-purple-50/80 border border-indigo-100 shadow-sm rounded-2xl">
                <div class="text-center space-y-3">
                    <div class="text-sm font-bold text-slate-800">Plugin Pricing Overview</div>
                    
                    <div class="grid grid-cols-2 gap-2 text-left bg-white/50 backdrop-blur-sm p-3 rounded-xl border border-indigo-50/50">
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Setup</div>
                            <div class="text-sm font-black text-indigo-900">{{ config('myacademy.currency_symbol','₦') }}{{ number_format($setupFee, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Usage</div>
                            <div class="text-sm font-black text-emerald-600">{{ config('myacademy.currency_symbol','₦') }}{{ number_format($usageFeePerStudent, 2) }} <span class="text-[10px] font-normal text-slate-400">/ student / term</span></div>
                        </div>
                    </div>

                    @if($calculatedStudentCount > 0)
                        <div class="text-xs text-slate-500 font-semibold">
                            Est. termly: <span class="text-emerald-600 font-bold">{{ config('myacademy.currency_symbol','₦') }}{{ number_format($estimatedTermlyUsageFee, 2) }}</span>
                        </div>
                    @endif

                    <button wire:click="install" wire:loading.attr="disabled" class="btn-primary w-full py-3 flex items-center justify-center gap-2 shadow-sm">
                        <svg wire:loading wire:target="install" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span wire:loading.remove wire:target="install">Install Plugin</span>
                        <span wire:loading wire:target="install">Installing...</span>
                    </button>
                    <p class="text-[10px] text-gray-400">Compatible with MyAcademy v2.0+</p>
                </div>
            </div>
            @else
            <div class="card-padded bg-emerald-50/60 border border-emerald-100 shadow-sm rounded-2xl">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-emerald-800">Active Subscription</div>
                            @if($installPivot?->installed_at)
                                <div class="text-[10px] text-emerald-600 font-medium">Since {{ \Carbon\Carbon::parse($installPivot->installed_at)->format('M j, Y') }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="border-t border-emerald-100/50 pt-2 text-xs text-slate-600 space-y-1">
                        <div>
                            <strong>Active Target Classes:</strong> {{ count($selectedClasses) }}
                        </div>
                        <div>
                            <strong>Students Billed:</strong> {{ number_format($calculatedStudentCount) }}
                        </div>
                        <div>
                            <strong>Est. Usage Fee:</strong> {{ config('myacademy.currency_symbol','₦') }}{{ number_format($estimatedTermlyUsageFee, 2) }} / term
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
