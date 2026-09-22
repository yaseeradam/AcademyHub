<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectTopic extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'subject_id',
        'term',
        'session',
        'week_number',
        'title',
        'learning_objectives',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tenant_id'   => 'integer',
        'class_id'    => 'integer',
        'subject_id'  => 'integer',
        'term'        => 'integer',
        'week_number' => 'integer',
        'created_by'  => 'integer',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForTerm(Builder $query, int $term): Builder
    {
        return $query->where('term', $term);
    }

    public function scopeForSession(Builder $query, string $session): Builder
    {
        return $query->where('session', $session);
    }
}
