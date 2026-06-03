<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtExam extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'exam_type',
        'title',
        'description',
        'class_id',
        'subject_id',
        'term',
        'session',
        'duration_minutes',
        'status',
        'access_code',
        'created_by',
        'assigned_teacher_id',
        'requested_by',
        'requested_at',
        'request_note',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'note',
        'published_at',
        'results_released_at',
        'starts_at',
        'ends_at',
        'pin',
        'grace_minutes',
        'allowed_cidrs',
        'show_score',
        'shuffle_questions',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'class_id' => 'integer',
        'subject_id' => 'integer',
        'term' => 'integer',
        'duration_minutes' => 'integer',
        'created_by' => 'integer',
        'assigned_teacher_id' => 'integer',
        'requested_by' => 'integer',
        'requested_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'results_released_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_minutes' => 'integer',
        'show_score' => 'boolean',
        'shuffle_questions' => 'boolean',
    ];


    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CbtQuestion::class, 'exam_id')->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class, 'exam_id');
    }
}
