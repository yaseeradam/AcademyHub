<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicTerm extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'academic_session_id',
        'name',
        'term_number',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'academic_session_id' => 'integer',
        'term_number' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'boolean',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Static helpers                                                     */
    /* ------------------------------------------------------------------ */

    /**
     * Return the currently active term model (eager-loads session).
     */
    public static function active(): ?self
    {
        try {
            return self::query()
                ->with('academicSession')
                ->where('is_active', true)
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Return the active term number (1, 2 or 3). Falls back to 1.
     */
    public static function activeTermNumber(): int
    {
        try {
            $n = self::query()->where('is_active', true)->value('term_number');

            return is_int($n) && $n >= 1 && $n <= 3 ? $n : 1;
        } catch (\Throwable) {
            return 1;
        }
    }

    /**
     * Return the session name for the active term (e.g. "2026/2027").
     * Falls back to AcademicSession::activeName().
     */
    public static function activeSessionName(): ?string
    {
        try {
            $term = self::active();
            if ($term && $term->academicSession) {
                return $term->academicSession->name;
            }
        } catch (\Throwable) {
            // fall through
        }

        return AcademicSession::activeName();
    }
}
