<?php

namespace App\Livewire\Procurement;

use App\Models\ProcurementRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Procurement & Expense Records')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    // View tab: 'all_purchases' or 'inventory'
    public string $activeTab = 'all_purchases';

    // Filters
    public string $search = '';
    public string $categoryFilter = 'all';
    public string $paymentMethodFilter = 'all';
    public string $dateRangeFilter = 'this_month';
    public string $locationFilter = 'all';
    public string $inventoryStatusFilter = 'all';
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;

    // Modals
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;

    // Form fields
    public ?int $editingId = null;
    public string $itemName = '';
    public string $category = 'General & Miscellaneous';
    public float $quantity = 1.0;
    public string $unit = 'pcs';
    public ?float $unitPrice = null;
    public float $totalAmount = 0.0;
    public string $purchasedAt = '';
    public ?int $purchaserId = null;
    public string $purchaserName = '';
    public string $vendorName = '';
    public string $vendorPhone = '';
    public string $paymentMethod = 'Cash';
    public string $receiptNumber = '';
    public $receiptFile = null;
    public ?string $existingReceiptPath = null;
    public string $notes = '';

    // Inventory Asset Tracking fields
    public bool $isInventoryItem = false;
    public string $inventoryLocation = 'Central Store';
    public string $inventoryStatus = 'in_use';
    public string $assignedTo = '';

    // Selected record for viewing/deleting
    public ?ProcurementRecord $selectedRecord = null;
    public ?int $deletingId = null;

    protected function rules(): array
    {
        return [
            'itemName'          => 'required|string|min:2|max:255',
            'category'          => 'required|string|max:100',
            'quantity'          => 'required|numeric|min:0.01',
            'unit'              => 'required|string|max:50',
            'unitPrice'         => 'nullable|numeric|min:0',
            'totalAmount'       => 'required|numeric|min:0',
            'purchasedAt'       => 'required|date',
            'purchaserId'       => 'nullable|integer',
            'purchaserName'     => 'nullable|string|max:255',
            'vendorName'        => 'nullable|string|max:255',
            'vendorPhone'       => 'nullable|string|max:50',
            'paymentMethod'     => 'required|string|max:50',
            'receiptNumber'     => 'nullable|string|max:100',
            'receiptFile'       => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:5120',
            'notes'             => 'nullable|string|max:1000',
            'isInventoryItem'   => 'boolean',
            'inventoryLocation' => 'nullable|string|max:100',
            'inventoryStatus'   => 'nullable|string|max:50',
            'assignedTo'        => 'nullable|string|max:100',
        ];
    }

    public function mount(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['admin', 'bursar', 'proprietor'], true),
            403,
            'Only administrators, bursars, and proprietors may access procurement records.'
        );
        $this->purchasedAt = Carbon::today()->format('Y-m-d');
        $this->purchaserId = auth()->id();
    }

    public static function locations(): array
    {
        return [
            'Central Store',
            'Science Lab',
            'Computer / ICT Lab',
            'Admin Office',
            'Staff Room',
            'Library',
            'Principal Office',
            'Classrooms (General)',
            'Clinic / First Aid',
            'Security Gate',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentMethodFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateRangeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatedQuantity(): void
    {
        $this->calculateTotal();
    }

    public function updatedUnitPrice(): void
    {
        $this->calculateTotal();
    }

    private function calculateTotal(): void
    {
        if ($this->unitPrice !== null && $this->unitPrice > 0 && $this->quantity > 0) {
            $this->totalAmount = round($this->quantity * $this->unitPrice, 2);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->purchasedAt = Carbon::today()->format('Y-m-d');
        $this->purchaserId = auth()->id();
        $this->showCreateModal = true;
    }

    public function savePurchase(): void
    {
        $this->validate();

        $tenantId = auth()->user()?->tenant_id ?? 1;

        $attachmentPath = null;
        if ($this->receiptFile) {
            $attachmentPath = $this->receiptFile->store('procurement-receipts/' . $tenantId, 'public');
        }

        ProcurementRecord::create([
            'tenant_id'               => $tenantId,
            'item_name'               => trim($this->itemName),
            'category'                => $this->category,
            'quantity'                => $this->quantity,
            'unit'                    => trim($this->unit),
            'unit_price'              => $this->unitPrice,
            'total_amount'            => $this->totalAmount,
            'purchased_at'            => $this->purchasedAt,
            'purchaser_id'            => $this->purchaserId,
            'purchaser_name'          => trim($this->purchaserName),
            'vendor_name'             => trim($this->vendorName),
            'vendor_phone'            => trim($this->vendorPhone),
            'payment_method'          => $this->paymentMethod,
            'receipt_number'          => trim($this->receiptNumber),
            'receipt_attachment_path' => $attachmentPath,
            'status'                  => 'completed',
            'is_inventory_item'       => $this->isInventoryItem,
            'inventory_location'      => $this->isInventoryItem ? trim($this->inventoryLocation) : null,
            'inventory_status'        => $this->isInventoryItem ? $this->inventoryStatus : 'in_use',
            'assigned_to'             => $this->isInventoryItem ? trim($this->assignedTo) : null,
            'notes'                   => trim($this->notes),
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        $this->dispatch('alert', message: 'Purchase record saved successfully!', type: 'success');
    }

    public function openEditModal(int $id): void
    {
        $record = ProcurementRecord::forTenant()->findOrFail($id);
        $this->editingId = $record->id;
        $this->itemName = $record->item_name;
        $this->category = $record->category;
        $this->quantity = (float) $record->quantity;
        $this->unit = $record->unit;
        $this->unitPrice = $record->unit_price ? (float) $record->unit_price : null;
        $this->totalAmount = (float) $record->total_amount;
        $this->purchasedAt = $record->purchased_at ? $record->purchased_at->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $this->purchaserId = $record->purchaser_id;
        $this->purchaserName = $record->purchaser_name ?? '';
        $this->vendorName = $record->vendor_name ?? '';
        $this->vendorPhone = $record->vendor_phone ?? '';
        $this->paymentMethod = $record->payment_method;
        $this->receiptNumber = $record->receipt_number ?? '';
        $this->existingReceiptPath = $record->receipt_attachment_path;
        $this->notes = $record->notes ?? '';
        $this->isInventoryItem = (bool) $record->is_inventory_item;
        $this->inventoryLocation = $record->inventory_location ?? 'Central Store';
        $this->inventoryStatus = $record->inventory_status ?? 'in_use';
        $this->assignedTo = $record->assigned_to ?? '';
        $this->receiptFile = null;

        $this->showEditModal = true;
    }

    public function updatePurchase(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'bursar'], true), 403);
        $this->validate();

        $record = ProcurementRecord::forTenant()->findOrFail($this->editingId);

        $attachmentPath = $record->receipt_attachment_path;
        if ($this->receiptFile) {
            if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }
            $attachmentPath = $this->receiptFile->store('procurement-receipts/' . $record->tenant_id, 'public');
        }

        $record->update([
            'item_name'               => trim($this->itemName),
            'category'                => $this->category,
            'quantity'                => $this->quantity,
            'unit'                    => trim($this->unit),
            'unit_price'              => $this->unitPrice,
            'total_amount'            => $this->totalAmount,
            'purchased_at'            => $this->purchasedAt,
            'purchaser_id'            => $this->purchaserId,
            'purchaser_name'          => trim($this->purchaserName),
            'vendor_name'             => trim($this->vendorName),
            'vendor_phone'            => trim($this->vendorPhone),
            'payment_method'          => $this->paymentMethod,
            'receipt_number'          => trim($this->receiptNumber),
            'receipt_attachment_path' => $attachmentPath,
            'is_inventory_item'       => $this->isInventoryItem,
            'inventory_location'      => $this->isInventoryItem ? trim($this->inventoryLocation) : null,
            'inventory_status'        => $this->isInventoryItem ? $this->inventoryStatus : 'in_use',
            'assigned_to'             => $this->isInventoryItem ? trim($this->assignedTo) : null,
            'notes'                   => trim($this->notes),
        ]);

        $this->showEditModal = false;
        $this->resetForm();
        $this->dispatch('alert', message: 'Purchase record updated successfully!', type: 'success');
    }

    public function openViewModal(int $id): void
    {
        $this->selectedRecord = ProcurementRecord::forTenant()->with('purchaser')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function deletePurchase(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'bursar'], true), 403);
        if ($this->deletingId) {
            $record = ProcurementRecord::forTenant()->findOrFail($this->deletingId);
            $record->delete();
            $this->showDeleteModal = false;
            $this->deletingId = null;
            $this->dispatch('alert', message: 'Purchase record removed.', type: 'info');
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'itemName', 'quantity', 'unitPrice', 'totalAmount',
            'purchaserName', 'vendorName', 'vendorPhone', 'receiptNumber',
            'receiptFile', 'existingReceiptPath', 'notes', 'isInventoryItem', 'assignedTo'
        ]);
        $this->category = 'General & Miscellaneous';
        $this->unit = 'pcs';
        $this->quantity = 1.0;
        $this->totalAmount = 0.0;
        $this->paymentMethod = 'Cash';
        $this->purchasedAt = Carbon::today()->format('Y-m-d');
        $this->purchaserId = auth()->id();
        $this->inventoryLocation = 'Central Store';
        $this->inventoryStatus = 'in_use';
        $this->resetValidation();
    }

    public function exportCsv(): StreamedResponse
    {
        $query = $this->buildFilteredQuery();
        $records = $query->orderByDesc('purchased_at')->get();

        $currency = config('academyhub.currency_symbol', '₦');
        $filename = 'procurement_records_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($records, $currency) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Item Description',
                'Category',
                'Quantity',
                'Unit',
                'Unit Price (' . $currency . ')',
                'Total Amount (' . $currency . ')',
                'Date Purchased',
                'Purchased By',
                'Vendor / Supplier',
                'Vendor Phone',
                'Payment Method',
                'Receipt / Invoice No',
                'Asset / Inventory',
                'Location',
                'Status',
                'Notes',
            ]);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->item_name,
                    $r->category,
                    $r->quantity,
                    $r->unit,
                    $r->unit_price ? number_format((float) $r->unit_price, 2) : '0.00',
                    number_format((float) $r->total_amount, 2),
                    $r->purchased_at ? $r->purchased_at->format('Y-m-d') : '',
                    $r->purchaser_display_name,
                    $r->vendor_name ?? '',
                    $r->vendor_phone ?? '',
                    $r->payment_method,
                    $r->receipt_number ?? '',
                    $r->is_inventory_item ? 'Yes' : 'No',
                    $r->inventory_location ?? '-',
                    ucfirst($r->status),
                    $r->notes ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFilteredQuery()
    {
        $query = ProcurementRecord::forTenant()
            ->with(['purchaser'])
            ->search($this->search)
            ->byCategory($this->categoryFilter);

        if ($this->activeTab === 'inventory') {
            $query->where('is_inventory_item', true);
            if ($this->locationFilter !== 'all') {
                $query->where('inventory_location', $this->locationFilter);
            }
            if ($this->inventoryStatusFilter !== 'all') {
                $query->where('inventory_status', $this->inventoryStatusFilter);
            }
        }

        if ($this->paymentMethodFilter !== 'all') {
            $query->where('payment_method', $this->paymentMethodFilter);
        }

        switch ($this->dateRangeFilter) {
            case 'today':
                $query->whereDate('purchased_at', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('purchased_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereBetween('purchased_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'this_term':
                $query->whereBetween('purchased_at', [Carbon::now()->subMonths(3)->startOfDay(), Carbon::now()->endOfDay()]);
                break;
            case 'custom':
                if ($this->customStartDate && $this->customEndDate) {
                    $query->whereBetween('purchased_at', [$this->customStartDate, $this->customEndDate]);
                }
                break;
            case 'all':
            default:
                break;
        }

        return $query;
    }

    public function render()
    {
        $records = $this->buildFilteredQuery()
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate(15);

        // Stats
        $baseQuery = ProcurementRecord::forTenant();
        $totalSpendAllTime = (clone $baseQuery)->sum('total_amount');
        $thisMonthSpend = (clone $baseQuery)
            ->whereBetween('purchased_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('total_amount');
        $totalItemsCount = (clone $baseQuery)->count();
        $totalAssetsCount = (clone $baseQuery)->where('is_inventory_item', true)->count();

        $topCategory = (clone $baseQuery)
            ->selectRaw('category, SUM(total_amount) as total_cat_spend')
            ->groupBy('category')
            ->orderByDesc('total_cat_spend')
            ->first();

        $staffUsers = User::withoutGlobalScopes()
            ->where('tenant_id', auth()->user()?->tenant_id ?? 1)
            ->whereIn('role', ['admin', 'teacher', 'bursar'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.procurement.index', [
            'records'           => $records,
            'totalSpendAllTime' => $totalSpendAllTime,
            'thisMonthSpend'    => $thisMonthSpend,
            'totalItemsCount'   => $totalItemsCount,
            'totalAssetsCount'  => $totalAssetsCount,
            'topCategory'       => $topCategory?->category ?? 'None',
            'topCategorySpend'  => $topCategory?->total_cat_spend ?? 0,
            'categories'        => ProcurementRecord::categories(),
            'paymentMethods'    => ProcurementRecord::paymentMethods(),
            'inventoryStatuses' => ProcurementRecord::inventoryStatuses(),
            'locations'         => self::locations(),
            'staffUsers'        => $staffUsers,
        ]);
    }
}
