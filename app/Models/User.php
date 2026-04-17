<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'profile_photo',
        'permissions',
        'custom_fields',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'whatsapp_ai_key_hash',
    ];

    /**
     * Get the tenant that the user belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'permissions' => 'array',
        'custom_fields' => 'array',
    ];

    public function hasPermission(string $permission): bool
    {
        $permission = trim($permission);
        if ($permission === '') {
            return false;
        }

        $definitions = (array) config('permissions.definitions', []);
        $defaultRoles = (array) ($definitions[$permission]['roles'] ?? []);

        $allowed = in_array($this->role, $defaultRoles, true);

        $overrides = $this->permissions;
        if (! is_array($overrides)) {
            $overrides = [];
        }

        $grants = $overrides['grant'] ?? [];
        if (! is_array($grants)) {
            $grants = [];
        }
        $revokes = $overrides['revoke'] ?? [];
        if (! is_array($revokes)) {
            $revokes = [];
        }

        $grants = array_values(array_unique(array_filter(array_map('strval', $grants))));
        $revokes = array_values(array_unique(array_filter(array_map('strval', $revokes))));

        if (in_array($permission, $revokes, true)) {
            return false;
        }

        if (in_array($permission, $grants, true)) {
            return true;
        }

        return $allowed;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo) {
            return null;
        }

        $path = str_replace('\\', '/', $this->profile_photo);

        return asset('uploads/'.$path);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBursar(): bool
    {
        return $this->role === 'bursar';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'user_id', 'student_id')
            ->withTimestamps();
    }
}
