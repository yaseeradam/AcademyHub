<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'homework_id',
        'student_id',
        'submission',
        'attachment',
        'submitted_at',
        'grade',
        'feedback',
        'graded_at',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function homework()
    {
        return $this->belongsTo(Homework::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
