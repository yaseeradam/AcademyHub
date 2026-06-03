<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Payment Gateway Cockpit</h1>
            <p class="mt-1 text-sm text-slate-500">Configure class tuition rates, installment plans, and manage payout settlement settings.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            <!-- ── Fee Scale + Installment Plans ── -->
            <div class="space-y-6">

                <!-- Class Tuition Settings -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-2">Class Tuition Settings</h2>
                    <p class="text-xs text-slate-400 mb-6">Assign outstanding fee scales for each class level for Term {{ $term }} ({{ $session }}).</p>

                    <form wire:submit.prevent="saveFeeStructure" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Select Class</label>
                            <select wire:model.live="selectedClass" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedClass') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tuition Amount</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">₦</span>
                                <input type="number" step="0.01" wire:model="amount_due" class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-8 pr-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200" placeholder="0.00">
                            </div>
                            @error('amount_due') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold py-3 transition duration-200 shadow-md hover:shadow-lg">
                            Update Fee Scale
                        </button>
                    </form>
                </div>

                <!-- Installment Plans Policy -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-lg font-bold text-slate-900">Payment Plans</h2>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-violet-600 bg-violet-50 px-2 py-1 rounded-full border border-violet-200">Per Class</span>
                    </div>
                    <p class="text-xs text-slate-400 mb-5">Enable payment plans parents can choose for the selected class. Full payment is always available.</p>

                    <div class="space-y-3 mb-5">
                        <!-- Full Payment — always on -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-emerald-100 bg-emerald-50/50 cursor-not-allowed opacity-80">
                            <input type="checkbox" checked disabled class="mt-0.5 h-4 w-4 rounded accent-emerald-600">
                            <div>
                                <span class="text-sm font-bold text-slate-800">Full Payment</span>
                                <p class="text-xs text-slate-500 mt-0.5">Pay the entire tuition in one transaction. Always enabled.</p>
                            </div>
                            <span class="ml-auto text-[10px] font-extrabold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full whitespace-nowrap self-start">Always On</span>
                        </label>

                        <!-- 2 Installments -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50/30 transition duration-150 cursor-pointer">
                            <input type="checkbox" wire:model.live="plan_two_installments" class="mt-0.5 h-4 w-4 rounded accent-violet-600">
                            <div>
                                <span class="text-sm font-bold text-slate-800">2 Installments per Term</span>
                                <p class="text-xs text-slate-500 mt-0.5">Fee split into 2 equal halves — beginning and mid-term.</p>
                            </div>
                        </label>

                        <!-- Monthly Spread -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50/30 transition duration-150 cursor-pointer">
                            <input type="checkbox" wire:model.live="plan_monthly" class="mt-0.5 h-4 w-4 rounded accent-violet-600">
                            <div>
                                <span class="text-sm font-bold text-slate-800">Monthly Spread</span>
                                <p class="text-xs text-slate-500 mt-0.5">Fee divided across months of the term at a fixed monthly rate.</p>
                            </div>
                        </label>
                    </div>

                    <button wire:click="saveInstallmentPolicy" type="button" class="w-full rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 text-sm transition duration-200 shadow-sm">
                        Save Payment Plans
                    </button>
                </div>
            </div>

            <!-- ── Payout Settlement Account ── -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Payout Settlement Account</h2>
                        <p class="text-xs text-slate-400">Configure the bank details where all student tuition payments will be settled.</p>
                    </div>
                    <div>
                        @if($subaccount_status === 'approved')
                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl text-xs font-extrabold border border-emerald-200">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Approved &amp; Live
                            </span>
                        @elseif($subaccount_status === 'pending')
                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 px-3 py-1.5 rounded-xl text-xs font-extrabold border border-amber-200">
                                <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Pending Verification
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 px-3 py-1.5 rounded-xl text-xs font-extrabold border border-slate-200">
                                <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                Not Configured
                            </span>
                        @endif
                    </div>
                </div>

                @if(!$isEditingBankDetails)
                    <!-- Read-only Bank details display card -->
                    <div class="bg-slate-50 rounded-2xl border border-slate-100 p-5 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block mb-0.5">Settlement Bank</span>
                                <span class="text-sm font-bold text-slate-800">{{ $bank_name }}</span>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block mb-0.5">Account Number</span>
                                <span class="text-sm font-bold text-slate-800 font-mono">{{ $account_number }}</span>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block mb-0.5">Account Name</span>
                                <span class="text-sm font-bold text-slate-800">{{ $account_name }}</span>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block mb-0.5">Settlement Frequency</span>
                                <span class="text-sm font-bold text-slate-800">{{ $timingOptions[$collection_timing] ?? $collection_timing }}</span>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-slate-200/60">
                            <button type="button" wire:click="enableEditing" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 transition duration-200 shadow-sm">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Update Bank Details
                            </button>
                        </div>
                    </div>
                @else
                    <form wire:submit.prevent="saveGatewaySettings" class="space-y-4">
                        @if($subaccount_status !== 'not_submitted')
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-semibold flex gap-2">
                                <svg class="h-4 w-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Changing payout details will place your account under "Pending Approval" state, suspending parent online checkout until re-verified.
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div x-data="{
                                    knownBanks: ['Access Bank','Guaranty Trust Bank (GTBank)','Zenith Bank','United Bank for Africa (UBA)','First Bank of Nigeria','Wema Bank','Sterling Bank','Union Bank','Fidelity Bank','Stanbic IBTC Bank','Ecobank Nigeria','Kuda Bank','OPay','Moniepoint MFB','Polaris Bank','Heritage Bank','Citibank Nigeria','Jaiz Bank','SunTrust Bank','Titan Trust Bank','Providus Bank','Parallex Bank','PalmPay','Carbon (One Finance)','VFD Microfinance Bank','LAPO Microfinance Bank','AB Microfinance Bank','Accion MFB','Eyowo','Fairmoney','Raven Bank','Sparkle','Standard Chartered Bank','Keystone Bank','First City Monument Bank (FCMB)','Coronation Bank','Globus Bank','Premium Trust Bank','Optimus Bank','Signature Bank'],
                                    get isOther() { return '{{ $bank_name }}' !== '' && !this.knownBanks.includes('{{ $bank_name }}'); }
                                }" class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Settlement Bank Name</label>

                                <select x-on:change="$event.target.value === '__other__' ? ($el.nextElementSibling.style.display='block') : ($el.nextElementSibling.style.display='none')"
                                        wire:model="bank_name"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-bold text-sm">
                                    <option value="">— Select your bank —</option>

                                    <optgroup label="Tier 1 Commercial Banks">
                                        <option value="Access Bank">Access Bank</option>
                                        <option value="First Bank of Nigeria">First Bank of Nigeria</option>
                                        <option value="Guaranty Trust Bank (GTBank)">Guaranty Trust Bank (GTBank)</option>
                                        <option value="United Bank for Africa (UBA)">United Bank for Africa (UBA)</option>
                                        <option value="Zenith Bank">Zenith Bank</option>
                                    </optgroup>

                                    <optgroup label="Commercial Banks">
                                        <option value="Citibank Nigeria">Citibank Nigeria</option>
                                        <option value="Coronation Bank">Coronation Bank</option>
                                        <option value="Ecobank Nigeria">Ecobank Nigeria</option>
                                        <option value="Fidelity Bank">Fidelity Bank</option>
                                        <option value="First City Monument Bank (FCMB)">First City Monument Bank (FCMB)</option>
                                        <option value="Globus Bank">Globus Bank</option>
                                        <option value="Heritage Bank">Heritage Bank</option>
                                        <option value="Jaiz Bank">Jaiz Bank</option>
                                        <option value="Keystone Bank">Keystone Bank</option>
                                        <option value="Optimus Bank">Optimus Bank</option>
                                        <option value="Parallex Bank">Parallex Bank</option>
                                        <option value="Polaris Bank">Polaris Bank</option>
                                        <option value="Premium Trust Bank">Premium Trust Bank</option>
                                        <option value="Providus Bank">Providus Bank</option>
                                        <option value="Signature Bank">Signature Bank</option>
                                        <option value="Stanbic IBTC Bank">Stanbic IBTC Bank</option>
                                        <option value="Standard Chartered Bank">Standard Chartered Bank</option>
                                        <option value="Sterling Bank">Sterling Bank</option>
                                        <option value="SunTrust Bank">SunTrust Bank</option>
                                        <option value="Titan Trust Bank">Titan Trust Bank</option>
                                        <option value="Union Bank">Union Bank</option>
                                        <option value="Wema Bank">Wema Bank</option>
                                    </optgroup>

                                    <optgroup label="Fintechs &amp; Digital Banks">
                                        <option value="Carbon (One Finance)">Carbon (One Finance)</option>
                                        <option value="Eyowo">Eyowo</option>
                                        <option value="Fairmoney">Fairmoney</option>
                                        <option value="Kuda Bank">Kuda Bank</option>
                                        <option value="Moniepoint MFB">Moniepoint MFB</option>
                                        <option value="OPay">OPay</option>
                                        <option value="PalmPay">PalmPay</option>
                                        <option value="Raven Bank">Raven Bank</option>
                                        <option value="Sparkle">Sparkle</option>
                                        <option value="VFD Microfinance Bank">VFD Microfinance Bank</option>
                                    </optgroup>

                                    <optgroup label="Microfinance Banks">
                                        <option value="AB Microfinance Bank">AB Microfinance Bank</option>
                                        <option value="Accion MFB">Accion MFB</option>
                                        <option value="LAPO Microfinance Bank">LAPO Microfinance Bank</option>
                                    </optgroup>

                                    <optgroup label="Not listed above?">
                                        <option value="__other__">Other — type my bank name below ↓</option>
                                    </optgroup>
                                </select>

                                {{-- Free-text fallback for unlisted banks --}}
                                <div style="display: {{ ($bank_name && !in_array($bank_name, ['Access Bank','Guaranty Trust Bank (GTBank)','Zenith Bank','United Bank for Africa (UBA)','First Bank of Nigeria','Wema Bank','Sterling Bank','Union Bank','Fidelity Bank','Stanbic IBTC Bank','Ecobank Nigeria','Kuda Bank','OPay','Moniepoint MFB','Polaris Bank','Heritage Bank','Citibank Nigeria','Jaiz Bank','SunTrust Bank','Titan Trust Bank','Providus Bank','Parallex Bank','PalmPay','Carbon (One Finance)','VFD Microfinance Bank','LAPO Microfinance Bank','AB Microfinance Bank','Accion MFB','Eyowo','Fairmoney','Raven Bank','Sparkle','Standard Chartered Bank','Keystone Bank','First City Monument Bank (FCMB)','Coronation Bank','Globus Bank','Premium Trust Bank','Optimus Bank','Signature Bank'])) ? 'block' : 'none' }};">
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="flex-1">
                                            <input type="text"
                                                   wire:model="bank_name"
                                                   placeholder="Type full official bank name..."
                                                   class="w-full rounded-xl border border-violet-300 bg-violet-50/50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-bold text-sm">
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-1.5 ml-1">Type the full official bank name exactly as it appears on your account statement.</p>
                                </div>

                                @error('bank_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>


                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Account Number (10-Digit NUBAN)</label>
                                <input type="text" wire:model="account_number" maxlength="10" placeholder="0123456789" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-mono">
                                @error('account_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Account Name</label>
                                <input type="text" wire:model="account_name" placeholder="e.g. Greenwood Academy Ltd" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                                @error('account_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Settlement Timing</label>
                                <select wire:model="collection_timing" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200 font-bold text-sm">
                                    @foreach($timingOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('collection_timing') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            @if($subaccount_status !== 'not_submitted')
                                <button type="button" wire:click="$set('isEditingBankDetails', false)" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold px-5 py-3 transition duration-200">
                                    Cancel
                                </button>
                            @endif
                            <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-3 transition duration-200 shadow-md">
                                Submit for Verification
                            </button>
                        </div>
                    </form>
                @endif
            </div>

        </div>

        <!-- Transactions Ledger -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 mb-6">Incoming Parent Tuition Payments Ledger</h2>

            @if($ledger->isEmpty())
                <div class="text-center py-16 border border-dashed border-slate-100 rounded-2xl">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-sm font-semibold text-slate-900">No payment records found</h3>
                    <p class="mt-1 text-sm text-slate-500">Parent tuition receipts will dynamically stream here as they execute checkout portals.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-3">
                                <th class="px-4 py-3">Receipt</th>
                                <th class="px-4 py-3">Student</th>
                                <th class="px-4 py-3">Class</th>
                                <th class="px-4 py-3">Term / Session</th>
                                <th class="px-4 py-3">Plan</th>
                                <th class="px-4 py-3">Method</th>
                                <th class="px-4 py-3">Amount Paid</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($ledger as $item)
                                <tr class="hover:bg-slate-50/40 transition duration-150 text-sm text-slate-600 font-semibold">
                                    <td class="px-4 py-4 text-slate-900 font-bold font-mono">
                                        {{ $item->receipt_number }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-900 font-bold">
                                        {{ $item->student?->full_name }}
                                    </td>
                                    <td class="px-4 py-4">
                                        {{ $item->student?->schoolClass?->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        Term {{ $item->term }} ({{ $item->session }})
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($item->installment_plan && $item->installment_plan !== 'full')
                                            <span class="inline-flex items-center gap-1 bg-violet-50 text-violet-700 px-2 py-1 rounded text-xs font-bold border border-violet-200">
                                                @if($item->installment_plan === 'two_installments')
                                                    2-Part #{{ $item->installment_number }}
                                                @elseif($item->installment_plan === 'monthly')
                                                    Monthly #{{ $item->installment_number }}
                                                @else
                                                    {{ $item->installment_plan }}
                                                @endif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2 py-1 rounded text-xs font-bold border border-emerald-200">
                                                Full
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs">
                                            {{ $item->payment_method ?: 'Digital Gateway' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 font-extrabold text-slate-900">
                                        ₦{{ number_format($item->amount_paid, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if($item->is_void)
                                            <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-bold text-rose-700 border border-rose-200">Voided</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200">Success</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</div>
