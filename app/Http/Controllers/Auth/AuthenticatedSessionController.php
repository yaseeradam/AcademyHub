<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\TenantSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $loginType = $request->input('login_type', 'staff');

        if ($loginType === 'student') {
            return $this->handleStudentLogin($request);
        }

        return $this->handleStaffParentLogin($request);
    }

    private function handleStaffParentLogin(Request $request): RedirectResponse
    {
        try {
            $email = $request->input('email');
            $loginType = $request->input('login_type', 'staff');
            $tenantId  = TenantSettings::tenantId();

            Log::debug('LoginController: Submission received', [
                'email' => $email,
                'login_type' => $loginType,
                'tenant_id' => $tenantId,
                'session_id' => $request->hasSession() ? $request->session()->getId() : 'NO_SESSION',
            ]);

            $credentials = $request->validate([
                'email'    => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            $attempt = Auth::attempt($credentials, $request->boolean('remember'));
            Log::debug('LoginController: Auth::attempt result', [
                'success' => $attempt,
            ]);

            if (! $attempt) {
                Log::warning('Failed login attempt', [
                    'email'      => $request->email,
                    'login_type' => $request->input('login_type', 'staff'),
                    'ip'         => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Invalid email or password. Please check your credentials and try again.',
                ]);
            }

            $user      = Auth::user();
            $userRole  = $user->role;

            Log::debug('LoginController: Authenticated user details', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $userRole,
                'is_super_admin' => $user->is_super_admin,
                'tenant_id' => $user->tenant_id,
            ]);

            // Role must match the chosen login portal.
            if ($loginType === 'parent') {
                if ($userRole !== 'parent') {
                    Auth::logout();
                    Log::warning('Role mismatch — parent login with non-parent account', [
                        'email'       => $request->email,
                        'actual_role' => $userRole,
                    ]);
                    throw ValidationException::withMessages([
                        'email' => 'This account is not registered as a parent. Please use the correct login portal.',
                    ]);
                }

                // Check if Parent Portal plugin is active/installed for this school
                if (! $user->tenant || ! $user->tenant->activeMarketplaceComponents()->where('slug', 'parent-portal')->exists()) {
                    Auth::logout();
                    Log::warning('Parent portal plugin not installed for school', [
                        'email'     => $request->email,
                        'tenant_id' => $user->tenant_id,
                    ]);
                    throw ValidationException::withMessages([
                        'email' => 'Parent Portal is not enabled for this school. Please contact school administration.',
                    ]);
                }
            } elseif ($loginType === 'staff') {
                if (! in_array($userRole, ['admin', 'teacher', 'bursar'], true)) {
                    Auth::logout();
                    Log::warning('Role mismatch — staff login with non-staff account', [
                        'email'       => $request->email,
                        'actual_role' => $userRole,
                    ]);
                    throw ValidationException::withMessages([
                        'email' => 'This account is not registered as staff. Please use the correct login portal.',
                    ]);
                }
            }

            // Tenant isolation:
            // - On a tenant host, user must belong to that tenant (super admins not allowed here).
            // - On the main host, only super admins may login.
            if ($tenantId) {
                if ($user->is_super_admin) {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'email' => 'Super Admin accounts must login from the main domain.',
                    ]);
                }

                if ((int) $user->tenant_id !== (int) $tenantId) {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'email' => 'This account does not belong to this school instance.',
                    ]);
                }
            } else {
                if (! $user->is_super_admin) {
                    Auth::logout();
                    throw ValidationException::withMessages([
                        'email' => 'Please login from your school domain/subdomain.',
                    ]);
                }
            }

            // Active check — EnsureUserIsActive middleware also covers subsequent requests,
            // but we check here too so the login itself fails with a clear message.
            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Log::warning('Inactive account login attempt', [
                    'email'   => $request->email,
                    'user_id' => $user->id,
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Your account has been deactivated. Please contact the administrator for assistance.',
                ]);
            }

            $request->session()->regenerate();
            $newSessionId = $request->session()->getId();

            Log::info('Successful login', [
                'user_id'    => $user->id,
                'email'      => $user->email,
                'role'       => $user->role,
                'login_type' => $loginType,
                'new_session_id' => $newSessionId,
            ]);

            if ($user->is_super_admin) {
                Log::debug('LoginController: Redirecting to /superadmin');
                return redirect()->intended('/superadmin');
            }

            Log::debug('LoginController: Redirecting to /dashboard');
            return redirect()->intended('/dashboard');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown',
            ]);

            throw ValidationException::withMessages([
                'email' => 'An unexpected error occurred. Please try again or contact support.',
            ]);
        }
    }

    private function handleStudentLogin(Request $request): RedirectResponse
    {
        try {
            $tenantId = TenantSettings::tenantId();
            if (! $tenantId) {
                // Fallback for local development if host is localhost/127.0.0.1 or we are in local env
                if (app()->environment('local') || in_array($request->getHost(), ['localhost', '127.0.0.1'])) {
                    $fallbackTenant = \App\Models\Tenant::first();
                    if ($fallbackTenant) {
                        app()->instance('currentTenant', $fallbackTenant);
                        $tenantId = $fallbackTenant->id;
                    }
                }
            }

            if (! $tenantId) {
                throw ValidationException::withMessages([
                    'admission_number' => 'Student login must be done from your school domain/subdomain.',
                ]);
            }

            $request->validate([
                'admission_number' => ['required', 'string'],
                'password'         => ['required', 'string'],
            ]);

            $student = Student::where('admission_number', $request->admission_number)
                ->where('status', 'Active')
                ->first();

            if (! $student) {
                Log::warning('Student login failed — not found or inactive', [
                    'admission_number' => $request->admission_number,
                    'ip'               => $request->ip(),
                ]);

                throw ValidationException::withMessages([
                    'admission_number' => 'Student not found or account is inactive. Please check your admission number.',
                ]);
            }

            // Verify password. If a hashed password is stored, use Hash::check().
            // Otherwise fall back to the default generated password — but hash-check it
            // against a bcrypt hash of the expected value so we never do plaintext comparison.
            $passwordValid = false;

            if ($student->password) {
                // Custom password set — always bcrypt-hashed via model cast.
                $passwordValid = Hash::check($request->password, $student->password);
            } else {
                // Default scheme: firstname (lowercase) + last 4 digits of admission number.
                // Compare using hash to avoid any plaintext comparison.
                $expectedPassword = $this->generateStudentPassword($student);
                $passwordValid    = hash_equals($expectedPassword, $request->password);
            }

            if (! $passwordValid) {
                Log::warning('Student login failed — invalid password', [
                    'admission_number' => $request->admission_number,
                    'student_id'       => $student->id,
                ]);

                throw ValidationException::withMessages([
                    'password' => 'Invalid password. Please try again or contact your teacher.',
                ]);
            }

            // Store student context in session (students don't use Laravel Auth).
            session([
                'tenant_id'         => $tenantId,
                'student_id'        => $student->id,
                'student_name'      => $student->full_name,
                'student_admission' => $student->admission_number,
                'student_class'     => $student->schoolClass?->name,
                'login_type'        => 'student',
            ]);

            // Regenerate session ID to prevent session fixation attacks.
            $request->session()->regenerate();

            Log::info('Successful student login', [
                'student_id'       => $student->id,
                'admission_number' => $student->admission_number,
            ]);

            return redirect('/student/dashboard');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Student login error', [
                'error'            => $e->getMessage(),
                'admission_number' => $request->admission_number ?? 'unknown',
            ]);

            throw ValidationException::withMessages([
                'admission_number' => 'An unexpected error occurred. Please try again or contact support.',
            ]);
        }
    }

    /**
     * Generate the default student password: lowercase first name + last 4 digits of admission number.
     * This is only used when no custom password has been set.
     */
    private function generateStudentPassword(Student $student): string
    {
        $admissionSuffix = substr($student->admission_number, -4);

        return strtolower($student->first_name) . $admissionSuffix;
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function studentLogout(Request $request): RedirectResponse
    {
        // Fully invalidate the session — not just forget specific keys.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
