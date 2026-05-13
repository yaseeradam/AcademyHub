<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Grace period in days before features are disabled.
     * After this, features are soft-disabled but the system stays up.
     * Hard lockdown (redirect to billing) only happens after LOCKDOWN_DAYS.
     */
    const GRACE_DAYS    = 7;
    const LOCKDOWN_DAYS = 21;  // 3 weeks past due = hard redirect to billing

    public function handle(Request $request, Closure $next): Response
    {
        // Super admin routes are never subject to subscription checks.
        if ($request->is('superadmin', 'superadmin/*')) {
            return $next($request);
        }

        $settingsPath = TenantSettings::settingsPath();
        $settings     = file_exists($settingsPath)
            ? (json_decode(file_get_contents($settingsPath), true) ?: [])
            : [];

        // If no due date is set, treat as valid for 1 year (new school)
        $dueDate = !empty($settings['subscription_due_date'])
            ? \Carbon\Carbon::parse($settings['subscription_due_date'])
            : now()->addYear();

        $now         = now();
        $isPastDue   = $dueDate->isPast();
        $daysPastDue = $isPastDue ? (int) $now->diffInDays($dueDate) : 0;
        $daysUntilDue = !$isPastDue ? (int) $now->diffInDays($dueDate) : 0;

        // Grace period: 0–GRACE_DAYS past due → show warning modal, all features work
        $inGracePeriod = $isPastDue && $daysPastDue <= self::GRACE_DAYS;

        // Restricted period: GRACE_DAYS+1 to LOCKDOWN_DAYS → features disabled, modal shown
        $featuresDisabled = $isPastDue && $daysPastDue > self::GRACE_DAYS && $daysPastDue <= self::LOCKDOWN_DAYS;

        // Hard lockdown: > LOCKDOWN_DAYS → redirect to billing only
        $hardLocked = $isPastDue && $daysPastDue > self::LOCKDOWN_DAYS;

        // Share with all views
        View::share([
            'subscriptionIsPastDue'    => $isPastDue,
            'subscriptionDaysPastDue'  => $daysPastDue,
            'subscriptionDaysUntilDue' => $daysUntilDue,
            'subscriptionDueDate'      => $dueDate,
            'subscriptionInGrace'      => $inGracePeriod,
            'subscriptionFeaturesDisabled' => $featuresDisabled,
            'subscriptionHardLocked'   => $hardLocked,
        ]);

        // Hard lockdown — only billing, login, logout allowed
        if ($hardLocked) {
            $allowedRoutes = ['billing.index', 'logout', 'login', 'login.store'];
            if (!$request->routeIs($allowedRoutes) && !$request->is('livewire/update')) {
                return redirect()->route('billing.index')
                    ->with('error', 'Your subscription expired over ' . self::LOCKDOWN_DAYS . ' days ago. Please renew to continue.');
            }
        }

        return $next($request);
    }
}
