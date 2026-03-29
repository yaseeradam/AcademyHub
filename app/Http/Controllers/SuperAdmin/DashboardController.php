<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::active()->count(),
            'total_users' => User::count(),
            'total_superadmins' => User::where('is_super_admin', true)->count(),
        ];

        $recentTenants = Tenant::latest()->take(5)->get();

        return view('superadmin.dashboard', compact('stats', 'recentTenants'));
    }
}
