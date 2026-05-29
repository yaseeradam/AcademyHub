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

    public function logout(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = preg_replace('/\D/', '', $request->phone);

        $user = \App\Models\User::where('whatsapp_phone', $phone)->first();
        if ($user) {
            $user->whatsapp_phone = null;
            $user->whatsapp_verified = false;
            $user->whatsapp_subscribed = false;
            $user->save();
            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        }

        return response()->json(['success' => false, 'message' => 'No active session found for this number'], 404);
    }

    public function getClasses()
    {
        $classes = \App\Models\SchoolClass::all(['id', 'name']);
        return response()->json(['success' => true, 'classes' => $classes]);
    }

    public function getUser($phone)
    {
        $user = \App\Models\User::where('whatsapp_phone', $phone)
            ->whereIn('role', ['parent', 'teacher', 'admin', 'superadmin', 'bursar', 'student'])
            ->with('tenant')
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
                $apiKey = config('services.whatsapp.api_key');
                $host = request()->schemeAndHttpHost();
                $reportCardUrl = "{$host}/api/whatsapp/report-card/{$student->id}?key={$apiKey}&term=1&session=2026/2027";
                
                return [
                    'name' => $student->full_name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->schoolClass?->name,
                    'attendance_today' => $attendance ? $attendance->status : null,
                    'report_card_url' => $reportCardUrl,
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

    private function buildStaffContext(\App\Models\User $staff): array
    {
        // 1. Get school details
        $school = [
            'name' => config('myacademy.school_name'),
            'phone' => config('myacademy.school_phone'),
            'email' => config('myacademy.school_email'),
        ];

        // 2. Fetch total students count
        $totalStudents = \App\Models\Student::count();
        $activeStudents = \App\Models\Student::where('status', 'Active')->count();
        $inactiveStudents = $totalStudents - $activeStudents;

        // 3. Gender breakdown
        $maleStudents = \App\Models\Student::where('status', 'Active')->where('gender', 'Male')->count();
        $femaleStudents = \App\Models\Student::where('status', 'Active')->where('gender', 'Female')->count();

        // 4. Classes with student counts
        $classesBreakdown = \App\Models\SchoolClass::withCount(['students' => function($query) {
            $query->where('status', 'Active');
        }])->get()->map(function($class) {
            return [
                'name' => $class->name,
                'students_count' => $class->students_count,
            ];
        })->values()->all();

        // 5. Total staff count
        $totalStaff = \App\Models\User::whereIn('role', ['admin', 'teacher', 'bursar'])->count();

        // 6. Today's attendance summary
        $presentToday = \App\Models\AttendanceMark::whereHas('sheet', function ($q) {
                $q->whereDate('date', today());
            })
            ->whereIn('status', ['P', 'L'])
            ->count();
            
        $absentToday = \App\Models\AttendanceMark::whereHas('sheet', function ($q) {
                $q->whereDate('date', today());
            })
            ->where('status', 'A')
            ->count();

        return [
            'user' => [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
            ],
            'school' => $school,
            'statistics' => [
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'inactive_students' => $inactiveStudents,
                'male_students' => $maleStudents,
                'female_students' => $femaleStudents,
                'total_staff' => $totalStaff,
                'today_present_students' => $presentToday,
                'today_absent_students' => $absentToday,
            ],
            'classes' => $classesBreakdown,
        ];
    }

    private function buildStudentContext(\App\Models\User $user): array
    {
        $student = \App\Models\Student::where('user_id', $user->id)->first();

        if (!$student) {
            return [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'student',
                ],
                'message' => 'Student record not linked to this user yet.'
            ];
        }

        $student->load(['schoolClass', 'section']);
        
        // Fetch homework
        $homework = $student->getHomeworkForStudent()->map(function ($hw) {
            return [
                'subject' => $hw->subject?->name,
                'title' => $hw->title,
                'description' => $hw->description,
                'due_date' => $hw->due_date,
                'teacher' => $hw->teacher?->name,
                'is_submitted' => $hw->submissions->isNotEmpty(),
            ];
        })->values()->all();

        // Fetch attendance today
        $attendance = $student->attendanceMarks()
            ->whereHas('sheet', function ($q) {
                $q->whereDate('date', today());
            })
            ->first();

        // Fetch recent scores/results
        $scores = $student->scores()
            ->latest()
            ->limit(5)
            ->with('subject')
            ->get()
            ->map(function ($score) {
                return [
                    'subject' => $score->subject?->name,
                    'total_score' => $score->total_score,
                    'term' => $score->term,
                    'session' => $score->session,
                ];
            })->values()->all();

        return [
            'student' => [
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class' => $student->schoolClass?->name,
                'section' => $student->section?->name,
                'attendance_today' => $attendance ? $attendance->status : null,
            ],
            'homework' => $homework,
            'recent_scores' => $scores,
            'school' => [
                'name' => config('myacademy.school_name'),
                'phone' => config('myacademy.school_phone'),
                'email' => config('myacademy.school_email'),
            ],
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

    public function askAi(Request $request)
    {
        $request->validate([
            'parent_id' => 'required',
            'question'  => 'required|string',
        ]);

        $user = \App\Models\User::findOrFail($request->parent_id);
        
        if ($user->role === 'parent') {
            $context = $this->buildParentContext($user);
            $roleLabel = 'parent';
        } elseif ($user->role === 'student') {
            $context = $this->buildStudentContext($user);
            $roleLabel = 'student';
        } else {
            $context = $this->buildStaffContext($user);
            $roleLabel = "staff member ({$user->role})";
        }
        
        $contextJson = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $history = $request->input('history', []);
        $historyText = "";
        if (!empty($history) && is_array($history)) {
            $historyText = "\nRECENT CONVERSATION HISTORY (Use this to remember pronouns, names, and previous context):\n";
            foreach ($history as $chat) {
                if (empty($chat['text'])) continue;
                $role = isset($chat['role']) && $chat['role'] === 'user' ? 'User' : 'HubGenie';
                $historyText .= "- {$role}: {$chat['text']}\n";
            }
            $historyText .= "\n";
        }

        $prompt = "You are HubGenie, a highly secure, ultra-concise, and intelligent virtual school assistant for the AcademyHub school management system.\n" .
                  "You are chatting with a {$roleLabel} on WhatsApp. Here is the verified, real-time context of the logged-in user, their school, and system statistics:\n" .
                  "{$contextJson}\n" .
                  $historyText . "\n" .
                  "User's Question:\n" .
                  "\"{$request->question}\"\n\n" .
                  "STRICT RULES & CONSTRAINTS:\n" .
                  "1. BREVITY: Keep your answer extremely short, concise, and to-the-point (maximum 1 to 2 sentences). Avoid all conversational fluff, long pleasantries, greetings, or repetitive sentences.\n" .
                  "2. STUDENT NAMES: Always fetch and explicitly state the student(s) full name(s) (e.g. 'Abdullahi Bala') when replying to questions about children, homework, attendance, or scores. Never refer to them generically as 'your child' or 'your student' if their name is available in the context.\n" .
                  "3. SECURITY & SENSITIVE DATA: Never reveal, discuss, or query sensitive information (e.g. passwords, password hashes, login tokens, secret keys, API credentials, or internal database schemas). Under no circumstances should you retrieve or expose password details.\n" .
                  "4. PASSWORD PROTECTION: If the user asks about passwords, credentials, resetting their password, or secure keys, you MUST immediately reject it and say exactly: '🔒 For security, passwords and login credentials cannot be accessed, modified, or discussed via WhatsApp.'\n" .
                  "5. FORMATTING: Avoid raw markdown headings or bolding that looks weird on WhatsApp (like double asterisks). Use simple spacing, clean lists, and warm school emojis (e.g., 🎒, 📚, 📊).\n" .
                  "6. Keep responses directly answering the question without extra surrounding filler.";

        // Try Gemini first, then fallback to Groq
        $answer = $this->tryGeminiAPI($prompt) ?: $this->tryGroqAPI($prompt);

        if ($answer) {
            return response()->json([
                'success' => true,
                'answer'  => $answer,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'AI service is currently busy or unavailable. Please try quick commands.',
        ], 500);
    }

    public function getReportCardPDF(Request $request, $studentId)
    {
        $student = \App\Models\Student::findOrFail($studentId);

        $term = (int) $request->query('term', 1);
        $session = (string) $request->query('session');
        
        if (empty($session)) {
            $active = \App\Models\AcademicSession::activeName();
            $session = $active ?: date('Y') . '/' . (date('Y') + 1);
        }

        $student->load(['schoolClass', 'section']);
        $data = app(\App\Support\ReportCardService::class)->build($student, $term, $session);

        $template = (string) config('myacademy.report_card_template', 'compact');
        $view = match ($template) {
            'compact' => 'pdf.report-card-compact',
            'elegant' => 'pdf.report-card-elegant',
            'modern' => 'pdf.report-card-modern',
            'classic' => 'pdf.report-card-classic',
            'aurora' => 'pdf.report-card-aurora',
            'heritage' => 'pdf.report-card-heritage',
            'nordic' => 'pdf.report-card-nordic',
            'vanguard' => 'pdf.report-card-vanguard',
            'signature' => 'pdf.report-card-signature',
            default => 'pdf.report-card-compact',
        };

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            ...$data,
        ])->setPaper('a4');

        $filename = 'report-card-' . $student->admission_number . '-' . str_replace('/', '-', $session) . '-T' . $term . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    private function tryGeminiAPI(string $prompt): ?string
    {
        try {
            $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
            if (empty($apiKey)) return null;

            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

            return $response->successful()
                ? ($response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null)
                : null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('WhatsApp AI: Gemini API error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function tryGroqAPI(string $prompt): ?string
    {
        try {
            $raw = config('services.groq.key') ?: env('GROQ_API_KEY');
            if (empty($raw)) return null;

            $keys = array_filter(array_map('trim', explode(',', $raw)));
            if (empty($keys)) return null;
            $apiKey = $keys[array_rand($keys)];

            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => 'llama-3.3-70b-versatile',
                    'messages'    => [
                        ['role' => 'system', 'content' => 'You are HubGenie, a highly concise and secure WhatsApp assistant. You MUST strictly use the verified, real-time context provided in the user prompt to answer questions. Answer directly in 1-2 brief sentences max. No fluff or greetings. For security, never discuss or reveal sensitive credentials like passwords, and if asked about passwords, say: 🔒 For security, passwords and login credentials cannot be accessed, modified, or discussed via WhatsApp.'],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 1000,
                ]);

            return $response->successful()
                ? ($response->json()['choices'][0]['message']['content'] ?? null)
                : null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp AI: Groq API error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}

