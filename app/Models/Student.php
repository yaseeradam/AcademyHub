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
        'k40_uid',
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
        // 'password' — intentionally excluded from mass assignment.
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'tenant_id'    => 'integer',
        'user_id'      => 'integer',
        'class_id'     => 'integer',
        'section_id'   => 'integer',
        'k40_uid'      => 'integer',
        'dob'          => 'date',
        'password'     => 'hashed',
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

    public function attendanceExcuses(): HasMany
    {
        return $this->hasMany(AttendanceExcuse::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getPassportPhotoUrlAttribute(): ?string
    {
        $avatarFallback = in_array(strtolower($this->gender ?? ''), ['female', 'f', 'girl'])
            ? '/avatars/girl_student_pink.png'
            : '/avatars/student_blue.png';

        $rawPhoto = $this->passport_photo;

        if (! $rawPhoto) {
            $customPhotoUrl = $this->custom_fields['photo_url'] ?? null;
            if ($customPhotoUrl && filter_var($customPhotoUrl, FILTER_VALIDATE_URL) && ! str_contains($customPhotoUrl, 'portal.academyhub.com.ng')) {
                return $customPhotoUrl;
            }
            return $avatarFallback;
        }

        // Check for dead external host
        if (str_contains($rawPhoto, 'portal.academyhub.com.ng')) {
            $adm = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $this->admission_number);
            if (! empty($adm)) {
                $matches = glob(public_path("uploads/Students/{$adm}*"));
                if (! empty($matches)) {
                    return '/uploads/Students/' . basename($matches[0]);
                }
            }
            return $avatarFallback;
        }

        if (filter_var($rawPhoto, FILTER_VALIDATE_URL)) {
            return $rawPhoto;
        }

        // Handle both forward and backward slashes
        $path = str_replace(['\\', '\\\\'], '/', $rawPhoto);
        
        // Remove 'uploads/' prefix if it exists
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, 8);
        }

        // Check if file actually exists on uploads disk or public path
        if (file_exists(public_path('uploads/' . $path))) {
            return '/uploads/'.$path;
        }

        if (file_exists(public_path($path))) {
            return '/'.$path;
        }

        // Fallback: check matching file in Students directory
        $adm = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $this->admission_number);
        if (! empty($adm)) {
            $matches = glob(public_path("uploads/Students/{$adm}*"));
            if (! empty($matches)) {
                return '/uploads/Students/' . basename($matches[0]);
            }
        }

        $customPhotoUrl = $this->custom_fields['photo_url'] ?? null;
        if ($customPhotoUrl && filter_var($customPhotoUrl, FILTER_VALIDATE_URL) && ! str_contains($customPhotoUrl, 'portal.academyhub.com.ng')) {
            return $customPhotoUrl;
        }

        return $avatarFallback;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->passport_photo_url;
    }

    public function hasRealPhoto(): bool
    {
        $rawPhoto = $this->passport_photo;
        if (! $rawPhoto) {
            $customPhotoUrl = $this->custom_fields['photo_url'] ?? null;
            return ! empty($customPhotoUrl) && filter_var($customPhotoUrl, FILTER_VALIDATE_URL) && ! str_contains($customPhotoUrl, 'portal.academyhub.com.ng');
        }

        if (str_contains($rawPhoto, 'portal.academyhub.com.ng')) {
            $adm = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $this->admission_number);
            if (! empty($adm)) {
                $matches = glob(public_path("uploads/Students/{$adm}*"));
                return ! empty($matches);
            }
            return false;
        }

        if (filter_var($rawPhoto, FILTER_VALIDATE_URL)) {
            return true;
        }

        $path = str_replace(['\\', '\\\\'], '/', $rawPhoto);
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, 8);
        }

        if (file_exists(public_path('uploads/' . $path)) || file_exists(public_path($path))) {
            return true;
        }

        $adm = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $this->admission_number);
        if (! empty($adm)) {
            $matches = glob(public_path("uploads/Students/{$adm}*"));
            return ! empty($matches);
        }

        return false;
    }

    public function getHasRealPhotoAttribute(): bool
    {
        return $this->hasRealPhoto();
    }

    /**
     * Get Base64 Data URI for PDF report cards and booklets.
     * Prevents broken image icons and avoids external network latency in DomPDF.
     */
    public function getPassportPhotoDataUriAttribute(): ?string
    {
        $photoUrl = $this->passport_photo_url;
        if (! $photoUrl) {
            return null;
        }

        $filePath = null;
        if (str_starts_with($photoUrl, '/uploads/')) {
            $filePath = public_path(substr($photoUrl, 1));
        } elseif (str_starts_with($photoUrl, '/avatars/')) {
            $filePath = public_path(substr($photoUrl, 1));
        } elseif (str_starts_with($photoUrl, '/')) {
            $filePath = public_path(substr($photoUrl, 1));
        }

        if ($filePath && file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/jpeg',
            };
            $content = @file_get_contents($filePath);
            if ($content !== false && $content !== '') {
                return 'data:' . $mime . ';base64,' . base64_encode($content);
            }
        }

        if (filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            return $photoUrl;
        }

        return null;
    }

    public function getRouteKeyName(): string
    {
        return 'admission_number';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            $student = $this->where('id', (int) $value)->first();
            if ($student) {
                return $student;
            }
        }

        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    public function subjectOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subject_overrides')
            ->withPivot('action')
            ->withTimestamps();
    }

    public static array $classSubjectsCache = [];

    public function getAssignedSubjectsAttribute()
    {
        if (! $this->class_id) {
            return collect();
        }

        if (! isset(static::$classSubjectsCache[$this->class_id])) {
            static::$classSubjectsCache[$this->class_id] = SchoolClass::allSubjectsForClass($this->class_id);
        }

        $allClassSubjects = static::$classSubjectsCache[$this->class_id];
        $classSubjectIds = $allClassSubjects->pluck('id');

        $overrides = $this->relationLoaded('subjectOverrides') ? $this->subjectOverrides : $this->subjectOverrides()->get();
        if ($overrides->isEmpty()) {
            return $allClassSubjects;
        }

        $removed = $overrides->where('pivot.action', 'remove')->pluck('id')->all();
        $added = $overrides->where('pivot.action', 'add')->pluck('id')->all();

        $finalIds = $classSubjectIds->diff($removed)->merge($added)->unique();

        return Subject::query()
            ->whereIn('id', $finalIds)
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

    public function getShift(): string
    {
        if (!empty($this->custom_fields['shift'])) {
            return \App\Support\AttendanceShiftConfig::normalizeShift($this->custom_fields['shift']);
        }

        if ($this->relationLoaded('section') && $this->section) {
            return $this->section->getShift();
        }

        if ($this->section_id) {
            $section = Section::find($this->section_id);
            if ($section) {
                return $section->getShift();
            }
        }

        return \App\Support\AttendanceShiftConfig::SHIFT_WESTERN;
    }

    public function getShiftLabel(): string
    {
        return $this->getShift() . ' Section';
    }

    /**
     * Return the hardware UID used for this student on the ZKTeco K40 device.
     * Matches the value that the device sends as USERID in ADMS punch data.
     * Priority: explicit k40_uid column → fall back to database id only if >= 200.
     * UIDs 1 to 50 are reserved for staff on K40 hardware.
     */
    public function getK40Uid(): ?int
    {
        if ($this->k40_uid) {
            return (int) $this->k40_uid;
        }

        return $this->id >= 200 ? (int) $this->id : null;
    }
}
