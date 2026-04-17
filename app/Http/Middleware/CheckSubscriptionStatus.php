<?php

namespace App\Http\Middleware;

use Closure;
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
        $settingsPath = storage_path('app/myacademy/settings.json');
        $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        // Fallback to exactly 1 year if not set
        $dueDate = !empty($settings['subscription_due_date'])
            ? \Carbon\Carbon::parse($settings['subscription_due_date'])
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
