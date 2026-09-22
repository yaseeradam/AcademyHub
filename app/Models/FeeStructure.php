<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'category',
        'term',
        'session',
        'amount_due',
        'installment_plans',
    ];

    protected $casts = [
        'tenant_id'          => 'integer',
        'class_id'           => 'integer',
        'term'               => 'integer',
        'amount_due'         => 'decimal:2',
        'installment_plans'  => 'array',
    ];

    /**
     * Returns the set of enabled installment plans for this fee structure.
     * Full payment is always available.
     */
    public function enabledPlans(): array
    {
        $plans = $this->installment_plans ?? [];
        $enabled = ['full' => true];

        if (!empty($plans['two_installments'])) {
            $enabled['two_installments'] = true;
        }
        if (!empty($plans['monthly'])) {
            $enabled['monthly'] = true;
        }

        return $enabled;
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
