<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Payment Gateway Cockpit</h1>
            <p class="mt-1 text-sm text-slate-500">Configure class tuition rates, set up payment tokens, and manage incoming digital transactions.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            <!-- Class Fee Scale -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-2">Class Tuition Settings</h2>
                <p class="text-xs text-slate-400 mb-6">Assign outstanding fee scales for each class level for *Term {{ $term }} ({{ $session }})*.</p>

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

            <!-- Credentials / Configuration -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-2">API Credentials &amp; Keys</h2>
                <p class="text-xs text-slate-400 mb-6">Manage online credit card payment options securely via Paystack API.</p>

                <form wire:submit.prevent="saveGatewaySettings" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Paystack Public Key</label>
                            <input type="text" wire:model="public_key" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200" placeholder="pk_test_...">
                            @error('public_key') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Paystack Secret Key</label>
                            <input type="password" wire:model="secret_key" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200" placeholder="sk_test_...">
                            @error('secret_key') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3 py-2 border-t border-slate-100 mt-4">
                        <input type="checkbox" wire:model="sandbox_mode" id="sandbox" class="h-4.5 w-4.5 rounded text-violet-600 border-slate-200 focus:ring-violet-500">
                        <label for="sandbox" class="text-xs font-semibold text-slate-600 cursor-pointer">
                            Enable Sandbox / Test Gateway Mode
                        </label>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-3 transition duration-200 shadow-md">
                            Save Gateway Config
                        </button>
                    </div>
                </form>
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
                                <th class="px-4 py-3">Receipt Number</th>
                                <th class="px-4 py-3">Student Name</th>
                                <th class="px-4 py-3">Class Level</th>
                                <th class="px-4 py-3">Term / Session</th>
                                <th class="px-4 py-3">Payment Method</th>
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
