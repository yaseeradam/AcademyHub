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
        $dueDateRaw = config('academyhub.subscription_due_date');
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

        if ($isPastDue) {
            // Block all standard mutating requests (CRUD stop)
            if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
                if ($request->is('livewire/update')) {
                    $payload = $request->json()->all();
                    $hasModifyingCalls = false;
                    
                    if (isset($payload['components']) && is_array($payload['components'])) {
                        foreach ($payload['components'] as $comp) {
                            $snapshot = isset($comp['snapshot']) ? json_decode($comp['snapshot'], true) : [];
                            $memo = $snapshot['memo'] ?? [];
                            $compName = $memo['name'] ?? '';
                            
                            // Allow the subscription billing page to execute operations (payment verification, etc.)
                            if ($compName !== 'admin.subscription-billing' && !empty($comp['calls'])) {
                                $hasModifyingCalls = true;
                                break;
                            }
                        }
                    }
                    
                    if ($hasModifyingCalls) {
                        return response()->json([
                            'message' => 'Your subscription has expired. Creating, updating, or deleting data is disabled in read-only mode.',
                        ], 403);
                    }
                } else {
                    $allowedRoutes = ['settings.subscription', 'logout', 'login', 'login.store'];
                    if (!$request->routeIs($allowedRoutes)) {
                        return back()->with('error', 'Your subscription has expired. Creating, updating, or deleting data is disabled in read-only mode.');
                    }
                }
            }

            // If >= 14 days past due, complete lock down (only billing and authentication paths allowed)
            if ($daysPastDue >= 14) {
                $allowedRoutes = ['settings.subscription', 'logout', 'login', 'login.store'];
                if (!$request->routeIs($allowedRoutes) && !$request->is('livewire/update')) {
                    return redirect()->route('settings.subscription')->with('error', 'Your subscription expired over 14 days ago. System access is locked until payment is made.');
                }
            }
        }

        return $next($request);
    }
}
