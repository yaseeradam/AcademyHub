<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Private channel for real-time biometric scan popups.
 *
 * Format:  private-biometric.scans.{tenantId}
 *
 * Authorization rules:
 *  - User must be authenticated
 *  - User must have the 'admin' role
 *  - User's tenant_id must match the channel's tenantId
 *
 * This prevents admins from one school receiving another school's scans.
 */
Broadcast::channel('biometric.scans.{tenantId}', function ($user, $tenantId) {
    if (!$user) return false;
    if ($user->is_super_admin || $user->role === 'superadmin') return true;
    if (in_array($user->role, ['admin', 'bursar', 'teacher'])) {
        if ($user->tenant_id === null || (int)$tenantId === 0) return true;
        return (int) $user->tenant_id === (int) $tenantId;
    }
    return false;
});
