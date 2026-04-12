<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function getParent($phone)
    {
        $parent = \App\Models\User::where('whatsapp_phone', $phone)
            ->where('role', 'parent')
            ->with('students.schoolClass')
            ->first();

        if (!$parent) {
            return response()->json(['success' => false, 'message' => 'Parent not found'], 404);
        }

        return response()->json(['success' => true, 'parent' => $parent]);
    }

    public function registerParent(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'admission_number' => 'required',
            'phone' => 'required'
        ]);

        $parent = \App\Models\User::where('email', $request->email)->where('role', 'parent')->first();
        if (!$parent) {
            return response()->json(['success' => false, 'message' => 'Parent email not found'], 404);
        }

        $student = \App\Models\Student::where('admission_number', $request->admission_number)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Student not linked to this parent email'], 403);
        }

        $otp = rand(100000, 999999);
        \Illuminate\Support\Facades\Cache::put('whatsapp_otp_' . $request->phone, [
            'parent_id' => $parent->id,
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

        $parent = \App\Models\User::find($cached['parent_id']);
        $parent->whatsapp_phone = $request->phone;
        $parent->whatsapp_verified = true;
        $parent->whatsapp_subscribed = true;
        $parent->save();

        \Illuminate\Support\Facades\Cache::forget('whatsapp_otp_' . $request->phone);

        $parent->load('students.schoolClass');

        return response()->json(['success' => true, 'message' => 'Registration successful', 'parent' => $parent]);
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

        $parent = \App\Models\User::where('role', 'parent')
            ->whereKey($request->parent_id)
            ->firstOrFail();

        if (!empty($request->key) && !empty($parent->whatsapp_ai_key_hash)) {
            if (hash('sha256', $request->key) !== $parent->whatsapp_ai_key_hash) {
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

        $context = $this->buildParentContext($parent);
        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are MyAcademy Parent Assistant. Answer only using the provided parent and student data. If the answer is not in the data, say you do not have access to that information. Keep responses concise and clear.'
                ],
                [
                    'role' => 'user',
                    'content' => "Parent Data:\n" . json_encode($context, JSON_UNESCAPED_SLASHES) . "\n\nQuestion: " . $request->question
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

    public function subscribe($parentId)
    {
        $parent = \App\Models\User::where('role', 'parent')->whereKey($parentId)->firstOrFail();
        $parent->whatsapp_subscribed = true;
        $parent->save();

        return response()->json(['success' => true, 'message' => 'Subscribed']);
    }

    public function unsubscribe($parentId)
    {
        $parent = \App\Models\User::where('role', 'parent')->whereKey($parentId)->firstOrFail();
        $parent->whatsapp_subscribed = false;
        $parent->save();

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
}
