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
        // 'tenant_id' — intentionally excluded. Handled by BelongsToTenant trait only.
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
        'password',
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

    public function setDobAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['dob'] = null;
            return;
        }

        if ($value instanceof \DateTimeInterface) {
            $this->attributes['dob'] = $value->format('Y-m-d');
            return;
        }

        $val = trim((string)$value);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $val, $m)) {
            $this->attributes['dob'] = sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
            return;
        }

        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $val, $m)) {
            $day = (int)$m[1];
            $month = (int)$m[2];
            $year = (int)$m[3];
            if (checkdate($month, $day, $year)) {
                $this->attributes['dob'] = sprintf('%04d-%02d-%02d', $year, $month, $day);
                return;
            }
        }

        try {
            $this->attributes['dob'] = \Carbon\Carbon::parse(str_replace('/', '-', $val))->format('Y-m-d');
        } catch (\Throwable) {
            $this->attributes['dob'] = null;
        }
    }

    public function setStatusAttribute($value): void
    {
        $val = trim((string)$value);
        if ($val === '') {
            $this->attributes['status'] = 'Active';
            return;
        }

        $norm = ucfirst(strtolower($val));
        if (in_array($norm, ['Active', 'Graduated', 'Expelled'], true)) {
            $this->attributes['status'] = $norm;
        } elseif (in_array(strtolower($val), ['graduated', 'grad', 'alumni'], true)) {
            $this->attributes['status'] = 'Graduated';
        } elseif (in_array(strtolower($val), ['expelled', 'withdrawn', 'suspended', 'left'], true)) {
            $this->attributes['status'] = 'Expelled';
        } else {
            $this->attributes['status'] = 'Active';
        }
    }

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
            $isFemale = in_array(strtolower($this->gender ?? ''), ['female', 'f', 'girl']);
            return $isFemale ? '/avatars/girl_student_pink.png' : '/avatars/student_blue.png';
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

        return '/uploads/'.$path;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->passport_photo_url;
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

        $classSubjects = SchoolClass::allSubjectsForClass($this->class_id)->pluck('id');
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
