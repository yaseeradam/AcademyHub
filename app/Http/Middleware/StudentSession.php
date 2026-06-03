<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Support\TenantSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $studentId = session('student_id');
        if (! $studentId) {
            if (auth()->check()) {
                $user = auth()->user();
                if (in_array($user->role, ['admin', 'teacher', 'bursar'], true)) {
                    return redirect()
                        ->route('cbt.index')
                        ->with('warning', 'You are logged in as staff. To test or preview the exam from the student\'s perspective, please log in with a student account or use a private/incognito window.');
                }
            }
            session(['url.intended' => $request->fullUrl()]);
            return redirect()->route('login');
        }

        $tenantId = TenantSettings::tenantId();

        if (! $tenantId && (app()->environment('local') || in_array($request->getHost(), ['localhost', '127.0.0.1']))) {
            $sessionTenantId = session('tenant_id');
            if ($sessionTenantId) {
                $tenant = \App\Models\Tenant::find($sessionTenantId);
                if ($tenant) {
                    app()->instance('currentTenant', $tenant);
                    $request->attributes->add(['tenant' => $tenant]);
                    $tenantId = $tenant->id;
                }
            }
        }

        if (! $tenantId || (int) session('tenant_id') !== (int) $tenantId) {
            $request->session()->forget(['tenant_id', 'student_id', 'student_name', 'student_admission', 'student_class', 'login_type']);
            $request->session()->regenerateToken();
            return redirect()->route('login');
        }

        $studentExists = Student::query()
            ->where('id', $studentId)
            ->where('status', 'Active')
            ->exists();

        if (! $studentExists) {
            $request->session()->forget(['student_id', 'student_name', 'student_admission', 'student_class', 'login_type']);
            $request->session()->regenerateToken();
            return redirect()->route('login');
        }

        if (session('student_must_reset_password') === true && !$request->is('student/profile', 'student/logout')) {
            return redirect()->route('student.profile')->with('warning', '⚠️ Security Alert: You are using the default password. Please choose a new, secure password now.');
        }

        return $next($request);
    }
}
