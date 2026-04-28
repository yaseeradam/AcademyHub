<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'boolean',
    ];

    use BelongsToTenant;

    public function terms(): HasMany
    {
        return $this->hasMany(AcademicTerm::class);
    }

    public static function activeName(): ?string
    {
        try {
            $name = self::query()->where('is_active', true)->value('name');
        } catch (\Throwable) {
            return null;
        }

        return is_string($name) && preg_match('/^\\d{4}\\/\\d{4}$/', $name) === 1 ? $name : null;
    }
}
