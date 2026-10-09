<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProcurementRecord extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'item_name',
        'category',
        'quantity',
        'unit',
        'unit_price',
        'total_amount',
        'purchased_at',
        'purchaser_id',
        'purchaser_name',
        'vendor_name',
        'vendor_phone',
        'payment_method',
        'receipt_number',
        'receipt_attachment_path',
        'status',
        'approved_by',
        'is_inventory_item',
        'inventory_location',
        'inventory_status',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'quantity'          => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'total_amount'      => 'decimal:2',
        'purchased_at'      => 'date',
        'is_inventory_item' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $record) {
            if ($record->quantity !== null && $record->unit_price !== null && (float) $record->quantity > 0 && (float) $record->unit_price > 0) {
                $record->total_amount = round((float) $record->quantity * (float) $record->unit_price, 2);
            }
        });
    }

    public static function categories(): array
    {
        return [
            'Furniture & Fittings',
            'IT & Electronics',
            'Stationery & Office Supplies',
            'Books & Curriculum',
            'Laboratory & Science Equipment',
            'Facility, Fuel & Generator',
            'Sports & Extracurricular',
            'Medical & First Aid',
            'Food & Canteen',
            'General & Miscellaneous',
        ];
    }

    public static function paymentMethods(): array
    {
        return [
            'Cash',
            'Bank Transfer',
            'POS / Debit Card',
            'Cheque',
            'Credit / Pay Later',
        ];
    }

    public static function inventoryStatuses(): array
    {
        return [
            'in_use'      => 'In Active Use',
            'in_stock'    => 'In Storage / Stock',
            'maintenance' => 'Under Maintenance / Repair',
            'depleted'    => 'Depleted / Consumed',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function purchaser()
    {
        return $this->belongsTo(User::class, 'purchaser_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getPurchaserDisplayNameAttribute(): string
    {
        return $this->purchaser?->name ?? $this->purchaser_name ?? 'School Admin';
    }

    public function getFormattedAmountAttribute(): string
    {
        return config('academyhub.currency_symbol', '₦') . number_format((float) $this->total_amount, 2);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (empty($this->receipt_attachment_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->receipt_attachment_path);
    }

    public function scopeForTenant($query, $tenantId = null)
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('item_name', 'like', "%{$term}%")
              ->orWhere('vendor_name', 'like', "%{$term}%")
              ->orWhere('receipt_number', 'like', "%{$term}%")
              ->orWhere('purchaser_name', 'like', "%{$term}%")
              ->orWhereHas('purchaser', function ($userQuery) use ($term) {
                  $userQuery->where('name', 'like', "%{$term}%");
              });
        });
    }

    public function scopeByCategory($query, ?string $category)
    {
        if (! $category || $category === 'all') return $query;
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, ?string $from, ?string $to)
    {
        if ($from) $query->whereDate('purchased_at', '>=', $from);
        if ($to) $query->whereDate('purchased_at', '<=', $to);
        return $query;
    }

    public function scopeInventoryOnly($query)
    {
        return $query->where('is_inventory_item', true);
    }

    public function scopeByLocation($query, ?string $location)
    {
        if (!$location || $location === 'all') return $query;
        return $query->where('inventory_location', $location);
    }

    public function scopeByInventoryStatus($query, ?string $status)
    {
        if (!$status || $status === 'all') return $query;
        return $query->where('inventory_status', $status);
    }
}
