<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceMark extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sheet_id',
        'student_id',
        'status',
        'note',
        'arrived_at',
        'departed_at',
    ];

    protected $casts = [
        'tenant_id'   => 'integer',
        'sheet_id'    => 'integer',
        'student_id'  => 'integer',
        'arrived_at'  => 'string',
        'departed_at' => 'string',
    ];

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(AttendanceSheet::class, 'sheet_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
