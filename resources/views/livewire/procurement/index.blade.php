<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="sm:flex sm:items-center sm:justify-between pb-5 border-b border-slate-200 dark:border-slate-700">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Procurement, Expense &amp; Asset Register</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Track all purchases, operational expenditures, vendors, receipts, and school physical assets.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-2.5">
            <a href="{{ route('procurement.cash-flow') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Cash Flow Summary
            </a>
            <button wire:click="exportCsv" type="button" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export CSV
            </button>
            <button wire:click="openCreateModal" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Purchase / Asset
            </button>
        </div>
    </div>

    {{-- Tabs (All Purchases vs Asset Inventory) --}}
    <div class="flex border-b border-slate-200 dark:border-slate-700 space-x-6">
        <button wire:click="$set('activeTab', 'all_purchases')" class="pb-3 text-sm font-bold border-b-2 transition-all {{ $activeTab === 'all_purchases' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400' }}">
            All Purchases &amp; Expenses ({{ $totalItemsCount }})
        </button>
        <button wire:click="$set('activeTab', 'inventory')" class="pb-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2 {{ $activeTab === 'inventory' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400' }}">
            <span>Physical Assets &amp; Inventory</span>
            <span class="px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'inventory' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                {{ $totalAssetsCount }}
            </span>
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500">Total Spend (All Time)</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">₦{{ number_format($totalSpendAllTime, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $totalItemsCount }} items recorded</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500">This Month's Spend</div>
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">₦{{ number_format($thisMonthSpend, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Current calendar month</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500">Tracked School Assets</div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalAssetsCount }} Items</div>
            <div class="text-xs text-slate-400 mt-1">Assigned to rooms/labs</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500">Top Expense Category</div>
            <div class="text-lg font-bold text-slate-900 dark:text-white truncate mt-1">{{ $topCategory }}</div>
            <div class="text-xs text-slate-400 mt-1">₦{{ number_format($topCategorySpend, 2) }}</div>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search item, vendor, receipt..." class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <select wire:model.live="categoryFilter" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            @if($activeTab === 'inventory')
                <div>
                    <select wire:model.live="locationFilter" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                        <option value="all">All Asset Locations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <select wire:model.live="paymentMethodFilter" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                        <option value="all">All Payment Methods</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm }}">{{ $pm }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <select wire:model.live="dateRangeFilter" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                    <option value="this_month">This Month</option>
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_term">This Term (Last 3 Mo)</option>
                    <option value="all">All Time</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs font-semibold text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Item Description</th>
                        <th class="px-4 py-3">Category</th>
                        @if($activeTab === 'inventory')
                            <th class="px-4 py-3">Location &amp; Status</th>
                        @endif
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Unit Price</th>
                        <th class="px-4 py-3 text-right font-bold">Total Amount</th>
                        <th class="px-4 py-3">Purchaser / Vendor</th>
                        <th class="px-4 py-3 text-center">Receipt</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-slate-700 dark:text-slate-200">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-medium text-slate-500">
                                {{ $rec->purchased_at ? $rec->purchased_at->format('M j, Y') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $rec->item_name }}</div>
                                @if($rec->is_inventory_item)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-900/40 dark:text-emerald-300 px-1.5 py-0.5 rounded">
                                        Asset Tracked
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                    {{ $rec->category }}
                                </span>
                            </td>
                            @if($activeTab === 'inventory')
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-medium text-slate-800 dark:text-white">{{ $rec->inventory_location ?? 'Central Store' }}</div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">{{ $rec->inventory_status ?? 'in_use' }}</span>
                                </td>
                            @endif
                            <td class="px-4 py-3 text-center text-xs whitespace-nowrap">
                                {{ number_format((float)$rec->quantity, 0) }} {{ $rec->unit }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-xs whitespace-nowrap">
                                ₦{{ number_format((float)$rec->unit_price, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                ₦{{ number_format((float)$rec->total_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div class="font-medium text-slate-800 dark:text-white">{{ $rec->purchaser_display_name }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $rec->vendor_name ?: 'Vendor not noted' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($rec->receipt_attachment_path)
                                    <a href="{{ $rec->receipt_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </a>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button wire:click="openViewModal({{ $rec->id }})" class="p-1 text-slate-400 hover:text-indigo-600" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <button wire:click="openEditModal({{ $rec->id }})" class="p-1 text-slate-400 hover:text-amber-600" title="Edit Purchase">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $rec->id }})" class="p-1 text-slate-400 hover:text-rose-600" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'inventory' ? 10 : 9 }}" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No procurement or asset records found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
            {{ $records->links() }}
        </div>
    </div>

    {{-- Create / Edit Modal --}}
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="$set('{{ $showCreateModal ? 'showCreateModal' : 'showEditModal' }}', false)" class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>
                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            {{ $showCreateModal ? 'Record New Purchase & Expense' : 'Edit Procurement Record' }}
                        </h3>
                        <button wire:click="$set('{{ $showCreateModal ? 'showCreateModal' : 'showEditModal' }}', false)" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto text-sm">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Item Name / Description *</label>
                            <input type="text" wire:model="itemName" placeholder="e.g. 50 Student Wooden Desks, 500L Diesel" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            @error('itemName') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Expense Category *</label>
                                <select wire:model="category" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Date Purchased *</label>
                                <input type="date" wire:model="purchasedAt" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Quantity *</label>
                                <input type="number" step="0.01" min="0.01" wire:model.live.debounce.300ms="quantity" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Unit (pcs, litres, etc.)</label>
                                <input type="text" wire:model="unit" placeholder="pcs" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Unit Price (₦)</label>
                                <input type="number" step="0.01" min="0" wire:model.live.debounce.300ms="unitPrice" placeholder="0.00" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Amount (₦) *</label>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-sm font-bold text-slate-400">₦</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" wire:model="totalAmount" class="w-full pl-8 text-sm font-bold border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                </div>
                                @error('totalAmount') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                                <select wire:model="paymentMethod" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                    @foreach($paymentMethods as $pm)
                                        <option value="{{ $pm }}">{{ $pm }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Physical Asset & Inventory Toggle --}}
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-600 space-y-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="isInventoryItem" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                <span class="font-semibold text-slate-800 dark:text-white">Track as Physical School Asset / Inventory</span>
                            </label>

                            @if($isInventoryItem)
                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Assigned Location</label>
                                        <select wire:model="inventoryLocation" class="w-full text-xs border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc }}">{{ $loc }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Current Condition</label>
                                        <select wire:model="inventoryStatus" class="w-full text-xs border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                            @foreach($inventoryStatuses as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Purchased By (Staff Member)</label>
                                <select wire:model="purchaserId" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                                    <option value="">Select Staff</option>
                                    @foreach($staffUsers as $su)
                                        <option value="{{ $su->id }}">{{ $su->name }} ({{ ucfirst($su->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Vendor / Store Name</label>
                                <input type="text" wire:model="vendorName" placeholder="e.g. Kano Educational Supplies Ltd" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Invoice / Receipt No</label>
                                <input type="text" wire:model="receiptNumber" placeholder="INV-2026-0042" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Receipt Photo / PDF</label>
                                <input type="file" wire:model="receiptFile" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Notes / Additional Details</label>
                            <textarea wire:model="notes" rows="2" placeholder="Item warranty, serial numbers, delivery notes..." class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-slate-50 dark:bg-slate-700/30 text-right border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
                        <button wire:click="$set('{{ $showCreateModal ? 'showCreateModal' : 'showEditModal' }}', false)" type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button wire:click="{{ $showCreateModal ? 'savePurchase' : 'updatePurchase' }}" type="button" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            {{ $showCreateModal ? 'Save Purchase Record' : 'Update Record' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if($showViewModal && $selectedRecord)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="$set('showViewModal', false)" class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>
                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $selectedRecord->item_name }}</h3>
                        <button wire:click="$set('showViewModal', false)" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-3 text-sm">
                        <div class="flex justify-between border-b pb-2 dark:border-slate-700">
                            <span class="text-slate-400">Total Expenditure:</span>
                            <span class="font-mono font-bold text-lg text-slate-900 dark:text-white">₦{{ number_format((float)$selectedRecord->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2 dark:border-slate-700">
                            <span class="text-slate-400">Quantity &amp; Price:</span>
                            <span class="text-slate-800 dark:text-white">{{ number_format((float)$selectedRecord->quantity, 0) }} {{ $selectedRecord->unit }} @ ₦{{ number_format((float)$selectedRecord->unit_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2 dark:border-slate-700">
                            <span class="text-slate-400">Purchased On:</span>
                            <span class="text-slate-800 dark:text-white">{{ $selectedRecord->purchased_at?->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2 dark:border-slate-700">
                            <span class="text-slate-400">Purchaser:</span>
                            <span class="text-slate-800 dark:text-white">{{ $selectedRecord->purchaser_display_name }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2 dark:border-slate-700">
                            <span class="text-slate-400">Vendor / Store:</span>
                            <span class="text-slate-800 dark:text-white">{{ $selectedRecord->vendor_name ?: 'N/A' }}</span>
                        </div>
                        @if($selectedRecord->is_inventory_item)
                            <div class="flex justify-between border-b pb-2 dark:border-slate-700">
                                <span class="text-slate-400">Asset Location:</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedRecord->inventory_location }}</span>
                            </div>
                        @endif
                        @if($selectedRecord->receipt_attachment_path)
                            <div class="pt-2">
                                <span class="text-xs font-semibold text-slate-400 block mb-1">Receipt Attachment:</span>
                                <a href="{{ $selectedRecord->receipt_url }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 font-bold hover:underline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    View / Download Full Receipt
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>
                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Delete Procurement Record?</h3>
                    <p class="text-sm text-slate-500 mt-2">This purchase record will be moved to archive/trash. This can be undone by superadmin.</p>
                    <div class="mt-6 flex justify-end gap-2">
                        <button wire:click="$set('showDeleteModal', false)" type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button wire:click="deletePurchase" type="button" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 rounded-lg hover:bg-rose-700">Delete Record</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
