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
}
