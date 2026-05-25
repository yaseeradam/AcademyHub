<div class="mx-auto max-w-5xl space-y-8 pb-12">
    <div>
        <h1 class="text-3xl font-black tracking-tight text-slate-900">Subscription & Billing</h1>
        <p class="mt-2 text-lg text-slate-600">Manage your school's usage, add-ons, and subscription plan.</p>
    </div>

    <!-- Core Subscription Card -->
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 bg-slate-50/50 p-6 sm:flex sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Core Subscription</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Base school management system containing students, results, fees, and attendance.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:text-right">
                <div class="text-3xl font-black text-slate-900">₦1,000<span class="text-base font-normal text-slate-500"> / student / yr</span></div>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between rounded-xl bg-blue-50/50 p-4 ring-1 ring-blue-100">
                <div class="flex items-center gap-4">
                    <div class="grid h-12 w-12 place-items-center rounded-lg bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-600">Current Student Count</div>
                        <div class="text-xl font-black text-slate-900">{{ number_format($studentCount) }} Students</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-semibold text-slate-600">Yearly Base Cost</div>
                    <div class="text-2xl font-black text-slate-900">₦{{ number_format($this->coreCost) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Based & Checkout Summary -->
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Broadcast Features summary -->
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
            <h3 class="text-lg font-bold text-slate-900">Broadcast Messaging</h3>
            <p class="mt-1 text-sm text-slate-500">Send unlimited messages configured with your own number.</p>
            
            <div class="mt-6 flex flex-col gap-4">
                <div class="flex items-center justify-between rounded-xl {{ $whatsapp ? 'bg-green-50 ring-green-200' : 'bg-slate-50 ring-slate-200' }} p-4 ring-1">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg {{ $whatsapp ? 'bg-green-500' : 'bg-slate-400' }} p-2 text-white shadow-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4l3 3" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold {{ $whatsapp ? 'text-green-900' : 'text-slate-900' }}">WhatsApp Broadcasts</div>
                            <div class="text-xs {{ $whatsapp ? 'text-green-700' : 'text-slate-600' }}">Unlimited bulk messaging to parents and staff</div>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($whatsapp)
                            <div class="font-bold text-green-600">Free <span class="text-xs font-normal text-green-700">included with Bot</span></div>
                            <button type="button" class="mt-1 text-sm font-semibold text-green-700 hover:text-green-800">Configure Device</button>
                        @else
                            <div class="font-bold text-slate-500">Requires WA Bot Pro</div>
                            <a href="{{ route('marketplace.show', 'whatsapp-bot') }}" class="mt-1 inline-block text-sm font-semibold text-slate-600 hover:text-slate-900">View in Marketplace</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-xl">
            <h3 class="text-lg font-bold">Estimated Yearly Bill</h3>
            <p class="mt-1 text-sm text-slate-400">Next billing cycle: {{ now()->addYear()->format('M Y') }}</p>
            
            <div class="mt-6 flex flex-col gap-3 border-y border-slate-700 py-4 text-sm font-medium text-slate-300">
                <div class="flex justify-between">
                    <span>Base ({{ number_format($studentCount) }} students)</span>
                    <span>₦{{ number_format($this->coreCost) }}</span>
                </div>
                @if($whatsapp)
                <div class="flex justify-between text-green-400">
                    <span>WA Bot Pro</span>
                    <span>₦{{ number_format($studentCount * 300) }}</span>
                </div>
                @endif
                @if($cbt)
                <div class="flex justify-between text-violet-400">
                    <span>CBT Pro</span>
                    <span>₦{{ number_format($studentCount * 200) }}</span>
                </div>
                @endif
                @if($parent_app)
                <div class="flex justify-between text-pink-400">
                    <span>Parent App+</span>
                    <span>₦{{ number_format($studentCount * 150) }}</span>
                </div>
                @endif
            </div>
            
            <div class="mt-6">
                <div class="flex items-end justify-between">
                    <span class="text-sm font-medium text-slate-400">Total Yearly</span>
                    <span class="text-3xl font-black">₦{{ number_format($this->totalCost) }}</span>
                </div>
                <button type="button" wire:click="payNow" wire:loading.attr="disabled" class="mt-6 w-full rounded-xl bg-amber-500 p-3 text-center text-sm font-bold text-slate-900 shadow hover:bg-amber-400 transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="payNow">Pay Now / Renew</span>
                    <span wire:loading wire:target="payNow">Initializing...</span>
                </button>
            </div>
        </div>
    </div>
</div>

@assets
<script src="https://js.paystack.co/v1/inline.js" defer></script>
@endassets

@script
    $wire.on('initialize-paystack', (eventData) => {
        let data = Array.isArray(eventData) ? eventData[0] : eventData;
        let handler = PaystackPop.setup({
            key: '{{ env('PAYSTACK_PUBLIC_KEY', 'pk_test_') }}', // Handled by standard env
            email: data.email,
            amount: data.amount, // in kobo
            ref: data.ref,
            currency: 'NGN',
            callback: function(response) {
                // Confirm payment success with Livewire component
                $wire.verifyPayment(response.reference);
            },
            onClose: function() {
                // Optional: alert('Transaction cancelled');
            }
        });
        handler.openIframe();
    });
@endscript
