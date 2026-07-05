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
        // If an authenticated staff user hits a student route, clear any stale student session
        // data (e.g. from previewing the portal) and redirect them to the staff area.
        if (auth()->check()) {
            $user = auth()->user();
            if (in_array($user->role, ['admin', 'teacher', 'bursar'], true)) {
                if (session()->has('student_id')) {
                    // Clear lingering student session so it won't cause 403 IP-lock errors or
                    // wrong redirects later when the staff member navigates to other pages.
                    $request->session()->forget([
                        'student_id', 'student_name', 'student_admission', 'student_class',
                        'login_type', 'student_must_reset_password', 'aptitude_attempt_id',
                    ]);
                }
                return redirect()
                    ->route('cbt.index')
                    ->with('warning', 'You are logged in as staff. To test or preview the exam from the student\'s perspective, please log in with a student account or use a private/incognito window.');
            }
        }

        $studentId = session('student_id');
        if (! $studentId) {
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
                    // Load settings now that tenant is bound in container
                    TenantSettings::loadToConfig();
                }
            }
        }

        if (! $tenantId || (int) session('tenant_id') !== (int) $tenantId) {
            $request->session()->forget(['tenant_id', 'student_id', 'student_name', 'student_admission', 'student_class', 'login_type']);
            $request->session()->regenerateToken();
            return redirect()->route('login');
        }

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : \App\Models\Tenant::find($tenantId);

        // For aptitude test candidates, verify the attempt exists instead of a student
        if (session('login_type') === 'aptitude') {
            $attemptId = session('aptitude_attempt_id');
            $attemptExists = $attemptId && \App\Models\CbtAttempt::query()
                ->where('id', $attemptId)
                ->exists();

            if (! $attemptExists) {
                $request->session()->forget(['student_id', 'student_name', 'student_admission', 'student_class', 'login_type', 'aptitude_attempt_id']);
                $request->session()->regenerateToken();
                return redirect()->route('login');
            }

            // Prevent aptitude candidates from accessing non-exam student routes
            if (! $request->is('cbt/portal*', 'cbt/student*', 'student/logout', 'livewire*')) {
                $attempt = \App\Models\CbtAttempt::query()->find($attemptId);
                if ($attempt) {
                    return redirect()->route('cbt.student.take', ['attempt' => $attempt]);
                }
                return redirect()->route('login');
            }

            return $next($request);
        }

        $isCbtRoute = $request->is('cbt/*', 'cbt');

        if ($isCbtRoute) {
            // Verify CBT plugin is active
            if (! $tenant || ! $tenant->activeMarketplaceComponents()->where('slug', 'cbt')->exists()) {
                $request->session()->forget(['tenant_id', 'student_id', 'student_name', 'student_admission', 'student_class', 'login_type', 'student_must_reset_password']);
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('warning', 'The CBT feature is not active for this school.');
            }
        } else {
            // Verify Student Dashboard plugin is active
            if (! $tenant || ! $tenant->activeMarketplaceComponents()->where('slug', 'student-dashboard')->exists()) {
                $request->session()->forget(['tenant_id', 'student_id', 'student_name', 'student_admission', 'student_class', 'login_type', 'student_must_reset_password']);
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('warning', 'Student Dashboard is not active for this school.');
            }
        }

        // Check if school's subscription is expired
        if ($tenant->isSubscriptionExpired()) {
            $request->session()->forget(['tenant_id', 'student_id', 'student_name', 'student_admission', 'student_class', 'login_type', 'student_must_reset_password']);
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('warning', 'Your school\'s subscription has expired. Please contact school administration.');
        }

        $student = Student::query()
            ->where('id', $studentId)
            ->where('status', 'Active')
            ->first();

        if (! $student) {
            $request->session()->forget(['tenant_id', 'student_id', 'student_name', 'student_admission', 'student_class', 'login_type']);
            $request->session()->regenerateToken();
            return redirect()->route('login');
        }

        if (!$isCbtRoute) {
            // Check class target eligibility for student-dashboard
            $pivot = $tenant->activeMarketplaceComponents()->where('slug', 'student-dashboard')->first();
            if ($pivot && $pivot->pivot) {
                $allowedClassIds = $pivot->pivot->allowed_class_ids;
                if (is_string($allowedClassIds)) {
                    $allowedClassIds = json_decode($allowedClassIds, true) ?: [];
                }
                $allowedClassIds = is_array($allowedClassIds) ? $allowedClassIds : [];
                $studentClassId = (string) $student->class_id;
                $allowedClassIds = array_map('strval', $allowedClassIds);
                if (!in_array($studentClassId, $allowedClassIds, true)) {
                    $request->session()->forget(['tenant_id', 'student_id', 'student_name', 'student_admission', 'student_class', 'login_type', 'student_must_reset_password']);
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('warning', 'Student Dashboard is not active for your class.');
                }
            }
        }

        // Check if student is using the default password - Removed restriction to force change password
        /*
        if (session('student_must_reset_password') === true && !$request->is('student/profile', 'student/logout')) {
            return redirect()->route('student.profile')->with('warning', '⚠️ Security Alert: You are using the default password. Please choose a new, secure password now.');
        }
        */

        return $next($request);
    }
}
