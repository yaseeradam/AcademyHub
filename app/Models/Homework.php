<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    use BelongsToTenant;

    protected $table = 'homework';
    
    protected $fillable = [
        'tenant_id',
        'teacher_id',
        'class_id',
        'section_id',
        'subject_id',
        'title',
        'content',
        'due_date',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'due_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function getStudentsForHomework()
    {
        $query = Student::where('class_id', $this->class_id)
            ->where('status', 'Active');
        
        if ($this->section_id) {
            $query->where('section_id', $this->section_id);
        }
        
        return $query->get();
    }
}
