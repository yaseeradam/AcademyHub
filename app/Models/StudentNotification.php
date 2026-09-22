<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentNotification extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'title',
        'body',
        'type',
        'link',
        'read_at',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'student_id' => 'integer',
        'read_at'    => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public static function send(int $studentId, string $title, string $body = '', string $type = 'general', string $link = ''): self
    {
        return self::create([
            'student_id' => $studentId,
            'title'      => $title,
            'body'       => $body ?: null,
            'type'       => $type,
            'link'       => $link ?: null,
        ]);
    }
}
