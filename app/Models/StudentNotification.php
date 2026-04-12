<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentNotification extends Model
{
    protected $fillable = [
        'student_id',
        'title',
        'body',
        'type',
        'link',
        'read_at',
    ];

    protected $casts = [
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
