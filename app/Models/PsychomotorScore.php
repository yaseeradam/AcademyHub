<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychomotorScore extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'class_id',
        'term',
        'session',
        'traits',
    ];

    protected $casts = [
        'tenant_id'  => 'integer',
        'student_id' => 'integer',
        'class_id'   => 'integer',
        'term'       => 'integer',
        'traits'     => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
