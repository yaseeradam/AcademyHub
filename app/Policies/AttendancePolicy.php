<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }

    public function view(User $user, Model $model): bool
    {
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        return in_array($user->role, ['admin', 'teacher']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher']);
    }

    public function update(User $user, Model $model): bool
    {
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        return in_array($user->role, ['admin', 'teacher']);
    }

    public function delete(User $user, Model $model): bool
    {
        if ($user->tenant_id !== $model->tenant_id) {
            return false;
        }

        return $user->role === 'admin';
    }
}
