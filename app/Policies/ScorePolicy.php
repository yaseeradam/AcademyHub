<?php

namespace App\Policies;

use App\Models\Score;
use App\Models\User;

class ScorePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }

    public function view(User $user, Score $score): bool
    {
        if ($user->tenant_id !== $score->tenant_id) {
            return false;
        }

        if (in_array($user->role, ['admin', 'teacher'])) {
            return true;
        }

        if ($user->role === 'parent') {
            return $user->students()->where('student_id', $score->student_id)->exists();
        }

        if ($score->student && $score->student->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }

    public function update(User $user, Score $score): bool
    {
        if ($user->tenant_id !== $score->tenant_id) {
            return false;
        }

        return in_array($user->role, ['admin', 'teacher']);
    }

    public function delete(User $user, Score $score): bool
    {
        if ($user->tenant_id !== $score->tenant_id) {
            return false;
        }

        return $user->role === 'admin';
    }
}
