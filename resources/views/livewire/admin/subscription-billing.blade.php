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

    <!-- Marketplace Add-ons -->
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Marketplace Add-ons</h2>
        <p class="mt-1 text-sm text-slate-600">Supercharge your school with powerful integrations.</p>
        
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            <!-- WhatsApp Bot -->
            <div class="relative flex flex-col justify-between overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 transition-all {{ $whatsapp ? 'ring-2 ring-green-500' : 'ring-slate-200' }}">
                <div>
                    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg text-white">
                        <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">WhatsApp Bot Pro</h3>
                    <p class="mt-2 text-sm text-slate-600">Automated results, attendance updates, and AI chat for parents.</p>
                    <div class="mt-4 font-bold text-slate-900">₦300 <span class="text-sm font-normal text-slate-500">/ student / yr</span></div>
                </div>
                <a href="{{ route('marketplace.show', 'whatsapp-bot') }}" class="mt-6 block text-center w-full rounded-xl py-2.5 text-sm font-bold transition-all bg-slate-900 text-white hover:bg-slate-800">
                    View in Marketplace
                </a>
            </div>

            <!-- CBT Pro -->
            <div class="relative flex flex-col justify-between overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 transition-all {{ $cbt ? 'ring-2 ring-violet-500' : 'ring-slate-200' }}">
                <div>
                    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 shadow-lg text-white">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">CBT Pro</h3>
                    <p class="mt-2 text-sm text-slate-600">Conduct computerized exams with AI grading and analytics.</p>
                    <div class="mt-4 font-bold text-slate-900">₦200 <span class="text-sm font-normal text-slate-500">/ student / yr</span></div>
                </div>
                <a href="{{ route('marketplace.show', 'cbt') }}" class="mt-6 block text-center w-full rounded-xl py-2.5 text-sm font-bold transition-all bg-slate-900 text-white hover:bg-slate-800">
                    View in Marketplace
                </a>
            </div>

            <!-- Parent App+ -->
            <div class="relative flex flex-col justify-between overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 transition-all {{ $parent_app ? 'ring-2 ring-pink-500' : 'ring-slate-200' }}">
                <div>
                    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg text-white">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2" stroke-width="2" />
                            <line x1="12" y1="18" x2="12" y2="18" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Parent App+</h3>
                    <p class="mt-2 text-sm text-slate-600">Dedicated mobile portal for parents to track their wards.</p>
                    <div class="mt-4 font-bold text-slate-900">₦150 <span class="text-sm font-normal text-slate-500">/ student / yr</span></div>
                </div>
                <a href="{{ route('marketplace.show', 'student-dashboard') }}" class="mt-6 block text-center w-full rounded-xl py-2.5 text-sm font-bold transition-all bg-slate-900 text-white hover:bg-slate-800">
                    View in Marketplace
                </a>
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
