<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'type',
        'category',
        'term',
        'session',
        'amount_paid',
        'payment_method',
        'receipt_number',
        'is_void',
        'void_reason',
        'voided_at',
        'voided_by',
        'date',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'student_id' => 'integer',
        'term' => 'integer',
        'amount_paid' => 'decimal:2',
        'date' => 'date',
        'is_void' => 'boolean',
        'voided_at' => 'datetime',
        'voided_by' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $transaction) {
            if ($transaction->type === 'Income' && ! $transaction->receipt_number) {
                $transaction->receipt_number = self::nextReceiptNumber();
            }
        });
    }

    public static function nextReceiptNumber(): string
    {
        // Use database-level locking to prevent duplicate receipt numbers
        // under concurrent requests.
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $latest = self::query()
                ->whereNotNull('receipt_number')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->value('receipt_number');

            if (! $latest) {
                return 'REC-001';
            }

            if (preg_match('/REC-(\d+)/', $latest, $m) !== 1) {
                return 'REC-001';
            }

            $next = (int) $m[1] + 1;

            return 'REC-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'receipt_number';
    }
}
