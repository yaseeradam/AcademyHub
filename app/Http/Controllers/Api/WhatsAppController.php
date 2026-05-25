<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    private function otpCacheKey(string $phone): string
    {
        $tenantId = TenantSettings::tenantId();

        return ($tenantId ? 'tenant_'.$tenantId.'_' : '').'whatsapp_otp_'.$phone;
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
            'phone'      => 'required|string',
        ]);

        $identifier = trim($request->identifier);
        $password   = $request->password;
        $phone      = preg_replace('/\D/', '', $request->phone);

        // --- Try student login (admission number) ---
        $student = \App\Models\Student::where('admission_number', strtoupper($identifier))
            ->where('status', 'Active')
            ->first();

        if ($student) {
            $valid = false;
            if ($student->password) {
                $valid = \Illuminate\Support\Facades\Hash::check($password, $student->password);
            } else {
                $suffix   = substr($student->admission_number, -4);
                $expected = strtolower($student->first_name) . $suffix;
                $valid    = $password === $expected;
            }

            if (! $valid) {
                return response()->json(['success' => false, 'message' => 'Invalid password. Use the same password as the school website.'], 401);
            }

            // If already linked to another WhatsApp phone number, reject
            if ($student->user_id) {
                $user = \App\Models\User::find($student->user_id);
                if ($user && $user->whatsapp_phone && $user->whatsapp_phone !== $phone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This student account is already linked to another WhatsApp number. Please contact your school administrator to unlink it.'
                    ], 400);
                }

                if ($user) {
                    $user->whatsapp_phone      = $phone;
                    $user->whatsapp_verified   = true;
                    $user->whatsapp_subscribed = true;
                    $user->save();
                }
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id'         => $student->id,
                    'name'       => $student->full_name,
                    'first_name' => $student->first_name,
                    'role'       => 'student',
                    'admission'  => $student->admission_number,
                    'class'      => $student->schoolClass?->name,
                ],
            ]);
        }

        // --- Try staff / parent / admin login (email) ---
        $user = \App\Models\User::where('email', $identifier)
            ->whereIn('role', ['admin', 'teacher', 'bursar', 'parent'])
            ->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Account not found. Use your admission number (students) or email address (staff/parents).'], 404);
        }

        if (! \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid password. Use the same password as the school website.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['success' => false, 'message' => 'Your account is inactive. Contact the administrator.'], 403);
        }

        // If already linked to another WhatsApp phone number, reject
        if ($user->whatsapp_phone && $user->whatsapp_phone !== $phone) {
            return response()->json([
                'success' => false,
                'message' => 'This account is already linked to another WhatsApp number. Please contact your school administrator to unlink it.'
            ], 400);
        }

        // Link WhatsApp phone
        $user->whatsapp_phone      = $phone;
        $user->whatsapp_verified   = true;
        $user->whatsapp_subscribed = true;
        $user->save();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'   => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }

    public function getUser($phone)
    {
        $user = \App\Models\User::where('whatsapp_phone', $phone)
            ->whereIn('role', ['parent', 'teacher', 'admin', 'superadmin', 'bursar', 'student'])
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        
        if ($user->role === 'parent') {
            $user->load('students.schoolClass');
        }

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required'
        ]);

        $user = \App\Models\User::where('email', $request->email)
            ->whereIn('role', ['parent', 'teacher', 'admin', 'superadmin', 'bursar'])
            ->first();
            
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authorized user email not found'], 404);
        }

        if ($user->role === 'parent') {
            if (!$request->admission_number) {
                return response()->json(['success' => false, 'message' => 'Parents must provide admission_number'], 400);
            }
            $student = \App\Models\Student::where('admission_number', $request->admission_number)->first();
            if (!$student || !$user->students()->where('student_id', $student->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Student not linked to this parent email'], 403);
            }
        }

        $otp = rand(100000, 999999);
        \Illuminate\Support\Facades\Cache::put($this->otpCacheKey($request->phone), [
            'user_id' => $user->id,
            'otp' => $otp
        ], now()->addMinutes(10));

        // OTP must be sent via WhatsApp/SMS by the bot, NOT returned in the response.
        return response()->json(['success' => true, 'message' => 'OTP generated. Send it to the user via WhatsApp.']);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required'
        ]);

        $cached = \Illuminate\Support\Facades\Cache::get($this->otpCacheKey($request->phone));
        if (!$cached || (string) $cached['otp'] !== (string) $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 400);
        }

        $user = \App\Models\User::find($cached['user_id']);
        $user->whatsapp_phone = $request->phone;
        $user->whatsapp_verified = true;
        $user->whatsapp_subscribed = true;
        $user->save();

        \Illuminate\Support\Facades\Cache::forget($this->otpCacheKey($request->phone));

        if ($user->role === 'parent') {
            $user->load('students.schoolClass');
        }

        return response()->json(['success' => true, 'message' => 'Registration successful', 'user' => $user]);
    }

    public function getAttendance($parentId)
    {
        $parent = \App\Models\User::findOrFail($parentId);
        $students = $parent->students()->with(['attendanceMarks' => function ($q) {
            $q->whereHas('sheet', function ($sq) {
                $sq->whereDate('date', today());
            });
        }])->get();

        return response()->json(['success' => true, 'students' => $students]);
    }

    public function getResults($parentId)
    {
        $parent = \App\Models\User::findOrFail($parentId);
        $students = $parent->students()->with(['scores' => function($q) {
            $q->latest()->limit(5)->with('subject');
        }])->get();

        return response()->json(['success' => true, 'students' => $students]);
    }

    public function getFees($parentId)
    {
        $parent = \App\Models\User::findOrFail($parentId);
        // Getting due transactions or fee structures
        // We'll return empty data for now or a simple summary
        return response()->json(['success' => true, 'data' => []]);
    }


    private function buildParentContext(\App\Models\User $parent): array
    {
        $parent->load([
            'students.schoolClass',
            'students.attendanceMarks' => function ($q) {
                $q->whereHas('sheet', function ($sq) {
                    $sq->whereDate('date', today());
                });
            },
            'students.scores' => function ($q) {
                $q->latest()->limit(5)->with('subject');
            },
        ]);

        return [
            'parent' => [
                'name' => $parent->name,
                'email' => $parent->email,
                'whatsapp_phone' => $parent->whatsapp_phone,
                'whatsapp_subscribed' => (bool) $parent->whatsapp_subscribed,
            ],
            'school' => [
                'name' => config('myacademy.school_name'),
                'phone' => config('myacademy.school_phone'),
                'email' => config('myacademy.school_email'),
            ],
            'students' => $parent->students->map(function ($student) {
                $attendance = $student->attendanceMarks->first();
                return [
                    'name' => $student->full_name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->schoolClass?->name,
                    'attendance_today' => $attendance ? $attendance->status : null,
                    'recent_scores' => $student->scores->map(function ($score) {
                        return [
                            'subject' => $score->subject?->name,
                            'total_score' => $score->total_score,
                            'term' => $score->term ?? null,
                            'session' => $score->session ?? null,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    public function subscribe($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        $user->whatsapp_subscribed = true;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Subscribed']);
    }

    public function unsubscribe($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        $user->whatsapp_subscribed = false;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Unsubscribed']);
    }

    public function getContact()
    {
        return response()->json([
            'success' => true,
            'phone' => config('myacademy.school_phone'),
            'email' => config('myacademy.school_email'),
        ]);
    }

    public function staffHomework(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'class' => 'required',
            'description' => 'required'
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        if (!in_array($user->role, ['teacher', 'admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized role'], 403);
        }

        $schoolClass = \App\Models\SchoolClass::where('name', 'like', '%' . $request->class . '%')->first();
        if (!$schoolClass) {
            return response()->json(['success' => false, 'message' => 'Class not found'], 404);
        }

        $academicSession = \App\Models\AcademicSession::active()->first();
        $academicTerm = \App\Models\AcademicTerm::active()->first();

        if (!$academicSession || !$academicTerm) {
            return response()->json(['success' => false, 'message' => 'Session data unavailable'], 400);
        }

        $homework = new \App\Models\Homework();
        $homework->title = 'WhatsApp Assignment: ' . $schoolClass->name;
        $homework->description = $request->description;
        $homework->school_class_id = $schoolClass->id;
        $homework->teacher_id = $user->id;
        $homework->academic_session_id = $academicSession->id;
        $homework->academic_term_id = $academicTerm->id;
        $homework->due_date = now()->addDays(2)->toDateString();
        $homework->save();

        return response()->json(['success' => true, 'message' => 'Homework assigned']);
    }

    public function adminBroadcast(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'target' => 'required',
            'message' => 'required'
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized broadcast attempt.'], 403);
        }

        $target = strtolower($request->target);
        $query = \App\Models\User::where('whatsapp_subscribed', true)
            ->whereNotNull('whatsapp_phone')
            ->where('id', '!=', $user->id);

        if ($target === 'parents' || $target === 'parent') {
            $query->where('role', 'parent');
        } elseif ($target === 'staff') {
            $query->whereIn('role', ['teacher', 'bursar', 'admin']);
        } elseif ($target === 'all') {
            // Keep all
        } else {
             return response()->json(['success' => false, 'message' => 'Invalid target. Use Parents, Staff, or All.'], 400);
        }

        $phones = $query->pluck('whatsapp_phone')->toArray();
        return response()->json(['success' => true, 'phones' => $phones]);
    }
}
