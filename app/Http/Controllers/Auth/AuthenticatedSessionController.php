<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
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
        } else {
            return $this->handleStaffParentLogin($request);
        }
    }
    
    private function handleStaffParentLogin(Request $request): RedirectResponse
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                Log::warning('Failed login attempt', [
                    'email' => $request->email,
                    'login_type' => $request->input('login_type', 'staff'),
                    'ip' => $request->ip()
                ]);
                
                throw ValidationException::withMessages([
                    'email' => 'Invalid email or password. Please check your credentials and try again.',
                ]);
            }

            $loginType = $request->input('login_type', 'staff');
            $userRole = Auth::user()->role;
            $tenantId = TenantSettings::tenantId();
            
            if ($loginType === 'parent') {
                if ($userRole !== 'parent') {
                    Auth::logout();
                    Log::warning('Role mismatch - parent login attempted with non-parent account', [
                        'email' => $request->email,
                        'actual_role' => $userRole
                    ]);
                    
                    throw ValidationException::withMessages([
                        'email' => 'This account is not registered as a parent. Please use the correct login portal.',
                    ]);
                }
            } elseif ($loginType === 'staff') {
                if (!in_array($userRole, ['admin', 'teacher', 'bursar'], true)) {
                    Auth::logout();
                    Log::warning('Role mismatch - staff login attempted with non-staff account', [
                        'email' => $request->email,
                        'actual_role' => $userRole
                    ]);
                    
                    throw ValidationException::withMessages([
                        'email' => 'This account is not registered as staff. Please use the correct login portal.',
                    ]);
                }
            }

            // Tenant isolation:
            // - On a tenant host, user must belong to that tenant (superadmins not allowed here).
            // - On the main host, only superadmins may login.
            $user = Auth::user();
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

            if (! $request->user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                Log::warning('Inactive account login attempt', [
                    'email' => $request->email,
                    'user_id' => $request->user()->id
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Your account has been deactivated. Please contact the administrator for assistance.',
                ]);
            }

            $request->session()->regenerate();
            
            Log::info('Successful login', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'role' => Auth::user()->role,
                'login_type' => $loginType
            ]);

            if ($user->is_super_admin) {
                return redirect()->intended(route('superadmin.dashboard'));
            }

            return redirect()->intended(route('dashboard'));
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown'
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
                throw ValidationException::withMessages([
                    'admission_number' => 'Student login must be done from your school domain/subdomain.',
                ]);
            }

            $request->validate([
                'admission_number' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);
            
            // Find student by admission number
            $student = Student::where('admission_number', $request->admission_number)
                             ->where('status', 'Active')
                             ->first();
            
            if (!$student) {
                Log::warning('Student login failed - not found or inactive', [
                    'admission_number' => $request->admission_number,
                    'ip' => $request->ip()
                ]);
                
                throw ValidationException::withMessages([
                    'admission_number' => 'Student not found or account is inactive. Please check your admission number.',
                ]);
            }
            
            // If a custom password has been set, use it; otherwise fall back to the default scheme.
            if ($student->password) {
                if (! Hash::check($request->password, $student->password)) {
                    Log::warning('Student login failed - invalid password', [
                        'admission_number' => $request->admission_number,
                        'student_id' => $student->id,
                    ]);

                    throw ValidationException::withMessages([
                        'password' => 'Invalid password. Please try again or contact your teacher.',
                    ]);
                }
            } else {
                $expectedPassword = $this->generateStudentPassword($student);
                if ($request->password !== $expectedPassword) {
                    Log::warning('Student login failed - invalid password', [
                        'admission_number' => $request->admission_number,
                        'student_id' => $student->id,
                    ]);

                    throw ValidationException::withMessages([
                        'password' => 'Invalid password. Please try again or contact your teacher.',
                    ]);
                }
            }
            
            // Create a temporary session for student
            session([
                'tenant_id' => $tenantId,
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'student_admission' => $student->admission_number,
                'student_class' => $student->schoolClass?->name,
                'login_type' => 'student'
            ]);
            
            Log::info('Successful student login', [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number
            ]);
            
            return redirect()->route('student.dashboard');
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Student login error', [
                'error' => $e->getMessage(),
                'admission_number' => $request->admission_number ?? 'unknown'
            ]);
            
            throw ValidationException::withMessages([
                'admission_number' => 'An unexpected error occurred. Please try again or contact support.',
            ]);
        }
    }
    
    private function generateStudentPassword(Student $student): string
    {
        // Simple password generation - you can customize this
        // For example: first name + last 4 digits of admission number
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
        $request->session()->forget(['student_id', 'student_name', 'student_admission', 'student_class', 'login_type']);
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
