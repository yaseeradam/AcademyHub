<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\TenantSettings;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Super admin routes don't belong to any tenant — skip subscription checks entirely.
        if ($request->is('superadmin', 'superadmin/*')) {
            return $next($request);
        }

        // Use the config values already loaded and cached by LoadTenantSettings middleware
        // instead of redundantly reading the JSON file from disk.
        $dueDateRaw = config('myacademy.subscription_due_date');
        // Fallback to exactly 1 year if not set
        $dueDate = !empty($dueDateRaw)
            ? \Carbon\Carbon::parse($dueDateRaw)
            : now()->addYear();
        $now = now();

        $isPastDue = $dueDate->isPast();
        $daysPastDue = $isPastDue ? $now->diffInDays($dueDate) : 0;
        $daysUntilDue = !$isPastDue ? $now->diffInDays($dueDate) : 0;

        // Share with all views to display modals and disable UI elements
        \Illuminate\Support\Facades\View::share([
            'subscriptionIsPastDue' => $isPastDue,
            'subscriptionDaysPastDue' => $daysPastDue,
            'subscriptionDaysUntilDue' => $daysUntilDue,
            'subscriptionDueDate' => $dueDate,
        ]);

        // If >= 14 days past due, complete lock down (only billing and authentication paths allowed)
        if ($isPastDue && $daysPastDue >= 14) {
            $allowedRoutes = ['billing.index', 'logout', 'login', 'login.store'];
            if (!$request->routeIs($allowedRoutes) && !$request->is('livewire/update')) {
                return redirect()->route('billing.index')->with('error', 'Your subscription expired over 14 days ago. System access is locked until payment is made.');
            }
        }

        return $next($request);
    }
}
