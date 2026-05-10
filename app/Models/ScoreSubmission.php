<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreSubmission extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'teacher_id',
        'class_id',
        'subject_id',
        'term',
        'session',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'note',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'teacher_id' => 'integer',
        'class_id' => 'integer',
        'subject_id' => 'integer',
        'term' => 'integer',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'reviewed_by' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
