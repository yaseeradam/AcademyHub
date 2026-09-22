<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->tenant_id !== $targetUser->tenant_id) {
            return false;
        }

        return $currentUser->role === 'admin' || $currentUser->id === $targetUser->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->tenant_id !== $targetUser->tenant_id) {
            return false;
        }

        return $currentUser->role === 'admin' || $currentUser->id === $targetUser->id;
    }

    public function delete(User $currentUser, User $targetUser): bool
    {
        if ($currentUser->tenant_id !== $targetUser->tenant_id) {
            return false;
        }

        return $currentUser->role === 'admin' && $currentUser->id !== $targetUser->id;
    }
}
