<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtQuestion extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'exam_id',
        'type',
        'prompt',
        'marks',
        'position',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'exam_id' => 'integer',
        'marks' => 'integer',
        'position' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'exam_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(CbtOption::class, 'question_id')->orderBy('position');
    }
}
