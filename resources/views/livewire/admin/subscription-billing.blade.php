@php
    $iconSvgs = [
        'whatsapp' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>',
        'whatsapp-bot' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>',
        'student' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
            </svg>',
        'student-dashboard' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
            </svg>',
        'parent-portal' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>',
        'exam' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>',
        'cbt' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>',
        'finance' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>',
        'savings-loan' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>',
        'messages' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>',
        'document' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>',
        'homework' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>',
        'e-learning' => '
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            </svg>',
    ];
@endphp

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
        <!-- Active Plugins list -->
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 font-sans">Active Plugins & Add-ons</h3>
                    <p class="mt-1 text-sm text-slate-500">Your school's active custom marketplace extensions and their license costs.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                    {{ count($this->activePlugins) }} Installed
                </span>
            </div>
            
            @if(count($this->activePlugins) > 0)
                <div class="mt-6 divide-y divide-slate-100 space-y-6">
                    @foreach($this->activePlugins as $plugin)
                        @php
                            $rawClasses = $plugin->pivot->allowed_class_ids ?? [];
                            $classes = is_string($rawClasses) ? (json_decode($rawClasses, true) ?: []) : (is_array($rawClasses) ? $rawClasses : []);
                            $pluginStudentCount = \App\Models\Student::whereIn('class_id', $classes)->where('status', 'active')->count();
                            $yearlyCost = $this->getPluginYearlyCost($plugin);
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-6 first:pt-0">
                            <div class="flex items-start gap-4">
                                <div class="rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 p-3 text-white shadow-md flex-shrink-0">
                                    @if(isset($iconSvgs[$plugin->slug]))
                                        <div class="h-6 w-6 text-white [&>svg]:w-6 [&>svg]:h-6 [&>svg]:stroke-current">{!! $iconSvgs[$plugin->slug] !!}</div>
                                    @elseif(!empty($plugin->icon) && str_contains($plugin->icon, '<svg'))
                                        <div class="h-6 w-6 text-white [&>svg]:w-6 [&>svg]:h-6 [&>svg]:stroke-current">{!! $plugin->icon !!}</div>
                                    @else
                                        <span class="text-xl">🧩</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-extrabold text-slate-900 text-base">{{ $plugin->name }}</div>
                                    <div class="text-xs font-medium text-slate-500 mt-0.5">{{ $plugin->short_description }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                            Setup Fee: {{ config('myacademy.currency_symbol','₦') }}{{ number_format($plugin->pivot->setup_fee ?? $plugin->setup_fee, 2) }}
                                        </span>
                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-600 ring-1 ring-inset ring-indigo-500/10">
                                            Usage: {{ config('myacademy.currency_symbol','₦') }}{{ number_format($plugin->pivot->usage_fee_per_student ?? $plugin->usage_fee_per_student, 2) }}/std/term
                                        </span>
                                        <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-600 ring-1 ring-inset ring-purple-500/10">
                                            Target: {{ count($classes) }} {{ Str::plural('Class', count($classes)) }} ({{ number_format($pluginStudentCount) }} stds)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left sm:text-right flex-shrink-0">
                                <div class="text-sm font-bold text-slate-400 uppercase tracking-wider text-[10px]">Est. Yearly Cost</div>
                                <div class="text-xl font-black text-slate-900 mt-0.5">
                                    {{ config('myacademy.currency_symbol','₦') }}{{ number_format($yearlyCost, 2) }}
                                </div>
                                <a href="{{ route('marketplace.show', $plugin->slug) }}" class="mt-1.5 inline-block text-xs font-extrabold text-indigo-600 hover:text-indigo-800 transition">
                                    Manage Settings
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 p-8 text-center bg-slate-50/50">
                    <div class="rounded-full bg-slate-100 p-3 text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </div>
                    <h4 class="mt-4 font-bold text-slate-900">No Active Plugins</h4>
                    <p class="mt-1 text-sm text-slate-500 max-w-sm">Enhance your school platform by installing CBT, parent portal, messages, or homework modules.</p>
                    <a href="{{ route('marketplace') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-indigo-700 transition">
                        Browse Marketplace
                    </a>
                </div>
            @endif
        </div>

        <!-- Order Summary -->
        <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-xl">
            <h3 class="text-lg font-bold">Estimated Yearly Bill</h3>
            @php
                $tenant = auth()->user()?->tenant;
                $nextBilling = $tenant?->expires_at 
                    ? $tenant->expires_at->format('M Y') 
                    : now()->addMonths(4)->format('M Y');
            @endphp
            <p class="mt-1 text-sm text-slate-400">Next billing cycle: {{ $nextBilling }}</p>
            
            <div class="mt-6 flex flex-col gap-3 border-y border-slate-700 py-4 text-sm font-medium text-slate-300">
                <div class="flex justify-between">
                    <span>Base ({{ number_format($studentCount) }} students)</span>
                    <span>₦{{ number_format($this->coreCost) }}</span>
                </div>
                @foreach($this->activePlugins as $plugin)
                    @php
                        $yearlyCost = $this->getPluginYearlyCost($plugin);
                    @endphp
                    <div class="flex justify-between text-indigo-300">
                        <span>{{ $plugin->name }}</span>
                        <span>₦{{ number_format($yearlyCost) }}</span>
                    </div>
                @endforeach
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
