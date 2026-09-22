<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->tenant_id !== $student->tenant_id) {
            return false;
        }

        if (in_array($user->role, ['admin', 'teacher'])) {
            return true;
        }

        if ($user->role === 'parent') {
            return $user->students()->where('student_id', $student->id)->exists();
        }

        if ($user->id === $student->user_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->tenant_id !== $student->tenant_id) {
            return false;
        }

        return $user->role === 'admin';
    }

    public function delete(User $user, Student $student): bool
    {
        if ($user->tenant_id !== $student->tenant_id) {
            return false;
        }

        return $user->role === 'admin';
    }
}
