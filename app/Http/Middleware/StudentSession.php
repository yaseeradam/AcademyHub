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
            return redirect()->route('login');
        }

        $tenantId = TenantSettings::tenantId();
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

        return $next($request);
    }
}
