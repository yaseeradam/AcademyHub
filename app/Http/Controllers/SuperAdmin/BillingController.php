<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BillingController extends Controller
{
    public function index()
    {
        // Load all tenants with their live stats
        $tenants = Tenant::orderBy('name')->get();

        $rows = $tenants->map(function (Tenant $tenant) {
            // Student & teacher counts
            $studentCount = DB::table('students')->where('tenant_id', $tenant->id)->count();
            $teacherCount = User::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('role', 'teacher')
                ->count();

            // Revenue collected from this tenant's transactions
            $revenueCollected = DB::table('transactions')
                ->where('tenant_id', $tenant->id)
                ->where('type', 'Income')
                ->where('is_void', false)
                ->sum('amount_paid');

            // Subscription due date from settings.json
            $settingsPath = storage_path('app/myacademy/tenants/' . $tenant->id . '/settings.json');
            $settings = File::exists($settingsPath)
                ? (json_decode(File::get($settingsPath), true) ?: [])
                : [];

            $dueDate     = null;
            $daysLeft    = null;
            $isPastDue   = false;
            $daysPastDue = 0;
            $inGrace     = false;

            if (!empty($settings['subscription_due_date'])) {
                $dueDate     = \Carbon\Carbon::parse($settings['subscription_due_date']);
                $isPastDue   = $dueDate->isPast();
                $daysPastDue = $isPastDue ? (int) now()->diffInDays($dueDate) : 0;
                $daysLeft    = $isPastDue ? null : (int) now()->diffInDays($dueDate);
                $inGrace     = $isPastDue && $daysPastDue <= ($tenant->grace_days ?? 7);
            }

            // Subscription fee — stored in settings or derived from plan
            $subscriptionFee = $settings['subscription_fee'] ?? match ($tenant->plan) {
                'pro'        => 50000,
                'enterprise' => 150000,
                default      => 0,
            };

            return [
                'tenant'           => $tenant,
                'student_count'    => $studentCount,
                'teacher_count'    => $teacherCount,
                'revenue_collected'=> (float) $revenueCollected,
                'subscription_fee' => (float) $subscriptionFee,
                'due_date'         => $dueDate,
                'days_left'        => $daysLeft,
                'is_past_due'      => $isPastDue,
                'days_past_due'    => $daysPastDue,
                'in_grace'         => $inGrace,
            ];
        });

        // Sort by revenue collected descending
        $rows = $rows->sortByDesc('revenue_collected')->values();

        // Summary totals
        $totalRevenue      = $rows->sum('revenue_collected');
        $totalStudents     = $rows->sum('student_count');
        $totalTeachers     = $rows->sum('teacher_count');
        $overdueCount      = $rows->where('is_past_due', true)->count();
        $graceCount        = $rows->where('in_grace', true)->count();

        return view('superadmin.billing', compact(
            'rows', 'totalRevenue', 'totalStudents', 'totalTeachers',
            'overdueCount', 'graceCount'
        ));
    }

    /**
     * Record a manual payment / extend subscription for a tenant.
     */
    public function recordPayment(\Illuminate\Http\Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'subscription_due_date' => ['required', 'date'],
            'subscription_fee'      => ['nullable', 'numeric', 'min:0'],
            'plan'                  => ['required', 'in:free,pro,enterprise'],
        ]);

        // Update plan on tenant
        $tenant->update(['plan' => $data['plan']]);

        // Write to settings.json
        $path     = storage_path('app/myacademy/tenants/' . $tenant->id . '/settings.json');
        $settings = File::exists($path) ? (json_decode(File::get($path), true) ?: []) : [];
        $settings['subscription_due_date'] = $data['subscription_due_date'];
        if (!empty($data['subscription_fee'])) {
            $settings['subscription_fee'] = (float) $data['subscription_fee'];
        }
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        \Illuminate\Support\Facades\Cache::forget(\App\Support\TenantSettings::settingsCacheKey($tenant));

        return back()->with('status', "Subscription updated for {$tenant->name}.");
    }
}
