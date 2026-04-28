<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyDataCollection extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'section_id',
        'teacher_id',
        'term',
        'session',
        'week_start',
        'week_end',
        'boys_present',
        'boys_absent',
        'girls_present',
        'girls_absent',
        'school_days',
        'note',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_note',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'class_id' => 'integer',
        'section_id' => 'integer',
        'teacher_id' => 'integer',
        'term' => 'integer',
        'week_start' => 'date:Y-m-d',
        'week_end' => 'date:Y-m-d',
        'boys_present' => 'integer',
        'boys_absent' => 'integer',
        'girls_present' => 'integer',
        'girls_absent' => 'integer',
        'school_days' => 'integer',
        'submitted_at' => 'datetime',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
