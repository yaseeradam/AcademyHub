<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $totalTenants     = Tenant::count();
        $activeTenants    = Tenant::where('status', 'active')->count();
        $pendingTenants   = Tenant::where('status', 'pending')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();
        $totalUsers       = User::where('is_super_admin', false)->orWhereNull('is_super_admin')->count();
        $freeTenants      = Tenant::where('plan', 'free')->count();
        $proTenants       = Tenant::where('plan', 'pro')->count();
        $enterpriseTenants= Tenant::where('plan', 'enterprise')->count();

        // Cross-tenant stats
        $totalStudents = DB::table('students')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalExams    = DB::table('cbt_exams')->count();
        $totalHomework = DB::table('homework')->count();

        // Revenue (sum of all transactions across all tenants)
        $totalRevenue = DB::table('transactions')->where('type', 'Income')->sum('amount_paid');

        // Storage usage per tenant
        $storageStats = [];
        $uploadsBase  = public_path('uploads');
        foreach (Tenant::select('id', 'name', 'slug')->get() as $t) {
            $tenantDir = $uploadsBase . '/tenant_' . $t->id;
            $size = 0;
            if (File::isDirectory($tenantDir)) {
                foreach (File::allFiles($tenantDir) as $file) {
                    $size += $file->getSize();
                }
            }
            $storageStats[] = ['name' => $t->name, 'slug' => $t->slug, 'bytes' => $size];
        }
        usort($storageStats, fn($a, $b) => $b['bytes'] - $a['bytes']);

        // Dormant schools — no user login in 30+ days
        $dormantTenants = Tenant::where('status', 'active')
            ->whereDoesntHave('users', fn($q) => $q->where('last_login_at', '>=', now()->subDays(30)))
            ->count();

        // Upcoming renewals (expiring in next 30 days)
        $upcomingRenewals = [];
        foreach (Tenant::where('status', 'active')->get() as $t) {
            $path = storage_path('app/myacademy/tenants/' . $t->id . '/settings.json');
            if (! File::exists($path)) continue;
            $s = json_decode(File::get($path), true) ?: [];
            if (empty($s['subscription_due_date'])) continue;
            $due = \Carbon\Carbon::parse($s['subscription_due_date']);
            if ($due->isFuture() && $due->diffInDays(now()) <= 30) {
                $upcomingRenewals[] = ['name' => $t->name, 'due' => $due->format('M j, Y'), 'days' => (int) now()->diffInDays($due)];
            }
        }

        // Monthly growth
        $monthlyGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyGrowth[] = [
                'month' => $date->format('M'),
                'count' => Tenant::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        }

        $recentTenants = Tenant::latest()->take(8)->get();

        $stats = compact(
            'totalTenants', 'activeTenants', 'pendingTenants', 'suspendedTenants',
            'totalUsers', 'freeTenants', 'proTenants', 'enterpriseTenants',
            'totalStudents', 'totalTeachers', 'totalExams', 'totalHomework',
            'totalRevenue', 'dormantTenants'
        );

        // Rename keys to match dashboard view
        $stats = [
            'total_tenants'      => $totalTenants,
            'active_tenants'     => $activeTenants,
            'pending_tenants'    => $pendingTenants,
            'suspended_tenants'  => $suspendedTenants,
            'total_users'        => $totalUsers,
            'free_tenants'       => $freeTenants,
            'pro_tenants'        => $proTenants,
            'enterprise_tenants' => $enterpriseTenants,
            'total_students'     => $totalStudents,
            'total_teachers'     => $totalTeachers,
            'total_exams'        => $totalExams,
            'total_homework'     => $totalHomework,
            'total_revenue'      => $totalRevenue,
            'dormant_tenants'    => $dormantTenants,
        ];

        // WhatsApp bot activity per tenant
        $whatsappStats = [];
        foreach (Tenant::select('id', 'name')->get() as $t) {
            $linked = User::where('tenant_id', $t->id)
                ->whereNotNull('whatsapp_phone')
                ->where('whatsapp_verified', true)
                ->count();
            if ($linked > 0) {
                $whatsappStats[] = ['name' => $t->name, 'linked' => $linked];
            }
        }

        // Recent errors from Laravel log
        $errorLog   = storage_path('logs/laravel.log');
        $recentErrors = [];
        if (File::exists($errorLog)) {
            $lines = array_reverse(explode("\n", File::get($errorLog)));
            $count = 0;
            foreach ($lines as $line) {
                if (str_contains($line, '.ERROR') && $count < 10) {
                    preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $dateMatch);
                    preg_match('/\.ERROR: (.+?)(?:\s\{|$)/', $line, $msgMatch);
                    if ($msgMatch) {
                        $recentErrors[] = [
                            'time'    => $dateMatch[1] ?? '',
                            'message' => \Illuminate\Support\Str::limit($msgMatch[1], 120),
                        ];
                        $count++;
                    }
                }
            }
        }

        return view('superadmin.dashboard', compact(
            'stats', 'recentTenants', 'monthlyGrowth', 'storageStats',
            'upcomingRenewals', 'whatsappStats', 'recentErrors'
        ));
    }
}
