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
        'term',
        'session',
        'published_at',
        'published_by',
        'note',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'class_id' => 'integer',
        'term' => 'integer',
        'published_at' => 'datetime',
        'published_by' => 'integer',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function isPublished(int $classId, int $term, string $session): bool
    {
        return self::query()
            ->where('class_id', $classId)
            ->where('term', $term)
            ->where('session', $session)
            ->whereNotNull('published_at')
            ->exists();
    }

    public static function publishedTermsForClass(int $classId, string $session): array
    {
        return self::query()
            ->where('class_id', $classId)
            ->where('session', $session)
            ->whereNotNull('published_at')
            ->pluck('term')
            ->map(fn($t) => (int) $t)
            ->unique()
            ->values()
            ->toArray();
    }
}
