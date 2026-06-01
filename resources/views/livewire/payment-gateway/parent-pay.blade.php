<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 relative overflow-hidden font-['Outfit',sans-serif]">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Secure Tuition Payment Center</h1>
            <p class="mt-1 text-sm text-slate-500">Pay school tuition and other outstanding balances directly from your dashboard.</p>
        </div>

        @if($paymentSuccess)
            <!-- TRANSACTION SUCCESS RECEIPTS SCREEN -->
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
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400 font-semibold">Amount Paid</span>
                        <span class="font-black text-emerald-600 text-lg">₦{{ number_format($amount_due, 2) }}</span>
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
                
                <!-- Left Panel: Student Selection & Balance -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm h-fit">
                    <h2 class="text-lg font-bold text-slate-900 mb-6">Select Child &amp; Bill</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Student Beneficiary</label>
                            <select wire:model.live="selectedStudentId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-bold">
                                @foreach($students as $s)
                                    <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->schoolClass?->name ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Balances Card -->
                        <div class="bg-slate-50 rounded-2xl border border-slate-100 p-5 space-y-4.5">
                            <div class="text-xs uppercase tracking-widest text-slate-400 font-bold border-b border-slate-200 pb-2">
                                Term {{ $term }} ({{ $session }}) Statement
                            </div>
                            <div class="flex justify-between items-center text-sm font-semibold">
                                <span class="text-slate-400">Total Allocated Fees</span>
                                <span class="text-slate-800 font-bold">₦{{ number_format($amount_due, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                                <span>Paid Tuition to Date</span>
                                <span class="font-extrabold">₦{{ number_format($amount_paid, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-base pt-3 border-t border-slate-200 border-dashed font-bold">
                                <span class="text-slate-900">Outstanding Balance</span>
                                <span class="text-xl font-black bg-gradient-to-r from-violet-600 to-pink-500 bg-clip-text text-transparent">
                                    ₦{{ number_format($outstanding_balance, 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 text-xs text-slate-400 bg-slate-50/50 p-3 rounded-xl">
                            <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Fee scales are configured dynamically by the bursary department based on each student class.
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Mock Gateway Checkout -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-6">Payment Card Details</h2>
                    
                    @if($outstanding_balance <= 0)
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
                        <!-- Credit Card Display Mockup -->
                        <div class="w-full height-[160px] bg-gradient-to-r from-violet-600 to-pink-600 rounded-2xl p-6 shadow-lg shadow-violet-500/10 flex flex-col justify-between mb-6 overflow-hidden relative">
                            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/5 rounded-full z-0"></div>
                            
                            <div class="flex justify-between items-center z-10">
                                <div class="h-8 w-11 bg-gradient-to-br from-amber-400 to-amber-600 rounded-md"></div>
                                <span class="text-[10px] uppercase tracking-widest text-white/60 font-black">QuickPay</span>
                            </div>

                            <div class="text-lg font-mono font-bold tracking-widest text-white z-10 text-shadow mt-4">
                                {{ $card_number ? wordwrap(str_replace(' ', '', $card_number), 4, ' ', true) : '•••• •••• •••• ••••' }}
                            </div>

                            <div class="flex justify-between items-end mt-4 z-10">
                                <div>
                                    <span class="text-[8px] uppercase tracking-wider text-white/50 block">Card Holder</span>
                                    <span class="text-xs font-bold uppercase text-white tracking-wider truncate max-w-[150px] block">{{ auth()->user()->name }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[8px] uppercase tracking-wider text-white/50 block">Expires</span>
                                    <span class="text-xs font-bold text-white font-mono block">{{ $card_expiry ?: 'MM/YY' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card details Form -->
                        <form wire:submit.prevent="processCardPayment" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Card Number</label>
                                <input type="text" wire:model.live="card_number" maxlength="19" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-mono" placeholder="4111 2222 3333 4444">
                                @error('card_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Expiry Date</label>
                                    <input type="text" wire:model.live="card_expiry" placeholder="MM/YY" maxlength="5" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-mono">
                                    @error('card_expiry') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">CVV</label>
                                    <input type="password" wire:model.live="card_cvv" placeholder="•••" maxlength="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-mono">
                                    @error('card_cvv') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold py-3.5 transition duration-200 shadow-md hover:shadow-lg shadow-violet-500/10 flex items-center justify-center gap-2 mt-6">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Pay ₦{{ number_format($outstanding_balance, 2) }} Securely
                            </button>
                        </form>
                    @endif
                </div>

            </div>
        @endif

    </div>
</div>
