<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SubjectAllocation extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'teacher_id',
        'subject_id',
        'class_id',
        'section_id',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'teacher_id' => 'integer',
        'subject_id' => 'integer',
        'class_id' => 'integer',
        'section_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $alloc) {
            $exists = static::query()
                ->where('teacher_id', $alloc->teacher_id)
                ->where('subject_id', $alloc->subject_id)
                ->where('class_id', $alloc->class_id)
                ->where('section_id', $alloc->section_id)
                ->exists();

            if ($exists) {
                return false;
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function isClassWide(): bool
    {
        return is_null($this->section_id);
    }

    public function getSubclassLabelAttribute(): string
    {
        return $this->section ? $this->section->name : 'All Arms';
    }
}
