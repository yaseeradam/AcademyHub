{{-- All modal state lives in Alpine.js — zero server round-trips for open/close --}}
<div
    class="space-y-6"
    x-data="{
        show: false,
        processing: false,
        component: null,
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
    <x-page-header title="Marketplace" subtitle="Extend your school's capabilities with powerful add-ons." accent="marketplace">
        <x-slot:actions>
            <a href="{{ route('more-features') }}" class="btn-outline">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Network toast (offline / slow) — teleported to body --}}
    <template x-teleport="body">
        <div
            id="network-toast-container"
            class="fixed top-5 right-5 z-[99999] flex flex-col gap-2 pointer-events-none"
        ></div>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($components as $component)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col transition-all hover:shadow-md">
                <div class="p-6 flex-1">
                    <a href="{{ route('marketplace.show', $component->slug) }}" wire:navigate class="block group/title">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mb-4 group-hover/title:bg-indigo-100 transition-colors">
                            {!! $component->icon ?: '📦' !!}
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover/title:text-indigo-600 transition-colors">{{ $component->name }}</h3>
                    </a>
                    <p class="text-sm text-gray-500 line-clamp-3">{{ $component->description }}</p>
                    <div class="mt-4">
                        @if($component->price > 0)
                            <span class="text-lg font-black text-gray-900">{{ config('myacademy.currency_symbol', '₦') }}{{ number_format($component->price, 2) }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 uppercase tracking-wide">Free</span>
                        @endif
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    @if(in_array($component->id, $installed))
                        <a href="{{ route('marketplace.show', $component->slug) }}" wire:navigate class="w-full inline-flex items-center justify-center py-2 px-4 rounded-lg text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                            Manage Plugin →
                        </a>
                    @else
                        {{-- Pure Alpine click — instant, no server call --}}
                        <button
                            @click="open({
                                id: {{ $component->id }},
                                name: @js($component->name),
                                icon: @js($component->icon ?: '📦'),
                                price: {{ $component->price }},
                                priceFormatted: @js(config('myacademy.currency_symbol', '₦') . number_format($component->price, 2))
                            })"
                            :disabled="processing"
                            class="w-full py-2 px-4 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Install Plugin
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-gray-200 border-dashed">
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
            <div class="bg-white rounded-2xl px-8 py-6 shadow-2xl flex items-center gap-4">
                <svg class="animate-spin h-6 w-6 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <div>
                    <p class="font-semibold text-gray-900">Processing…</p>
                    <p class="text-sm text-gray-500">Please wait, installing your plugin.</p>
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
                class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl"
                @click.stop
            >
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mb-4" x-text="component?.icon"></div>

                <h3 class="text-xl font-bold text-gray-900 mb-2">Confirm Installation</h3>

                <p class="text-gray-600 mb-6">
                    <template x-if="component?.price > 0">
                        <span>
                            This is a paid plugin costing
                            <strong class="text-gray-900" x-text="component?.priceFormatted"></strong>.
                            Clicking "Proceed" will open a secure Paystack payment window.
                        </span>
                    </template>
                    <template x-if="!component?.price">
                        <span>This is a free plugin. It will be added to your sidebar immediately after installing.</span>
                    </template>
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        @click="close()"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors focus:outline-none"
                    >
                        Cancel
                    </button>

                    {{-- Free install button --}}
                    <button
                        x-show="!component?.price"
                        @click="handleInstall()"
                        :disabled="processing"
                        class="relative inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 transition-colors focus:outline-none"
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
                        class="relative inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 transition-colors focus:outline-none"
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