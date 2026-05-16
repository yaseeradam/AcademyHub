<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychomotorScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'term',
        'session',
        'traits',
    ];

    protected $casts = [
        'traits' => 'array',
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
