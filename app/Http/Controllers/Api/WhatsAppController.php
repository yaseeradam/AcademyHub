<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function getUser($phone)
    {
        $user = \App\Models\User::where('whatsapp_phone', $phone)
            ->whereIn('role', ['parent', 'teacher', 'admin', 'superadmin', 'bursar'])
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
        \Illuminate\Support\Facades\Cache::put('whatsapp_otp_' . $request->phone, [
            'user_id' => $user->id,
            'otp' => $otp
        ], now()->addMinutes(10));

        return response()->json(['success' => true, 'otp' => $otp]);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required'
        ]);

        $cached = \Illuminate\Support\Facades\Cache::get('whatsapp_otp_' . $request->phone);
        if (!$cached || $cached['otp'] != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 400);
        }

        $user = \App\Models\User::find($cached['user_id']);
        $user->whatsapp_phone = $request->phone;
        $user->whatsapp_verified = true;
        $user->whatsapp_subscribed = true;
        $user->save();

        \Illuminate\Support\Facades\Cache::forget('whatsapp_otp_' . $request->phone);

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

    public function askAi(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer',
            'key' => 'nullable|string',
            'question' => 'required|string|max:1000',
        ]);

        $user = \App\Models\User::whereKey($request->parent_id)
            ->firstOrFail();

        if (!empty($request->key) && !empty($user->whatsapp_ai_key_hash)) {
            if (hash('sha256', $request->key) !== $user->whatsapp_ai_key_hash) {
                return response()->json([
                    'success' => false,
                    'code' => 'key_invalid',
                    'message' => 'Invalid AI key',
                ], 403);
            }
        }

        $keys = array_filter(array_map('trim', explode(',', env('GROQ_API_KEY', ''))));
        if (empty($keys)) {
            return response()->json([
                'success' => false,
                'message' => 'Groq API key not configured',
            ], 500);
        }

        if ($user->role === 'parent') {
            $context = $this->buildParentContext($user);
            $systemInstruction = 'You are MyAcademy Parent Assistant. Answer only using the provided parent and student data. If the answer is not in the data, say you do not have access to that information. Keep responses concise and clear.';
            $contextString = "Parent Data:\n" . json_encode($context, JSON_UNESCAPED_SLASHES) . "\n\nQuestion: " . $request->question;
        } else {
            $context = [
               'staff' => [
                   'name' => $user->name,
                   'email' => $user->email,
                   'role' => $user->role
               ],
               'school' => [
                 'name' => config('myacademy.school_name')
               ]
            ];
            $systemInstruction = 'You are MyAcademy Staff Assistant. Answer the staff member\'s question concisely. Be helpful and professional.';
            $contextString = "Staff Data:\n" . json_encode($context, JSON_UNESCAPED_SLASHES) . "\n\nQuestion: " . $request->question;
        }

        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemInstruction
                ],
                [
                    'role' => 'user',
                    'content' => $contextString
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 800
        ];

        $response = null;
        foreach ($keys as $apiKey) {
            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', $payload);

            // Break on success or non-quota error
            if ($response->successful()) {
                break;
            }

            // 429 = rate limit / quota exceeded — try next key
            if ($response->status() !== 429) {
                break;
            }

            Log::warning('Groq key quota exceeded, trying next key', ['status' => $response->status()]);
        }

        if (!$response || !$response->successful()) {
            Log::error('Groq AI request failed on all keys', [
                'status' => $response?->status(),
                'body' => $response?->body(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI request failed',
            ], 502);
        }

        $data = $response->json();
        $answer = $data['choices'][0]['message']['content'] ?? null;

        if (!$answer) {
            return response()->json([
                'success' => false,
                'message' => 'Empty AI response',
            ], 502);
        }

        return response()->json(['success' => true, 'answer' => trim($answer)]);
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
