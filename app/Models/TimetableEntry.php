<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'section_id',
        'day_of_week',
        'starts_at',
        'ends_at',
        'subject_id',
        'teacher_id',
        'room',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'class_id' => 'integer',
        'section_id' => 'integer',
        'day_of_week' => 'integer',
        'teacher_id' => 'integer',
        'starts_at' => 'string',
        'ends_at' => 'string',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
