<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendanceMark extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sheet_id',
        'teacher_id',
        'status',
        'note',
        'punch_in_time',
        'punch_out_time',
    ];

    protected $casts = [
        'tenant_id'     => 'integer',
        'sheet_id'      => 'integer',
        'teacher_id'    => 'integer',
        'punch_in_time' => 'string',
        'punch_out_time'=> 'string',
    ];

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(TeacherAttendanceSheet::class, 'sheet_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
