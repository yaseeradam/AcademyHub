<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperadminNotification extends Model
{
    protected $fillable = [
        'tenant_id',
        'type',
        'title',
        'message',
        'action_url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    // ── Static helpers ─────────────────────────────────────────────────

    /**
     * Create a "payout request submitted" notification when a school submits bank details.
     */
    public static function notifyPayoutRequest(Tenant $tenant): void
    {
        static::create([
            'tenant_id'  => $tenant->id,
            'type'       => 'payout_request',
            'title'      => 'New Payout Settlement Request',
            'message'    => "{$tenant->name} has submitted their bank account details and is requesting payout activation.",
            'action_url' => route('superadmin.tenants.edit', $tenant) . '#payout',
        ]);
    }

    /**
     * Count unread notifications for topbar badge.
     */
    public static function unreadCount(): int
    {
        return static::whereNull('read_at')->count();
    }
}
