<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'name',
        'shift',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'class_id' => 'integer',
        'shift' => 'string',
    ];

    public function getShift(): string
    {
        return \App\Support\AttendanceShiftConfig::normalizeShift($this->shift ?? 'Western');
    }

    public function getShiftLabel(): string
    {
        return $this->getShift() . ' Section';
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function subjectAllocations(): HasMany
    {
        return $this->hasMany(SubjectAllocation::class, 'section_id');
    }
}
