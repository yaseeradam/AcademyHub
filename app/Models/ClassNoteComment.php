<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassNoteComment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_note_id',
        'user_id',
        'student_id',
        'comment',
    ];

    protected $casts = [
        'tenant_id'     => 'integer',
        'class_note_id' => 'integer',
        'user_id'       => 'integer',
        'student_id'    => 'integer',
    ];

    protected $appends = ['commenter_name'];

    public function classNote(): BelongsTo
    {
        return $this->belongsTo(ClassNote::class, 'class_note_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function getCommenterNameAttribute(): string
    {
        if ($this->student) {
            return $this->student->full_name;
        }
        if ($this->user) {
            return $this->user->name;
        }
        return 'System';
    }
}
