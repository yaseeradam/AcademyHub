<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        if (! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account is inactive. Contact the administrator.',
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }
    
    private function handleStudentLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'admission_number' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        
        // Find student by admission number
        $student = Student::where('admission_number', $request->admission_number)
                         ->where('status', 'Active')
                         ->first();
        
        if (!$student) {
            throw ValidationException::withMessages([
                'admission_number' => 'Student not found or inactive.',
            ]);
        }
        
        // For now, we'll use a simple password system for students
        // You can customize this logic based on your requirements
        $expectedPassword = $this->generateStudentPassword($student);
        
        if (!Hash::check($request->password, Hash::make($expectedPassword))) {
            // Try direct comparison for simple passwords
            if ($request->password !== $expectedPassword) {
                throw ValidationException::withMessages([
                    'password' => 'Invalid password.',
                ]);
            }
        }
        
        // Create a temporary session for student
        session([
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'student_admission' => $student->admission_number,
            'student_class' => $student->schoolClass?->name,
            'login_type' => 'student'
        ]);
        
        return redirect()->route('student.dashboard');
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
}

