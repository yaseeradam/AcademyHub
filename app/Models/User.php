<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
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
        // 'is_super_admin' — intentionally excluded to prevent mass-assignment privilege escalation.
        // Set explicitly via artisan commands or seeders only.
        // 'tenant_id' — intentionally excluded. Tenant assignment is handled by the BelongsToTenant
        // trait and the creating() model event only. Never set via user-supplied input.
        'profile_photo',
        'permissions',
        'custom_fields',
        'whatsapp_phone',
        'whatsapp_verified',
        'whatsapp_subscribed',
        'is_class_teacher',
        'shift',
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
        'email_verified_at'  => 'datetime',
        'password'           => 'hashed',
        'is_active'          => 'boolean',
        'is_super_admin'     => 'boolean',
        'tenant_id'          => 'integer',
        'permissions'        => 'array',
        'custom_fields'      => 'array',
        'whatsapp_verified'   => 'boolean',
        'whatsapp_subscribed' => 'boolean',
        'is_class_teacher'    => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('request') && request()->is('superadmin', 'superadmin/*')) {
                return;
            }

            if (! app()->bound('currentTenant')) {
                return;
            }

            $tenant = app('currentTenant');
            $tenantId = $tenant && isset($tenant->id) ? (int) $tenant->id : null;
            if (! $tenantId) {
                return;
            }

            // Strict tenant isolation — super admins are intentionally excluded from tenant queries.
            $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
        });

        static::creating(function (self $user) {
            if ($user->is_super_admin) {
                return;
            }

            if (! empty($user->tenant_id)) {
                return;
            }

            if (! app()->bound('currentTenant')) {
                return;
            }

            $tenant = app('currentTenant');
            if ($tenant && isset($tenant->id)) {
                $user->tenant_id = (int) $tenant->id;
            }
        });
    }

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
            if ($this->role === 'admin' || $this->role === 'proprietor' || $this->is_super_admin) {
                return asset('avatars/Admin.png');
            }
            return asset('avatars/teachers.png');
        }

        $path = str_replace('\\', '/', $this->profile_photo);

        return asset('uploads/'.$path);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isProprietor(): bool
    {
        return $this->role === 'proprietor';
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

    public function children()
    {
        return $this->students();
    }

    /**
     * Returns 'Islamic' or 'Western' based on user assignment.
     */
    public function getShift(): string
    {
        $shift = $this->custom_fields['shift'] ?? $this->shift;
        return \App\Support\AttendanceShiftConfig::normalizeShift($shift);
    }

    /**
     * Returns human-readable section label.
     */
    public function getShiftLabel(): string
    {
        return $this->getShift() . ' Section';
    }

    /**
     * Returns matching hardware biometric UID on K40 device.
     */
    public function getK40Uid(): ?int
    {
        return isset($this->custom_fields['k40_uid']) ? (int) $this->custom_fields['k40_uid'] : $this->id;
    }

    /**
     * Get clean international digits for WhatsApp (e.g. 2348012345678).
     */
    public function getCleanWhatsappPhoneAttribute(): ?string
    {
        $phone = $this->whatsapp_phone ?? ($this->custom_fields['phone'] ?? ($this->custom_fields['whatsapp'] ?? ($this->custom_fields['whatsapp_phone'] ?? null)));
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $phone);
        if (empty($digits)) {
            return null;
        }

        // Convert local Nigerian numbers starting with '0' (e.g. 08012345678) to international prefix 234
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = '234' . substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Get human-friendly formatted WhatsApp phone display (e.g. +234 801 234 5678).
     */
    public function getFormattedWhatsappPhoneAttribute(): ?string
    {
        $clean = $this->clean_whatsapp_phone;
        if (! $clean) {
            return null;
        }

        if (str_starts_with($clean, '234') && strlen($clean) === 13) {
            return '+234 ' . substr($clean, 3, 3) . ' ' . substr($clean, 6, 3) . ' ' . substr($clean, 9);
        }

        return '+' . $clean;
    }

    /**
     * Generate direct WhatsApp click-to-chat URL with optional pre-filled greeting.
     */
    public function getWhatsappChatUrl(?string $message = null): ?string
    {
        $clean = $this->clean_whatsapp_phone;
        if (! $clean) {
            return null;
        }

        $url = "https://wa.me/{$clean}";
        if ($message !== null && trim($message) !== '') {
            $url .= '?text=' . rawurlencode(trim($message));
        }

        return $url;
    }
}

