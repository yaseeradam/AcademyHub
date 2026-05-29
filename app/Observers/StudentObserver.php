<?php

namespace App\Observers;

use App\Models\Student;

class StudentObserver
{
    public function creating(Student $student): void
    {
        if (empty($student->password)) {
            $admissionSuffix = substr($student->admission_number, -4);
            $defaultPassword = strtolower($student->first_name) . $admissionSuffix;
            $student->password = \Illuminate\Support\Facades\Hash::make($defaultPassword);
        }
    }

    public function updating(Student $student): void
    {
        // If class is changing, clear subject overrides
        if ($student->isDirty('class_id') && $student->getOriginal('class_id') !== null) {
            $student->subjectOverrides()->detach();
        }
    }
}
