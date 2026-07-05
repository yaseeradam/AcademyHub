<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'name',
        'level',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_allocations', 'class_id', 'subject_id')
            ->withTimestamps()
            ->distinct()
            ->orderBy('name');
    }

    public function defaultSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withPivot('is_core')
            ->withTimestamps()
            ->orderBy('name');
    }

    public static function allSubjectsForClass(int $classId): \Illuminate\Support\Collection
    {
        $class = self::find($classId);
        if (!$class) {
            return collect();
        }

        // Get subjects from default subjects (class_subject table)
        $defaultSubjects = $class->defaultSubjects()->get();

        // Get subjects from allocations (subject_allocations table)
        $allocatedSubjects = $class->subjects()->get();

        // Get subjects that have scores recorded for this class
        $scoreSubjectIds = \App\Models\Score::query()
            ->where('class_id', $classId)
            ->distinct()
            ->pluck('subject_id')
            ->toArray();
        $scoreSubjects = \App\Models\Subject::query()
            ->whereIn('id', $scoreSubjectIds)
            ->get();

        // Combine all and make unique by ID
        return collect()
            ->concat($defaultSubjects)
            ->concat($allocatedSubjects)
            ->concat($scoreSubjects)
            ->unique('id')
            ->sortBy('name')
            ->values();
    }
}
