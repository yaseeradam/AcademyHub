<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $totalTenants    = Tenant::count();
        $activeTenants   = Tenant::where('status', 'active')->count();
        $pendingTenants  = Tenant::where('status', 'pending')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();
        $totalUsers      = User::whereNull('is_super_admin')->orWhere('is_super_admin', false)->count();
        $totalSuperAdmins = User::where('is_super_admin', true)->count();

        // Plan breakdown
        $basicTenants      = Tenant::where('plan', 'basic')->count();
        $proTenants        = Tenant::where('plan', 'pro')->count();
        $enterpriseTenants = Tenant::where('plan', 'enterprise')->count();

        // Financial Overview & App Installs Metrics
        $coreInvoiced = 0.00;
        $corePaid = 0.00;
        $coreOutstanding = 0.00;

        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            // Student count for this school
            $studentCount = \App\Models\Student::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
            $coreCost = $studentCount * \App\Support\PlatformSettings::getStudentTermlyFee();

            if ($coreCost > 0) {
                $coreInvoiced += $coreCost;
                // A subscription is considered paid if the tenant is active and has not expired
                $isSubscriptionActive = $tenant->status === 'active' && $tenant->expires_at && $tenant->expires_at->isFuture();
                
                if ($isSubscriptionActive) {
                    $corePaid += $coreCost;
                } else {
                    $coreOutstanding += $coreCost;
                }
            }
        }

        // Include all bills in metrics
        $totalInvoiced = (float) \App\Models\TenantPluginBill::where('status', '!=', 'void')->sum('total_due') + $coreInvoiced;

        $totalPaid = (float) \App\Models\TenantPluginBill::where('status', 'paid')->sum('total_due') + $corePaid;

        $totalOutstanding = (float) \App\Models\TenantPluginBill::where('status', 'unpaid')->sum('total_due') + $coreOutstanding;
        $totalInstalls = (int) DB::table('tenant_marketplace_components')
            ->whereNotNull('installed_at')
            ->whereNull('uninstalled_at')
            ->count();

        $stats = [
            'total_tenants'     => $totalTenants,
            'active_tenants'    => $activeTenants,
            'pending_tenants'   => $pendingTenants,
            'suspended_tenants' => $suspendedTenants,
            'total_users'       => $totalUsers,
            'total_superadmins' => $totalSuperAdmins,
            'free_tenants'      => $basicTenants,
            'pro_tenants'       => $proTenants,
            'enterprise_tenants'=> $enterpriseTenants,
            'total_invoiced'    => $totalInvoiced,
            'total_paid'        => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'total_installs'    => $totalInstalls,
        ];

        // Monthly tenant creation for last 6 months
        $monthlyGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $count = Tenant::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyGrowth[] = [
                'month' => $date->format('M'),
                'count' => $count,
            ];
        }

        $recentTenants = Tenant::latest()->take(8)->get();
        $pendingPayoutTenants = Tenant::where('settings->payment_gateway->subaccount_status', 'pending')->get();

        return view('superadmin.dashboard', compact(
            'stats',
            'recentTenants',
            'monthlyGrowth',
            'pendingPayoutTenants'
        ));
    }
}
