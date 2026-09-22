<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherAttendanceSheet extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'date',
        'term',
        'session',
        'taken_by',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'term' => 'integer',
        'date' => 'date:Y-m-d',
        'taken_by' => 'integer',
    ];

    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(TeacherAttendanceMark::class, 'sheet_id');
    }
}
