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
        $freeTenants       = Tenant::where('plan', 'free')->count();
        $proTenants        = Tenant::where('plan', 'pro')->count();
        $enterpriseTenants = Tenant::where('plan', 'enterprise')->count();

        // Financial Overview & App Installs Metrics
        $totalInvoiced = (float) \App\Models\TenantPluginBill::where('status', '!=', 'void')->sum('total_due');
        $totalPaid = (float) \App\Models\TenantPluginBill::where('status', 'paid')->sum('total_due');
        $totalOutstanding = (float) \App\Models\TenantPluginBill::where('status', 'unpaid')->sum('total_due');
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
            'free_tenants'      => $freeTenants,
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

        return view('superadmin.dashboard', compact(
            'stats',
            'recentTenants',
            'monthlyGrowth'
        ));
    }
}
