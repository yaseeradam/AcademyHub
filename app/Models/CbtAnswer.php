<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtAnswer extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'attempt_id',
        'question_id',
        'option_id',
        'is_correct',
        'text_answer',
        'awarded_marks',
        'teacher_comment',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'attempt_id' => 'integer',
        'question_id' => 'integer',
        'option_id' => 'integer',
        'is_correct' => 'boolean',
        'text_answer' => 'string',
        'awarded_marks' => 'integer',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(CbtAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(CbtOption::class, 'option_id');
    }
}
