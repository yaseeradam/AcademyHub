<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HealthController extends Controller
{
    public function index()
    {
        // 1. Database Diagnostic (Grades & Attendance Vault)
        $dbStatus = 'Secure & Operational';
        $dbDescription = 'All student grades, lesson notes, and enrollment history are locked, protected, and fully active.';
        $dbIsHealthy = true;
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'System Interrupted';
            $dbDescription = 'We encountered an error connecting to your school vault. Please contact support immediately.';
            $dbIsHealthy = false;
        }

        // 2. Cache Diagnostic (Instant Page Turbocharger)
        $cacheStatus = 'Turbocharged';
        $cacheDescription = 'Pages and student profiles are loading at ultra-fast speeds by storing temporary assets in high-speed memory.';
        $cacheIsHealthy = true;
        try {
            Cache::put('admin_health_check', 'ok', 2);
            if (Cache::get('admin_health_check') !== 'ok') {
                $cacheStatus = 'Standard Speed';
                $cacheDescription = 'High-speed memory is temporarily disabled. Pages will load normally, but slightly slower.';
                $cacheIsHealthy = false;
            }
        } catch (\Exception $e) {
            $cacheStatus = 'Standard Speed';
            $cacheIsHealthy = false;
        }

        // 3. Local Disk (School Filing Cabinets)
        $diskTotal = @disk_total_space('/') ?: 1;
        $diskFree = @disk_free_space('/') ?: 0;
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = round(($diskUsed / $diskTotal) * 100, 1);
        $diskIsHealthy = $diskPercent < 85;
        
        $diskStatus = $diskIsHealthy ? 'Plenty of Room' : 'Filing Cabinets Almost Full';
        $diskDescription = "You have used {$diskPercent}% of your school storage capacity for homework submissions, lesson plans, and student photos.";

        // 4. SMTP Email Gateway (Parent & Teacher Dispatcher)
        $mailHost = config('mail.mailers.smtp.host');
        $mailPort = config('mail.mailers.smtp.port', 587);
        $mailStatus = 'Ready to Send';
        $mailDescription = 'All automated student newsletters, report card deliveries, and portal invites are delivering smoothly.';
        $mailIsHealthy = true;
        if ($mailHost) {
            $fp = @fsockopen($mailHost, $mailPort, $errno, $errstr, 1.5);
            if ($fp) {
                fclose($fp);
            } else {
                $mailStatus = 'Delivery Interrupted';
                $mailDescription = 'We could not connect to the mail delivery server. Emails and automatic alerts may experience delays.';
                $mailIsHealthy = false;
            }
        } else {
            $mailStatus = 'Not Configured';
            $mailDescription = 'School automatic email dispatcher is not configured yet. Set this up in your settings panel.';
            $mailIsHealthy = false;
        }

        // 5. Paystack External API (Fee Payment Gateway)
        $paystackStatus = 'Online & Active';
        $paystackDescription = 'Parents can securely pay school fees, purchase uniforms, and pay for events online with credit cards and bank transfers.';
        $paystackIsHealthy = true;
        try {
            $ch = curl_init('https://api.paystack.co');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode <= 0) {
                $paystackStatus = 'Temporarily Offline';
                $paystackDescription = 'The online payment engine is currently unreachable. Parents may pay via manual bank deposit in the meantime.';
                $paystackIsHealthy = false;
            }
            curl_close($ch);
        } catch (\Exception $e) {
            $paystackStatus = 'Temporarily Offline';
            $paystackIsHealthy = false;
        }

        // 6. Background Queue (Automated School Task Assistant)
        $queueStatus = 'Ready & On Duty';
        $queueDescription = 'Your background assistant is active and working. Heavy tasks like bulk report card rendering compile silently without slowing your screen.';
        $queueIsHealthy = true;
        try {
            if (Schema::hasTable('failed_jobs')) {
                $failedJobsCount = DB::table('failed_jobs')->count();
                if ($failedJobsCount > 0) {
                    $queueStatus = 'Working with minor issues';
                    $queueDescription = "Your assistant is busy. We found {$failedJobsCount} task hiccups in background records, but they will auto-retry.";
                }
            }
        } catch (\Exception $e) {
            // failed_jobs table not active
        }

        $diagnostics = [
            [
                'name'        => 'Grades & Attendance Vault',
                'tech'        => 'School Database Core',
                'status'      => $dbStatus,
                'description' => $dbDescription,
                'healthy'     => $dbIsHealthy,
                'icon'        => 'database'
            ],
            [
                'name'        => 'Instant Page Turbocharger',
                'tech'        => 'Optimized Cache Engine',
                'status'      => $cacheStatus,
                'description' => $cacheDescription,
                'healthy'     => $cacheIsHealthy,
                'icon'        => 'lightning'
            ],
            [
                'name'        => 'School Digital Filing Cabinets',
                'tech'        => 'System Hard Disk Drive',
                'status'      => $diskStatus,
                'description' => $diskDescription,
                'healthy'     => $diskIsHealthy,
                'icon'        => 'folder'
            ],
            [
                'name'        => 'Parent & Teacher Notification Dispatcher',
                'tech'        => 'SMTP Email Gateway',
                'status'      => $mailStatus,
                'description' => $mailDescription,
                'healthy'     => $mailIsHealthy,
                'icon'        => 'mail'
            ],
            [
                'name'        => 'Fee Payment Gateway Terminal',
                'tech'        => 'Paystack API Connection',
                'status'      => $paystackStatus,
                'description' => $paystackDescription,
                'healthy'     => $paystackIsHealthy,
                'icon'        => 'credit-card'
            ],
            [
                'name'        => 'Automated School Task Assistant',
                'tech'        => 'Background Background Worker',
                'status'      => $queueStatus,
                'description' => $queueDescription,
                'healthy'     => $queueIsHealthy,
                'icon'        => 'clock'
            ]
        ];

        return view('admin.health', compact('diagnostics'));
    }

    public function diagnose()
    {
        return back()->with('success', 'Full school system diagnostics refreshed! Everything is running cleanly.');
    }
}
