@php
    $getIconSvg = function($icon, $slug) {
        $icons = [
            'whatsapp' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>',
            'whatsapp-bot' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>',
            'student' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
                </svg>',
            'student-dashboard' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
                </svg>',
            'parent-portal' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>',
            'exam' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>',
            'cbt' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>',
            'finance' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>',
            'savings-loan' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>',
            'messages' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>',
            'document' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>',
            'homework' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>',
            'e-learning' => '
                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>',
        ];

        $key = isset($icons[$icon]) ? $icon : (isset($icons[$slug]) ? $slug : 'default');
        return $icons[$key] ?? '
            <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>';
    };

    $getIconBackground = function($slug) {
        $gradients = [
            'whatsapp' => 'from-emerald-400 to-green-500',
            'whatsapp-bot' => 'from-emerald-400 to-green-500',
            'student' => 'from-indigo-500 to-indigo-600',
            'student-dashboard' => 'from-indigo-500 to-indigo-600',
            'messages' => 'from-amber-400 to-orange-500',
            'homework' => 'from-sky-400 to-blue-500',
            'document' => 'from-sky-400 to-blue-500',
            'cbt' => 'from-purple-400 to-indigo-600',
            'exam' => 'from-purple-400 to-indigo-600',
            'savings-loan' => 'from-teal-400 to-emerald-500',
            'finance' => 'from-teal-400 to-emerald-500',
            'parent-portal' => 'from-pink-400 to-rose-500',
        ];
        return $gradients[$slug] ?? 'from-gray-400 to-slate-500';
    };

    $getBadgeColor = function($slug) {
        $badges = [
            'whatsapp' => 'bg-green-100 text-green-800',
            'whatsapp-bot' => 'bg-green-100 text-green-800',
            'student' => 'bg-blue-100 text-blue-800',
            'student-dashboard' => 'bg-blue-100 text-blue-800',
            'messages' => 'bg-amber-100 text-amber-800',
            'homework' => 'bg-sky-100 text-sky-800',
            'document' => 'bg-sky-100 text-sky-800',
            'cbt' => 'bg-purple-100 text-purple-800',
            'exam' => 'bg-purple-100 text-purple-800',
            'savings-loan' => 'bg-emerald-100 text-emerald-800',
            'finance' => 'bg-emerald-100 text-emerald-800',
            'parent-portal' => 'bg-pink-100 text-pink-800',
        ];
        return $badges[$slug] ?? 'bg-gray-100 text-gray-800';
    };
@endphp

<div
    class="space-y-6"
    x-data="{
        show: false,
        processing: false,
        component: null,
        activeTab: 'all',
        installedIds: @js($installed),
        open(data) { this.component = data; this.show = true; },
        close() { if (this.processing) return; this.show = false; this.component = null; },

        async handleInstall() {
            if (!navigator.onLine) {
                window.dispatchEvent(new CustomEvent('network-toast', { detail: { msg: 'You are offline. Please check your connection.', type: 'error' } }));
                return;
            }
            this.processing = true;
            this.show = false;
            try {
                await $wire.confirmInstall(this.component.id);
            } catch(e) {
                window.dispatchEvent(new CustomEvent('network-toast', { detail: { msg: 'Request failed. Please check your connection.', type: 'error' } }));
                this.processing = false;
            }
        },

        async handlePayment() {
            if (!navigator.onLine) {
                window.dispatchEvent(new CustomEvent('network-toast', { detail: { msg: 'You are offline. Please check your connection.', type: 'error' } }));
                return;
            }
            this.processing = true;
            this.show = false;
            try {
                await $wire.startPayment(this.component.id);
            } catch(e) {
                window.dispatchEvent(new CustomEvent('network-toast', { detail: { msg: 'Request failed. Please check your connection.', type: 'error' } }));
                this.processing = false;
            }
        }
    }"
    @keydown.escape.window="close()"
