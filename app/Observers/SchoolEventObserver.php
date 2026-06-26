<?php

namespace App\Observers;

use App\Models\SchoolEvent;
use App\Support\WhatsAppService;
use App\Support\TenantSettings;
use App\Models\User;

class SchoolEventObserver
{
    /**
     * Handle the SchoolEvent "created" event.
     */
    public function created(SchoolEvent $schoolEvent): void
    {
        $tenant = $schoolEvent->tenant;
        if ($tenant) {
            if (!app()->bound('currentTenant')) {
                app()->instance('currentTenant', $tenant);
            }
            TenantSettings::loadToConfig();
        }

        // Fetch all parents in this school who have registered a WhatsApp number and are subscribed
        $parents = User::where('role', 'parent')
            ->where('whatsapp_subscribed', true)
            ->whereNotNull('whatsapp_phone')
            ->get();

        if ($parents->isEmpty()) {
            return;
        }

        $eventTitle = $schoolEvent->title;
        $eventDesc = $schoolEvent->description ?: 'No description provided.';
        $eventLoc = $schoolEvent->location ?: 'School Campus';
        $eventTime = $schoolEvent->starts_at ? $schoolEvent->starts_at->format('d M Y, h:i A') : 'TBD';

        $message = "📅 *NEW SCHOOL EVENT*\n\n" .
                   "🎒 *{$eventTitle}*\n" .
                   "📝 *About:* {$eventDesc}\n" .
                   "📍 *Location:* {$eventLoc}\n" .
                   "🕒 *Date & Time:* {$eventTime}\n\n" .
                   "Would you like to attend? Please RSVP using the options below:";

        $buttons = [
            ['id' => "rsvp_evt_{$schoolEvent->id}|yes", 'title' => "🙋 Yes, I'll attend"],
            ['id' => "rsvp_evt_{$schoolEvent->id}|no",  'title' => "🙅 No, I can't"]
        ];

        foreach ($parents as $parent) {
            WhatsAppService::sendMessage(
                $parent->whatsapp_phone,
                $message,
                null,
                null,
                null,
                $buttons
            );
        }
    }

    /**
     * Handle the SchoolEvent "updated" event.
     */
    public function updated(SchoolEvent $schoolEvent): void
    {
        //
    }

    /**
     * Handle the SchoolEvent "deleted" event.
     */
    public function deleted(SchoolEvent $schoolEvent): void
    {
        //
    }

    /**
     * Handle the SchoolEvent "restored" event.
     */
    public function restored(SchoolEvent $schoolEvent): void
    {
        //
    }

    /**
     * Handle the SchoolEvent "force deleted" event.
     */
    public function forceDeleted(SchoolEvent $schoolEvent): void
    {
        //
    }
}
