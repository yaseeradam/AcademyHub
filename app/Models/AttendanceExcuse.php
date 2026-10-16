<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceExcuse extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'parent_id',
        'start_date',
        'end_date',
        'reason',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'tenant_id'   => 'integer',
        'student_id'  => 'integer',
        'parent_id'   => 'integer',
        'start_date'  => 'date:Y-m-d',
        'end_date'    => 'date:Y-m-d',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('start_date', '<=', $date)
                     ->where('end_date', '>=', $date);
    }

    public function scopeActiveForStudent($query, int $studentId, string $date)
    {
        return $query->where('student_id', $studentId)
                     ->where('start_date', '<=', $date)
                     ->where('end_date', '>=', $date);
    }
}
