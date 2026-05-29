<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtAttempt extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'exam_id',
        'student_id',
        'ip_address',
        'allowed_ip',
        'started_at',
        'last_activity_at',
        'submitted_at',
        'terminated_at',
        'terminated_by',
        'termination_reason',
        'score',
        'max_score',
        'percent',
        'theory_status',
        'assigned_teacher_id',
        'forwarded_at',
        'marked_at',
        'transferred_at',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'exam_id' => 'integer',
        'student_id' => 'integer',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'submitted_at' => 'datetime',
        'terminated_at' => 'datetime',
        'terminated_by' => 'integer',
        'score' => 'integer',
        'max_score' => 'integer',
        'percent' => 'decimal:2',
        'assigned_teacher_id' => 'integer',
        'forwarded_at' => 'datetime',
        'marked_at' => 'datetime',
        'transferred_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'exam_id');
    }

    protected static function booted(): void
    {
        static::saved(function ($attempt) {
            if ($attempt->student_id) {
                \App\Support\StudentPerformanceService::clearCache($attempt->student_id);
            }
        });

        static::deleted(function ($attempt) {
            if ($attempt->student_id) {
                \App\Support\StudentPerformanceService::clearCache($attempt->student_id);
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CbtAnswer::class, 'attempt_id');
    }

    public function assignedTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }
}
