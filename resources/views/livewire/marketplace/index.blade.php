<div
    class="space-y-6 font-sans"
    x-data="{
        show: false,
        processing: false,
        component: null,
        activeCategory: 'All',
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
    {{-- Custom App Store Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-5">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Marketplace</h1>
            <p class="mt-1 text-sm text-gray-500 font-medium">Extend your school's capabilities with curated administrative apps</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('more-features') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50">
                <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </div>
    </div>

    {{-- Network toast (offline / slow) — teleported to body --}}
    <template x-teleport="body">
        <div
            id="network-toast-container"
            class="fixed top-5 right-5 z-[99999] flex flex-col gap-2 pointer-events-none"
        ></div>
    </template>

    {{-- 1. APP STORE FEATURED HERO BANNER --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-950 to-slate-900 shadow-xl min-h-[220px] flex flex-col md:flex-row items-center justify-between p-7 lg:p-9">
        {{-- Subtle background stars/dots inline --}}
        <div class="absolute inset-0 pointer-events-none opacity-20 mix-blend-screen bg-[radial-gradient(circle,#ffffff_1.5px,transparent_1.5px)]" style="background-size: 32px 32px;"></div>

        {{-- Left Content --}}
        <div class="relative z-10 w-full md:w-3/5 space-y-3">
            <div class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/20 border border-indigo-400/30 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-indigo-300">
                🔥 App of the Month
            </div>
            <h2 class="text-3xl lg:text-4xl font-black text-white tracking-tight leading-[1.1]">
                Parent Portal
            </h2>
            <p class="text-sm font-semibold text-indigo-200 max-w-lg leading-relaxed">
                Empower your parents. Grant them secure, real-time portal access to examine assignments, homework, results, and pay outstanding fee structures instantly.
            </p>
            <div class="pt-2 flex items-center gap-4">
                <a href="{{ route('marketplace.show', 'parent-portal') }}" 
                   class="inline-flex items-center gap-2 rounded-xl bg-white hover:bg-indigo-50 text-indigo-950 font-bold text-xs px-5 py-2.5 shadow-md transition-all">
                    View Details
                </a>
                <span class="text-xs font-bold text-indigo-300">4.95 ★★★★★ (42 Reviews)</span>
            </div>
        </div>

        {{-- Right Content: Simulated phone --}}
        <div class="relative z-10 w-full md:w-2/5 flex items-center justify-end py-6 md:py-0">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl border border-white/10 p-5 shadow-xl w-full max-w-[280px]">
                <div class="flex items-center gap-3.5 border-b border-white/10 pb-3 mb-3">
                    <div class="h-10 w-10 rounded-xl bg-blue-500 flex items-center justify-center text-white shadow shadow-blue-500/30 font-bold text-lg">
                        👤
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-white leading-tight">Secure Access</h4>
                        <p class="text-[10px] text-indigo-200 mt-0.5">Dual Authentication portal</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="h-2 w-3/4 bg-white/20 rounded"></div>
                    <div class="h-2 w-full bg-white/10 rounded"></div>
                    <div class="h-2 w-5/6 bg-white/10 rounded"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. APP STORE CATEGORY FILTER ROW --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <template x-for="cat in ['All', 'Education', 'Communication', 'Finance', 'Portal', 'Examination']">
            <button
                @click="activeCategory = cat"
                :class="activeCategory === cat ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100 border-indigo-600' : 'bg-white text-gray-600 hover:bg-gray-50 border-gray-200'"
                class="px-4.5 py-2 rounded-full border text-xs font-bold transition-all whitespace-nowrap"
                x-text="cat"
            ></button>
        </template>
    </div>

    {{-- 3. APP GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($components as $component)
            @php
                $color = match($component->slug) {
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
            
            <div
                x-show="activeCategory === 'All' || activeCategory === '{{ $component->category }}' || (activeCategory === 'Education' && '{{ $component->category }}' === 'Examination')"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden flex flex-col transition-all hover:shadow-lg hover:-translate-y-0.5 group"
            >
                <div class="p-5 flex-1 flex gap-4">
                    {{-- App Icon left (App store styled) --}}
                    <a href="{{ route('marketplace.show', $component->slug) }}" wire:navigate class="flex-shrink-0">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $color }} text-white flex items-center justify-center text-3xl shadow-md transition-all group-hover:scale-95 duration-300 relative overflow-hidden">
                            {{-- Glossy finish layer --}}
                            <div class="absolute inset-0 bg-gradient-to-b from-white/20 to-transparent pointer-events-none"></div>
                            
                            @if($component->icon === 'whatsapp')
                                <svg class="h-8 w-8 text-white fill-current" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.457 5.709 1.458h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            @elseif($component->icon === 'student')
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                            @elseif($component->icon === 'exam')
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            @elseif($component->icon === 'finance')
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif($component->icon === 'messages')
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            @elseif($component->icon === 'document')
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @else
                                <span class="text-3xl">{!! $component->icon ?: '📦' !!}</span>
                            @endif
                        </div>
                    </a>

                    {{-- Card Details --}}
                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                        <div>
                            <a href="{{ route('marketplace.show', $component->slug) }}" wire:navigate class="block group-hover:text-indigo-600 transition-colors">
                                <h3 class="text-[15px] font-black text-gray-900 tracking-tight line-clamp-1 leading-snug">{{ $component->name }}</h3>
                            </a>
                            <p class="text-[11px] font-semibold text-gray-400 mt-0.5">{{ $component->category }}</p>
                            <p class="text-xs text-gray-500 line-clamp-2 mt-1.5 leading-relaxed">{{ $component->short_description ?: $component->description }}</p>
                        </div>
                        
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-[11px] font-extrabold text-slate-700 flex items-center gap-1">
                                <span class="text-amber-400">★</span> {{ number_format($component->rating_avg ?: 4.8, 1) }}
                                <span class="text-slate-400 font-semibold">({{ number_format($component->installs ?: 10) }}k installs)</span>
                            </span>

                            @if(in_array($component->id, $installed))
                                <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3.5 py-1.5 rounded-full uppercase tracking-wider cursor-default shadow-sm border border-emerald-100">
                                    Active
                                </span>
                            @else
                                <button
                                    @click="open({
                                        id: {{ $component->id }},
                                        name: @js($component->name),
                                        icon: @js($component->icon ?: '📦'),
                                        price: {{ $component->price }},
                                        priceFormatted: @js(config('myacademy.currency_symbol', '₦') . number_format($component->price, 2))
                                    })"
                                    :disabled="processing"
                                    :class="component?.price > 0 ? 'bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white' : 'bg-[#f1f3f9] text-[#007aff] hover:bg-[#007aff] hover:text-white'"
                                    class="text-[10px] font-black px-4 py-1.5 rounded-full transition-all tracking-wider uppercase inline-flex items-center justify-center min-w-[70px]"
                                >
                                    @if($component->price > 0)
                                        ₦{{ number_format($component->price) }}
                                    @else
                                        GET
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-gray-200 border-dashed shadow-sm">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No plugins available</h3>
                <p class="mt-1 text-sm text-gray-500">Check back later for new marketplace add-ons.</p>
            </div>
        @endforelse
    </div>

    {{-- Global processing overlay (shown while Livewire processes) --}}
    <template x-teleport="body">
        <div
            x-show="processing"
            class="fixed inset-0 w-screen h-screen bg-black/40 backdrop-blur-sm flex items-center justify-center z-[9998]"
            style="display:none"
        >
            <div class="bg-white rounded-3xl px-8 py-6 shadow-2xl flex items-center gap-4">
                <svg class="animate-spin h-6 w-6 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <div>
                    <p class="font-bold text-gray-900">Processing…</p>
                    <p class="text-sm text-gray-500">Connecting to server and launching module...</p>
                </div>
            </div>
        </div>
    </template>

    {{-- Confirmation modal — teleported to <body> --}}
    <template x-teleport="body">
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 w-screen h-screen bg-black/60 backdrop-blur-md flex items-center justify-center z-[9999]"
            @click.self="close()"
            style="display:none"
        >
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-3xl p-6 max-w-md w-full mx-4 shadow-2xl"
                @click.stop
            >
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mb-4" x-text="component?.icon"></div>

                <h3 class="text-xl font-bold text-gray-900 mb-2">Confirm Installation</h3>

                <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                    <template x-if="component?.price > 0">
                        <span>
                            This is a premium plugin costing
                            <strong class="text-gray-900" x-text="component?.priceFormatted"></strong>.
                            Clicking "Proceed" will open a secure Paystack checkout portal.
                        </span>
                    </template>
                    <template x-if="!component?.price">
                        <span>This is a free plugin. It will be added to your sidebar immediately after installing.</span>
                    </template>
                </p>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <button
                        @click="close()"
                        class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors focus:outline-none"
                    >
                        Cancel
                    </button>

                    {{-- Free install button --}}
                    <button
                        x-show="!component?.price"
                        @click="handleInstall()"
                        :disabled="processing"
                        class="relative inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[#007aff] hover:bg-blue-600 disabled:opacity-60 transition-colors focus:outline-none"
                    >
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" x-show="processing">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="processing ? 'Installing…' : 'Install Now'"></span>
                    </button>

                    {{-- Paid payment button --}}
                    <button
                        x-show="component?.price > 0"
                        @click="handlePayment()"
                        :disabled="processing"
                        class="relative inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 transition-colors focus:outline-none"
                    >
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" x-show="processing">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="processing ? 'Connecting…' : 'Proceed to Payment'"></span>
                    </button>
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
                toast.className = `pointer-events-auto flex items-center gap-3 rounded-xl px-4 py-3 shadow-xl text-sm font-medium ${colors} transition-all duration-300 opacity-0 translate-y-2`;
                toast.innerHTML = `
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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