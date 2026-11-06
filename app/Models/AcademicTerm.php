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

    /**
     * Return list of available term numbers and human-readable names for a given session.
     * If the session is the active session, only returns terms up to the active term.
     * If the session is a past session, returns all terms recorded for that session (or 1..3).
     *
     * @return array<int, string>
     */
    public static function availableTermsForSession(?string $sessionName = null): array
    {
        $termDefinitions = [
            1 => 'Term 1 (First Term)',
            2 => 'Term 2 (Second Term)',
            3 => 'Term 3 (Third Term)',
        ];

        $sessionName = $sessionName ?: self::activeSessionName();

        // Find session model
        $sessionModel = null;
        if ($sessionName) {
            try {
                $sessionModel = AcademicSession::query()
                    ->where('name', $sessionName)
                    ->first();
            } catch (\Throwable) {
                $sessionModel = null;
            }
        }

        $activeSessionName = AcademicSession::activeName();
        $isActiveSession = ($sessionModel && $sessionModel->is_active)
            || ($sessionName === $activeSessionName)
            || (!$sessionModel && empty($activeSessionName));

        if ($isActiveSession) {
            $activeTermNumber = self::activeTermNumber(); // 1, 2, or 3 based on is_active = true

            $allowed = [];
            foreach ($termDefinitions as $num => $label) {
                if ($num <= $activeTermNumber) {
                    $allowed[$num] = $label;
                }
            }

            return !empty($allowed) ? $allowed : [1 => 'Term 1 (First Term)'];
        }

        // For historical / past sessions, check if specific terms exist in DB for that session
        if ($sessionModel) {
            try {
                $sessionTerms = self::query()
                    ->where('academic_session_id', $sessionModel->id)
                    ->orderBy('term_number')
                    ->get();

                if ($sessionTerms->isNotEmpty()) {
                    $allowed = [];
                    foreach ($sessionTerms as $st) {
                        $num = (int) $st->term_number;
                        $allowed[$num] = $termDefinitions[$num] ?? "Term {$num}";
                    }
                    return $allowed;
                }
            } catch (\Throwable) {
                // fall back to default
            }
        }

        return $termDefinitions;
    }
}