>
    {{-- Premium Light Mode Header Card --}}
    <div class="relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- Left: Logo and Descriptions --}}
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-slate-900 text-white rounded-xl flex items-center justify-center shadow-lg shadow-slate-900/10">
                <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-900 leading-none">Marketplace</h1>
                <p class="text-xs sm:text-sm font-semibold text-gray-500 mt-1">Discover and install premium modules and extensions.</p>
            </div>
        </div>
        
        {{-- Right: Rounded Back button --}}
        <a href="{{ route('more-features') }}" class="px-6 py-2 bg-white border border-gray-200 hover:bg-gray-50 rounded-2xl text-sm font-bold text-gray-800 shadow-sm transition-all duration-200 self-end sm:self-auto text-center min-w-[90px]">
            Back
        </a>
    </div>

    {{-- Clean Light Category Filters --}}
    <div class="flex flex-wrap items-center gap-2 pb-2">
        <button
            @click="activeTab = 'all'"
            :class="activeTab === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200'"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200"
        >
            All Plugins
            <span :class="activeTab === 'all' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'" class="rounded-full px-2 py-0.5 text-[10px] font-black">{{ count($components) }}</span>
        </button>
        
        <button
            @click="activeTab = 'installed'"
            :class="activeTab === 'installed' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200'"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200"
        >
            Installed
            <span :class="activeTab === 'installed' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'" class="rounded-full px-2 py-0.5 text-[10px] font-black">{{ count($installed) }}</span>
        </button>

        <button
            @click="activeTab = 'free'"
            :class="activeTab === 'free' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200'"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200"
        >
            Free
            <span :class="activeTab === 'free' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'" class="rounded-full px-2 py-0.5 text-[10px] font-black">{{ $components->where('price', 0)->count() }}</span>
        </button>

        <button
            @click="activeTab = 'paid'"
            :class="activeTab === 'paid' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200'"
            class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all duration-200"
        >
            Premium
            <span :class="activeTab === 'paid' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'" class="rounded-full px-2 py-0.5 text-[10px] font-black">{{ $components->where('price', '>', 0)->count() }}</span>
        </button>
    </div>

    {{-- Featured Products Title Row --}}
    <div class="flex items-center justify-between border-t border-gray-100 pt-6">
        <h2 class="text-lg font-black text-gray-900 tracking-tight">Featured Products</h2>
        <span class="text-xs font-semibold text-gray-400">{{ count($components) }} products available</span>
    </div>

    {{-- Clean Light-Theme Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($components as $component)
            @php
                $isInstalled = in_array($component->id, $installed);
                $gradientClass = $getIconBackground($component->slug);
                $badgeStyle = $getBadgeColor($component->slug);
            @endphp
            <div
                x-show="activeTab === 'all' || 
                        (activeTab === 'installed' && installedIds.includes({{ $component->id }})) || 
                        (activeTab === 'free' && {{ $component->price == 0 ? 'true' : 'false' }}) || 
                        (activeTab === 'paid' && {{ $component->price > 0 ? 'true' : 'false' }})"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="group bg-white rounded-[1.5rem] border border-gray-100 shadow-sm hover:border-blue-400 hover:ring-2 hover:ring-blue-500/10 hover:shadow-md transition-all duration-300 flex p-5 cursor-pointer"
                @if($isInstalled)
                    onclick="window.location.href='{{ route('marketplace.show', $component->slug) }}'"
                @else
                    @click="open({
                        id: {{ $component->id }},
                        name: @js($component->name),
                        icon: @js($component->icon ?: '📦'),
                        price: {{ $component->price }},
                        priceFormatted: @js(config('myacademy.currency_symbol', '₦') . number_format($component->price, 2))
                    })"
                @endif
            >
                <div class="flex items-start gap-5 w-full">
                    {{-- App Icon gradient --}}
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $gradientClass }} flex items-center justify-center flex-shrink-0 shadow-sm select-none">
                        {!! $getIconSvg($component->icon, $component->slug) !!}
                    </div>

                    {{-- Card Info Columns --}}
                    <div class="flex-1 min-w-0 flex flex-col justify-between h-full">
                        {{-- Top Title & Rating --}}
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-base font-extrabold text-gray-900 tracking-tight leading-snug group-hover:text-blue-600 transition-colors truncate">
                                {{ $component->name }}
                            </h3>
                            
                            <div class="flex items-center text-amber-500 flex-shrink-0 gap-0.5">
                                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.9 1.603-.9 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.9-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-xs font-black text-amber-500 leading-none">{{ number_format($component->rating_avg ?: 4.5, 1) }}</span>
                            </div>
                        </div>

                        {{-- Middle Description --}}
                        <p class="text-xs text-gray-500 font-medium leading-relaxed mt-1 mb-3 line-clamp-2">
                            {{ $component->short_description ?: $component->description }}
                        </p>

                        {{-- Bottom badge capsule & downloads --}}
                        <div class="flex items-center justify-between mt-auto pt-0.5">
                            <div class="flex items-center gap-2">
                                @if($isInstalled)
                                    <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        Installed
                                    </span>
                                @elseif($component->price > 0)
                                    <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($component->price, 0) }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider {{ $badgeStyle }}">
                                        FREE
                                    </span>
                                @endif

                                <span class="text-[10px] font-bold text-gray-400 capitalize">
                                    {{ $component->category }}
                                </span>
                            </div>

                            <span class="text-[10px] font-semibold text-gray-400">
                                {{ $component->installs ?: 0 }} Installs
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-gray-50 rounded-3xl border border-gray-200 border-dashed">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-4 text-sm font-extrabold text-gray-900">No plugins available</h3>
                <p class="mt-1 text-xs text-gray-400 font-medium">Check back later for new marketplace add-ons.</p>
            </div>
        @endforelse
    </div>

    {{-- Premium Glassmorphic Configuration Spinner Overlay --}}
    <template x-teleport="body">
        <div
            x-show="processing"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 w-screen h-screen bg-slate-950/45 backdrop-blur-sm flex items-center justify-center z-[9998]"
            style="display:none"
        >
            <div class="bg-white/90 rounded-[2rem] px-10 py-8 shadow-2xl flex flex-col items-center text-center gap-4 border border-white/60 max-w-sm w-full mx-4 backdrop-blur-md">
                <div class="relative flex items-center justify-center h-16 w-16">
                     <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
                     <div class="absolute inset-0 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
                     <svg class="h-6 w-6 text-blue-600 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5h10.5" />
                     </svg>
                </div>
                <div class="space-y-1">
                    <p class="text-lg font-black text-slate-900 leading-tight">Configuring Plugin</p>
                    <p class="text-xs font-bold text-slate-500 max-w-[240px] leading-relaxed">Setting up components, routing gates, and resources for your school...</p>
                </div>
            </div>
        </div>
    </template>

    {{-- Acquisition Confirmation Modal (Light theme) --}}
    <template x-teleport="body">
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 w-screen h-screen bg-slate-950/40 backdrop-blur-sm flex items-center justify-center z-[9999]"
            @click.self="close()"
            style="display:none"
        >
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                class="bg-white rounded-[2rem] overflow-hidden max-w-md w-full mx-4 shadow-2xl border border-gray-100 text-left"
                @click.stop
            >
                {{-- Modal Header Banner --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8 text-white relative">
                    <div class="absolute right-0 top-0 bottom-0 w-32 bg-white/5 skew-x-[-20deg] translate-x-16"></div>
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 text-white flex items-center justify-center text-3xl font-bold border border-white/20 backdrop-blur-md" x-text="component?.icon"></div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-blue-200/90 leading-none">Confirm Acquisition</span>
                            <h3 class="text-xl font-black leading-tight mt-1" x-text="component?.name"></h3>
                        </div>
                    </div>
                </div>

                {{-- Modal Content --}}
                <div class="p-6">
                    <p class="text-sm text-gray-500 font-semibold leading-relaxed mb-6">
                        <template x-if="component?.price > 0">
                            <span>
                                This is a premium plugin costing
                                <strong class="text-blue-600 font-black" x-text="component?.priceFormatted"></strong>.
                                Proceeding will securely open a Paystack checkout gateway to finalize your payment transaction.
                            </span>
                        </template>
                        <template x-if="!component?.price">
                            <span>This is a free plugin. Installing it will instantly register and mount its components into your school portal's dashboard navigation.</span>
                        </template>
                    </p>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                         <button
                             @click="close()"
                             class="px-5 py-2.5 bg-gray-100 hover:bg-gray-250 rounded-xl text-xs font-bold text-gray-650 transition-colors focus:outline-none"
                         >
                             Cancel
                         </button>

                         {{-- Free install button --}}
                         <button
                             x-show="!component?.price"
                             @click="handleInstall()"
                             :disabled="processing"
                             class="relative inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-black text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-60 transition-all focus:outline-none shadow-md shadow-blue-500/20 active:scale-95"
                         >
                             <span x-text="processing ? 'Installing…' : 'Install Now'"></span>
                         </button>

                         {{-- Paid payment button --}}
                         <button
                             x-show="component?.price > 0"
                             @click="handlePayment()"
                             :disabled="processing"
                             class="relative inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-black text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-60 transition-all focus:outline-none shadow-md shadow-blue-500/20 active:scale-95"
                         >
                             <span x-text="processing ? 'Connecting…' : 'Proceed to Checkout'"></span>
                         </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
        <script src="https://js.paystack.co/v1/inline.js"></script>
        <script>
            // ── Network toast helper ──────────────────────────────────────────
            function showNetworkToast(msg, type = 'error') {
                const container = document.getElementById('network-toast-container');
                if (!container) return;

                const colorMap = {
                    error:   'bg-red-600 text-white',
                    warning: 'bg-amber-500 text-white',
                    success: 'bg-green-600 text-white',
                };
                const colors = colorMap[type] ?? colorMap.error;

                const toast = document.createElement('div');
                toast.className = `pointer-events-auto flex items-center gap-3 rounded-xl px-4 py-3 shadow-xl text-xs font-semibold ${colors} transition-all duration-300 opacity-0 translate-y-2`;
                toast.innerHTML = `
                    <svg class="h-4.5 w-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                    </svg>
                    <span>${msg}</span>`;

                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateY(0)';
                });

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-8px)';
                    setTimeout(() => toast.remove(), 300);
                }, 4500);
            }

            // Listen for custom network toast events (from Alpine methods)
            window.addEventListener('network-toast', e => {
                showNetworkToast(e.detail.msg, e.detail.type ?? 'error');
            });

            // ── Offline / back-online detection ──────────────────────────────
            window.addEventListener('offline', () => {
                showNetworkToast('You are offline. Please check your internet connection.', 'error');
            });

            window.addEventListener('online', () => {
                showNetworkToast('Back online!', 'success');
            });

            // ── Slow connection detection (warn if request takes > 5s) ───────
            let slowTimer = null;
            document.addEventListener('livewire:request', () => {
                slowTimer = setTimeout(() => {
                    showNetworkToast('Slow connection detected. This may take a moment…', 'warning');
                }, 5000);
            });
            document.addEventListener('livewire:response', () => {
                clearTimeout(slowTimer);
            });

            // ── Paystack popup ────────────────────────────────────────────────
            window.addEventListener('open-paystack', event => {
                const data = event.detail[0] ?? event.detail;

                // If offline, bail immediately
                if (!navigator.onLine) {
                    showNetworkToast('You are offline. Cannot open payment window.', 'error');
                    return;
                }

                PaystackPop.setup({
                    key: data.key,
                    email: data.email,
                    amount: data.amount,
                    ref: data.ref,
                    metadata: {
                        custom_fields: [{
                            display_name: 'Component ID',
                            variable_name: 'component_id',
                            value: data.component_id
                        }]
                    },
                    callback: response => {
                        @this.call('verifyPayment', response.reference);
                    },
                    onClose: () => {
                        // Reset processing state
                        const el = document.querySelector('[x-data]');
                        if (el?._x_dataStack?.[0]) {
                            el._x_dataStack[0].processing = false;
                        }
                        showNetworkToast('Payment cancelled. No charge was made.', 'warning');
                    }
                }).openIframe();
            });
        </script>
    @endpush
</div>