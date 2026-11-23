<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultPublication extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'section_id',
        'term',
        'session',
        'published_at',
        'ca1_published_at',
        'ca2_published_at',
        'exam_published_at',
        'published_by',
        'note',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'class_id' => 'integer',
        'section_id' => 'integer',
        'term' => 'integer',
        'published_at' => 'datetime',
        'ca1_published_at' => 'datetime',
        'ca2_published_at' => 'datetime',
        'exam_published_at' => 'datetime',
        'published_by' => 'integer',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function forScope(int $classId, int $term, string $session, ?int $sectionId = null)
    {
        $query = self::query()
            ->where('class_id', $classId)
            ->where('term', $term)
            ->where('session', $session);

        if ($sectionId !== null && (int) $sectionId > 0) {
            $query->where('section_id', (int) $sectionId);
        } else {
            $query->whereNull('section_id');
        }

        return $query;
    }

    public static function isPublished(int $classId, int $term, string $session, ?int $sectionId = null): bool
    {
        return self::forScope($classId, $term, $session, $sectionId)
            ->whereNotNull('published_at')
            ->exists();
    }

    public static function isCa1Published(int $classId, int $term, string $session, ?int $sectionId = null): bool
    {
        return self::forScope($classId, $term, $session, $sectionId)
            ->where(function ($q) {
                $q->whereNotNull('published_at')->orWhereNotNull('ca1_published_at');
            })
            ->exists();
    }

    public static function isCa2Published(int $classId, int $term, string $session, ?int $sectionId = null): bool
    {
        return self::forScope($classId, $term, $session, $sectionId)
            ->where(function ($q) {
                $q->whereNotNull('published_at')->orWhereNotNull('ca2_published_at');
            })
            ->exists();
    }

    public static function isExamPublished(int $classId, int $term, string $session, ?int $sectionId = null): bool
    {
        return self::forScope($classId, $term, $session, $sectionId)
            ->where(function ($q) {
                $q->whereNotNull('published_at')->orWhereNotNull('exam_published_at');
            })
            ->exists();
    }

    public static function isComponentPublished(string $component, int $classId, int $term, string $session, ?int $sectionId = null): bool
    {
        $field = match (strtolower(trim($component))) {
            'ca1' => 'ca1_published_at',
            'ca2' => 'ca2_published_at',
            'exam' => 'exam_published_at',
            default => 'published_at',
        };

        return self::forScope($classId, $term, $session, $sectionId)
            ->where(function ($q) use ($field) {
                $q->whereNotNull('published_at')->orWhereNotNull($field);
            })
            ->exists();
    }

    public static function anyPublished(int $classId, int $term, string $session, ?int $sectionId = null): bool
    {
        return self::forScope($classId, $term, $session, $sectionId)
            ->where(function ($q) {
                $q->whereNotNull('published_at')
                    ->orWhereNotNull('ca1_published_at')
                    ->orWhereNotNull('ca2_published_at')
                    ->orWhereNotNull('exam_published_at');
            })
            ->exists();
    }

    public static function publishedTermsForClass(int $classId, string $session, ?int $sectionId = null): array
    {
        $query = self::query()
            ->where('class_id', $classId)
            ->where('session', $session);

        if ($sectionId !== null && (int) $sectionId > 0) {
            $query->where('section_id', (int) $sectionId);
        } else {
            $query->whereNull('section_id');
        }

        return $query
            ->whereNotNull('published_at')
            ->pluck('term')
            ->map(fn($t) => (int) $t)
            ->unique()
            ->values()
            ->toArray();
    }
}
