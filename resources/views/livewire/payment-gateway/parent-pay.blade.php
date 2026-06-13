<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 relative overflow-hidden font-['Outfit',sans-serif]">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Secure Tuition Payment Center</h1>
            <p class="mt-1 text-sm text-slate-500">Pay school tuition and other outstanding balances directly from your dashboard.</p>
        </div>

        @if($paymentSuccess)
            <!-- TRANSACTION SUCCESS RECEIPT SCREEN -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-8 shadow-lg max-w-lg mx-auto text-center flex flex-col items-center animate-slideIn">
                <div class="h-16 w-16 bg-emerald-50 border border-emerald-500 rounded-full flex items-center justify-center mb-6 text-emerald-500 animate-bounce">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                    </svg>
                </div>
                
                <h2 class="text-2xl font-black text-slate-900 mb-2">Payment Successful!</h2>
                <p class="text-slate-500 text-sm max-w-sm mb-8 leading-relaxed">
                    Thank you! Your transaction has been processed securely. A payment confirmation receipt has been recorded in the school financial ledgers.
                </p>

                <!-- Dynamic Receipt -->
                <div class="bg-slate-50 rounded-2xl border border-slate-100 p-6 w-full text-left space-y-4 mb-6">
                    <div class="text-xs uppercase tracking-widest text-violet-600 font-extrabold border-b border-slate-200/60 pb-3 text-center">
                        Official Digital Receipt
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-slate-200/40 pb-2.5">
                        <span class="text-slate-400 font-semibold">Receipt Number</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $receiptNumber }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-slate-200/40 pb-2.5">
                        <span class="text-slate-400 font-semibold">Date &amp; Time</span>
                        <span class="font-bold text-slate-900">{{ $receiptDate }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-slate-200/40 pb-2.5">
                        <span class="text-slate-400 font-semibold">Payment Category</span>
                        <span class="font-bold text-slate-900">Tuition Fees</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-slate-200/40 pb-2.5">
                        <span class="text-slate-400 font-semibold">Term / Session</span>
                        <span class="font-bold text-slate-900">Term {{ $selectedTerm }} ({{ $selectedSession }})</span>
                    </div>
                    @if($installmentLabel)
                        <div class="flex justify-between items-center text-sm border-b border-slate-200/40 pb-2.5">
                            <span class="text-slate-400 font-semibold">Payment Plan</span>
                            <span class="font-bold text-slate-900">{{ $installmentLabel }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400 font-semibold">Amount Paid</span>
                        <span class="font-black text-emerald-600 text-lg">₦{{ number_format($paymentAmount, 2) }}</span>
                    </div>
                </div>

                <div class="flex gap-4 w-full">
                    <button wire:click="$set('paymentSuccess', false)" class="flex-1 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold py-3 transition duration-200 shadow-md">
                        Done &amp; Close
                    </button>
                </div>
            </div>

        @else
            <!-- MAIN CHECKOUT PORTAL -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Left Panel: Student Selection, Term, Balance & Plan -->
                <div class="space-y-5">

                    <!-- Student + Term Selectors -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-900 mb-6">Select Child &amp; Bill</h2>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Student Beneficiary</label>
                                <select wire:model.live="selectedStudentId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-bold">
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->schoolClass?->name ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Academic Session</label>
                                    <select wire:model.live="selectedSession" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-bold text-sm">
                                        @foreach($sessions as $sess)
                                            <option value="{{ $sess }}">{{ $sess }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Academic Term</label>
                                    <select wire:model.live="selectedTerm" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-bold text-sm">
                                        <option value="1">1st Term</option>
                                        <option value="2">2nd Term</option>
                                        <option value="3">3rd Term</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Balances Card -->
                            <div class="bg-slate-50 rounded-2xl border border-slate-100 p-5 space-y-3">
                                <div class="text-xs uppercase tracking-widest text-slate-400 font-bold border-b border-slate-200 pb-2">
                                    Term {{ $selectedTerm }} ({{ $selectedSession }}) Statement
                                </div>
                                <div class="flex justify-between items-center text-sm font-semibold">
                                    <span class="text-slate-400">Total Allocated Fees</span>
                                    <span class="text-slate-800 font-bold">₦{{ number_format($amount_due, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                                    <span>Paid Tuition to Date</span>
                                    <span class="font-extrabold">₦{{ number_format($amount_paid, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-base pt-2 border-t border-slate-200 border-dashed font-bold">
                                    <span class="text-slate-900">Outstanding Balance</span>
                                    <span class="text-xl font-black bg-gradient-to-r from-violet-600 to-pink-500 bg-clip-text text-transparent">
                                        ₦{{ number_format($outstanding_balance, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Plan Selector -->
                    @if($isGatewayApproved && $outstanding_balance > 0)
                        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-base font-bold text-slate-900">Payment Plan</h2>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-violet-600 bg-violet-50 px-2 py-1 rounded-full border border-violet-200">Set by School</span>
                            </div>

                            <div class="space-y-2">
                                <!-- Full Payment — always available -->
                                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition duration-150
                                    {{ $selectedPlan === 'full' ? 'border-violet-400 bg-violet-50/60 shadow-sm' : 'border-slate-200 hover:border-violet-300 hover:bg-violet-50/30' }}">
                                    <input type="radio" wire:model.live="selectedPlan" value="full" class="h-4 w-4 accent-violet-600">
                                    <div class="flex-1">
                                        <span class="text-sm font-bold text-slate-900">Full Payment</span>
                                        <p class="text-xs text-slate-500 mt-0.5">Pay the entire outstanding balance in one go.</p>
                                    </div>
                                    <span class="text-sm font-extrabold text-violet-700 whitespace-nowrap">
                                        ₦{{ number_format($outstanding_balance, 2) }}
                                    </span>
                                </label>

                                @if(!empty($enabledPlans['two_installments']))
                                    @php
                                        $halfAmt = number_format(round($amount_due / 2, 2), 2);
                                    @endphp
                                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition duration-150
                                        {{ $selectedPlan === 'two_installments' ? 'border-violet-400 bg-violet-50/60 shadow-sm' : 'border-slate-200 hover:border-violet-300 hover:bg-violet-50/30' }}">
                                        <input type="radio" wire:model.live="selectedPlan" value="two_installments" class="h-4 w-4 accent-violet-600">
                                        <div class="flex-1">
                                            <span class="text-sm font-bold text-slate-900">2 Installments</span>
                                            <p class="text-xs text-slate-500 mt-0.5">Pay in 2 equal halves — beginning &amp; mid-term.</p>
                                        </div>
                                        <span class="text-sm font-extrabold text-violet-700 whitespace-nowrap">
                                            ₦{{ $halfAmt }}/half
                                        </span>
                                    </label>
                                @endif

                                @if(!empty($enabledPlans['monthly']))
                                    @php
                                        $monthlyAmt = number_format(round($amount_due / 3, 2), 2);
                                    @endphp
                                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition duration-150
                                        {{ $selectedPlan === 'monthly' ? 'border-violet-400 bg-violet-50/60 shadow-sm' : 'border-slate-200 hover:border-violet-300 hover:bg-violet-50/30' }}">
                                        <input type="radio" wire:model.live="selectedPlan" value="monthly" class="h-4 w-4 accent-violet-600">
                                        <div class="flex-1">
                                            <span class="text-sm font-bold text-slate-900">Monthly Spread</span>
                                            <p class="text-xs text-slate-500 mt-0.5">Spread across 3 months of the term at a fixed rate.</p>
                                        </div>
                                        <span class="text-sm font-extrabold text-violet-700 whitespace-nowrap">
                                            ₦{{ $monthlyAmt }}/month
                                        </span>
                                    </label>
                                @endif
                            </div>

                            @if($installmentLabel && $selectedPlan !== 'full')
                                <div class="mt-4 p-3 bg-violet-50 border border-violet-200 rounded-xl flex items-center gap-2 text-xs text-violet-800 font-bold">
                                    <svg class="h-4 w-4 text-violet-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $installmentLabel }} — you will pay ₦{{ number_format($paymentAmount, 2) }} now.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Right Panel: Mock Gateway Checkout -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-6">Payment Card Details</h2>
                    
                    @if(!$isGatewayApproved)
                        <div class="text-center py-12 flex flex-col items-center justify-center h-full">
                            <div class="h-14 w-14 bg-amber-50 border border-amber-500 rounded-full flex items-center justify-center mb-4 text-amber-500 animate-pulse">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-900 text-sm">Online Payments Suspended</h3>
                            <p class="text-slate-400 text-xs mt-2 max-w-xs leading-relaxed">
                                Online checkout is temporarily disabled. The school's settlement payout account configuration is awaiting verification by the platform administrator.
                            </p>
                            <p class="text-slate-400 text-xs mt-1 max-w-xs leading-relaxed">
                                Please contact the school's bursar department to settle outstanding tuition fees manually.
                            </p>
                        </div>
                    @elseif($outstanding_balance <= 0)
                        <div class="text-center py-12 flex flex-col items-center justify-center h-full">
                            <div class="h-14 w-14 bg-emerald-50 border border-emerald-500 rounded-full flex items-center justify-center mb-4 text-emerald-500">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-900 text-sm">Tuition Fully Settled!</h3>
                            <p class="text-slate-400 text-xs mt-1 max-w-xs leading-relaxed">Great job! There are no outstanding tuition fees left to pay for this child for the current term.</p>
                        </div>
                    @else
                        <!-- Premium Checkout Summary -->
                        <div class="space-y-6">
                            <div class="bg-gradient-to-br from-violet-600 to-indigo-700 rounded-2xl p-6 text-white relative overflow-hidden shadow-lg shadow-violet-500/10">
                                <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/5 rounded-full z-0"></div>
                                <div class="relative z-10">
                                    <span class="text-[9px] uppercase tracking-widest text-violet-200 font-extrabold bg-white/10 px-2.5 py-1 rounded-full">Secure Checkout</span>
                                    
                                    <h3 class="text-xl font-black mt-4 mb-1">Official School Tuition portal</h3>
                                    <p class="text-xs text-violet-100/80 leading-relaxed font-semibold">
                                        Your payment transaction will be initialized and securely redirected to Paystack. Complete your checkout via Card, Bank Transfer, USSD, or Bank App.
                                    </p>
                                </div>
                            </div>

                            <!-- Charge Summary Banner -->
                            <div class="bg-slate-50 rounded-2xl border border-slate-100 p-5 space-y-4">
                                <div class="text-xs uppercase tracking-widest text-slate-400 font-bold border-b border-slate-200 pb-2">
                                    Order Summary
                                </div>

                                <div class="flex justify-between items-center text-sm font-semibold">
                                    <span class="text-slate-400">Student Name</span>
                                    <span class="text-slate-800 font-bold">
                                        @foreach($students as $s)
                                            @if($s->id == $selectedStudentId)
                                                {{ $s->full_name }}
                                            @endif
                                        @endforeach
                                    </span>
                                </div>

                                <div class="flex justify-between items-center text-sm font-semibold">
                                    <span class="text-slate-400">Payment Plan</span>
                                    <span class="text-slate-800 font-bold capitalize">
                                        {{ str_replace('_', ' ', $selectedPlan) }}
                                    </span>
                                </div>

                                @if($installmentLabel && $selectedPlan !== 'full')
                                    <div class="flex justify-between items-center text-sm font-semibold text-violet-700">
                                        <span>Current Stage</span>
                                        <span class="font-extrabold bg-violet-50 border border-violet-100 px-2 py-0.5 rounded-lg text-xs">{{ $installmentLabel }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between items-center text-base pt-3 border-t border-slate-200 border-dashed font-bold">
                                    <span class="text-slate-900">Total Amount Due Now</span>
                                    <span class="text-xl font-black text-emerald-600">
                                        ₦{{ number_format($paymentAmount, 2) }}
                                    </span>
                                </div>
                            </div>

                            @if ($errorMessage)
                                <div class="mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-600 font-semibold flex items-center gap-2.5 shadow-sm">
                                    <svg class="h-5 w-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>{{ $errorMessage }}</span>
                                </div>
                            @endif

                            <form wire:submit.prevent="processCardPayment" class="space-y-4">
                                <button type="submit" class="w-full rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold py-3.5 transition duration-200 shadow-md hover:shadow-lg shadow-violet-500/10 flex items-center justify-center gap-2 mt-6">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Proceed to Paystack Checkout
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

            </div>
        @endif

    </div>
</div>
