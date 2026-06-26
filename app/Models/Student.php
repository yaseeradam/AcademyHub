<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\Homework;
use Laravel\Sanctum\HasApiTokens;

class Student extends Model
{
    use HasApiTokens;
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'admission_number',
        'first_name',
        'last_name',
        'class_id',
        'section_id',
        'gender',
        'dob',
        'blood_group',
        'guardian_name',
        'guardian_phone',
        'guardian_address',
        'passport_photo',
        'status',
        'custom_fields',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'user_id' => 'integer',
        'class_id' => 'integer',
        'section_id' => 'integer',
        'dob' => 'date',
        'password' => 'hashed',
        'custom_fields' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function cbtAttempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'user_id')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getPassportPhotoUrlAttribute(): ?string
    {
        if (! $this->passport_photo) {
            return null;
        }

        if (filter_var($this->passport_photo, FILTER_VALIDATE_URL)) {
            return $this->passport_photo;
        }

        // Handle both forward and backward slashes
        $path = str_replace(['\\', '\\\\'], '/', $this->passport_photo);
        
        // Remove 'uploads/' prefix if it exists
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, 8);
        }

        return asset('uploads/'.$path);
    }

    public function getRouteKeyName(): string
    {
        return 'admission_number';
    }

    public function subjectOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subject_overrides')
            ->withPivot('action')
            ->withTimestamps();
    }

    public function getAssignedSubjectsAttribute()
    {
        if (!$this->schoolClass) {
            return collect();
        }

        $classSubjects = $this->schoolClass->defaultSubjects->pluck('id');
        $overrides = $this->subjectOverrides;
        
        $removed = $overrides->where('pivot.action', 'remove')->pluck('id');
        $added = $overrides->where('pivot.action', 'add')->pluck('id');
        
        return Subject::query()
            ->whereIn('id', $classSubjects->diff($removed)->merge($added))
            ->orderBy('name')
            ->get();
    }

    public function homeworkSubmissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function attendanceMarks(): HasMany
    {
        return $this->hasMany(AttendanceMark::class);
    }

    public function getHomeworkForStudent()
    {
        return Homework::where('class_id', $this->class_id)
            ->where(function($query) {
                $query->whereNull('section_id')
                      ->orWhere('section_id', $this->section_id);
            })
            ->with(['subject', 'teacher', 'submissions' => function($query) {
                $query->where('student_id', $this->id);
            }])
            ->orderBy('due_date', 'desc')
            ->get();
    }
}
