<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'label',
        'type',
        'form_type',
        'required',
        'options',
        'placeholder',
        'order',
        'is_active',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'required' => 'boolean',
        'options' => 'array',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
