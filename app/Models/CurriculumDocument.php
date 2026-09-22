<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumDocument extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'subject_id',
        'term',
        'session',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'uploaded_by',
    ];

    protected $casts = [
        'term' => 'integer',
        'file_size' => 'integer',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }

    public function scopeForClassSubjectTerm($query, int $classId, int $subjectId, int $term, ?string $session = null)
    {
        $query->where('class_id', $classId)
              ->where('subject_id', $subjectId)
              ->where('term', $term);

        if (! empty($session)) {
            $query->where('session', $session);
        }

        return $query;
    }
}
