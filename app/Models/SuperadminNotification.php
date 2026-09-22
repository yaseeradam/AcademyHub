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
     * Create an "app rating/review submitted" notification.
     */
    public static function notifyAppRating(Tenant $tenant, string $appName, int $rating, ?string $comment): void
    {
        $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
        static::create([
            'tenant_id'  => $tenant->id,
            'type'       => 'app_rating',
            'title'      => "New App Rating ({$stars})",
            'message'    => "{$tenant->name} rated '{$appName}' {$rating}/5 stars." . ($comment ? " Comment: \"{$comment}\"" : ""),
            'action_url' => route('superadmin.marketplace.index'),
        ]);
    }

    /**
     * Create a "support ticket created" notification.
     */
    public static function notifySupportTicket(Tenant $tenant, SupportTicket $ticket): void
    {
        static::create([
            'tenant_id'  => $tenant->id,
            'type'       => 'support_ticket',
            'title'      => "New Support Ticket (#{$ticket->id})",
            'message'    => "{$tenant->name} created a new support ticket via WhatsApp: \"{$ticket->message}\"",
            'action_url' => route('superadmin.dashboard'),
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

