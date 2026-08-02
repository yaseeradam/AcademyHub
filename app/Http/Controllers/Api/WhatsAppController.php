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
                $valid    = hash_equals($expected, $password);
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
                $q->with('sheet')->latest()->limit(5);
            },
            'students.scores' => function ($q) {
                $q->with('subject');
            },
        ]);

        $phone = preg_replace('/\D/', '', $parent->whatsapp_phone ?: '');
        $activeStudentId = null;
        if (!empty($phone)) {
            $activeStudentId = \Illuminate\Support\Facades\Cache::get("whatsapp_active_student_{$phone}");
        }

        $tenant = $parent->tenant;
        $gatewayActive = $tenant ? $tenant->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists() : false;
        $subaccountStatus = $tenant ? ($tenant->settings['payment_gateway']['subaccount_status'] ?? 'not_submitted') : 'not_submitted';
        $onlinePaymentActive = $gatewayActive && ($subaccountStatus === 'approved');

        $dayOfWeek = now()->dayOfWeekIso;

        $events = \App\Models\SchoolEvent::where('starts_at', '>=', today())
            ->orderBy('starts_at')
            ->limit(5)
            ->get()
            ->map(function($e) use ($parent) {
                $rsvp = \App\Models\EventRsvp::where('event_id', $e->id)
                    ->where('user_id', $parent->id)
                    ->first();
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'description' => $e->description,
                    'date' => $e->starts_at?->toDateString(),
                    'location' => $e->location,
                    'your_rsvp_status' => $rsvp ? $rsvp->status : 'no response yet'
                ];
            })->all();

        $announcements = \App\Models\Announcement::whereIn('audience', ['parent', 'all'])
            ->where('published_at', '<=', now())
            ->latest()
            ->limit(5)
            ->get(['title', 'body', 'published_at'])
            ->map(fn($a) => [
                'title' => $a->title,
                'body' => strip_tags((string)$a->body),
                'date' => $a->published_at?->toDateString()
            ])->all();

        return [
            'parent' => [
                'name' => $parent->name,
                'email' => $parent->email,
                'whatsapp_phone' => $parent->whatsapp_phone,
                'whatsapp_subscribed' => (bool) $parent->whatsapp_subscribed,
            ],
            'active_session_student_id' => $activeStudentId,
            'academic_system' => [
                'active_term' => \App\Models\AcademicTerm::activeTermNumber(),
                'active_term_name' => \App\Models\AcademicTerm::active() ? \App\Models\AcademicTerm::active()->name : 'First Term',
                'active_session' => \App\Models\AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1),
                'today_date' => today()->format('l, F j, Y'),
            ],
            'school' => [
                'name' => config('academyhub.school_name'),
                'phone' => config('academyhub.school_phone'),
                'email' => config('academyhub.school_email'),
            ],
            'upcoming_events' => $events,
            'recent_announcements' => $announcements,
            'students' => $parent->students->map(function ($student) use ($dayOfWeek, $onlinePaymentActive) {
                $todayMark = $student->attendanceMarks->first(function ($m) {
                    return $m->sheet && \Carbon\Carbon::parse($m->sheet->date)->isToday();
                });

                $attendanceTodayText = 'Not Marked Yet Today (Roll call pending)';
                if ($todayMark) {
                    if ($todayMark->status === 'P') $attendanceTodayText = 'Present';
                    elseif ($todayMark->status === 'L') $attendanceTodayText = 'Late';
                    elseif ($todayMark->status === 'A') $attendanceTodayText = 'Absent';
                }

                $attendanceHistory = $student->attendanceMarks->map(function ($m) {
                    $dateStr = $m->sheet ? \Carbon\Carbon::parse($m->sheet->date)->format('D, M j, Y') : 'Unknown Date';
                    $statusText = match($m->status) {
                        'P' => 'Present',
                        'L' => 'Late',
                        'A' => 'Absent',
                        default => $m->status
                    };
                    return [
                        'date' => $dateStr,
                        'status' => $statusText,
                    ];
                })->values()->all();

                $apiKey = config('services.whatsapp.api_key');
                $activeTermNumber = \App\Models\AcademicTerm::activeTermNumber();
                $activeSessionName = \App\Models\AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1);
                
                $reportCardUrl = route('whatsapp.report-card', [
                    'studentId' => $student->id,
                    'key'       => $apiKey,
                    'term'      => $activeTermNumber,
                    'session'   => $activeSessionName,
                    't'         => time()
                ]);

                // Calculate Tuition billing balances dynamically
                $feeStructure = \App\Models\FeeStructure::where('class_id', $student->class_id)
                    ->where('term', $activeTermNumber)
                    ->where('session', $activeSessionName)
                    ->first();

                $amountDue = $feeStructure ? (float) $feeStructure->amount_due : 0.0;

                $amountPaid = (float) \App\Models\Transaction::where('student_id', $student->id)
                    ->where('type', 'Income')
                    ->where('term', $activeTermNumber)
                    ->where('session', $activeSessionName)
                    ->where('is_void', false)
                    ->sum('amount_paid');

                $outstandingBalance = max(0.0, $amountDue - $amountPaid);

                $paymentUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'whatsapp.pay',
                    now()->addHours(24),
                    [
                        'studentId' => $student->id,
                        'term'      => $activeTermNumber,
                        'session'   => $activeSessionName,
                        'amount'    => $outstandingBalance,
                        'key'       => $apiKey
                    ]
                );
                
                $timetable = \App\Models\TimetableEntry::where('class_id', $student->class_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->with('subject')
                    ->orderBy('starts_at')
                    ->get()
                    ->map(fn($t) => [
                        'subject' => $t->subject?->name,
                        'time' => $t->starts_at . ' - ' . $t->ends_at,
                        'room' => $t->room
                    ])->all();

                $studentClassIds = \App\Models\Score::where('student_id', $student->id)
                    ->pluck('class_id')
                    ->unique()
                    ->filter()
                    ->push($student->class_id)
                    ->toArray();

                $publishedResults = \App\Models\ResultPublication::whereIn('class_id', $studentClassIds)
                    ->get()
                    ->map(fn($pub) => [
                        'term' => $pub->term,
                        'session' => $pub->session,
                        'published_at' => $pub->published_at?->toDateString(),
                    ])->all();

                $homework = $student->getHomeworkForStudent()->map(function ($hw) {
                    return [
                        'subject' => $hw->subject?->name,
                        'title' => $hw->title,
                        'due_date' => $hw->due_date,
                        'is_submitted' => $hw->submissions->isNotEmpty(),
                    ];
                })->values()->all();

                return [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'admission_number' => $student->admission_number,
                    'class' => $student->schoolClass?->name,
                    'attendance_today' => $attendanceTodayText,
                    'recent_attendance_history' => $attendanceHistory,
                    'report_card_url' => $reportCardUrl,
                    'tuition_fees' => [
                        'amount_due'          => $amountDue,
                        'amount_paid'         => $amountPaid,
                        'outstanding_balance' => $outstandingBalance,
                        'payment_checkout_url'=> ($outstandingBalance > 0 && $onlinePaymentActive) ? $paymentUrl : ($onlinePaymentActive ? 'PAID' : 'DISABLED'),
                        'online_payment_enabled' => $onlinePaymentActive,
                    ],
                    'today_timetable' => $timetable,
                    'published_results' => $publishedResults,
                    'active_homework' => $homework,
                    'recent_scores' => $student->scores->map(function ($score) {
                        return [
                            'subject' => $score->subject?->name,
                            'total_score' => $score->total,
                            'term' => $score->term ?? null,
                            'session' => $score->session ?? null,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    private function buildStaffContext(\App\Models\User $staff, ?string $queryText = null): array
    {
        // 1. Get school details
        $school = [
            'name' => config('academyhub.school_name'),
            'phone' => config('academyhub.school_phone'),
            'email' => config('academyhub.school_email'),
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

        $dayOfWeek = now()->dayOfWeekIso;

        $events = \App\Models\SchoolEvent::where('starts_at', '>=', today())
            ->orderBy('starts_at')
            ->limit(5)
            ->get()
            ->map(function($e) {
                $yesCount = \App\Models\EventRsvp::where('event_id', $e->id)->where('status', 'yes')->count();
                $noCount = \App\Models\EventRsvp::where('event_id', $e->id)->where('status', 'no')->count();
                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'description' => $e->description,
                    'date' => $e->starts_at?->toDateString(),
                    'location' => $e->location,
                    'rsvp_summary' => [
                        'attending' => $yesCount,
                        'not_attending' => $noCount,
                    ]
                ];
            })->all();

        $announcements = \App\Models\Announcement::whereIn('audience', ['staff', 'all'])
            ->where('published_at', '<=', now())
            ->latest()
            ->limit(5)
            ->get(['title', 'body', 'published_at'])
            ->map(fn($a) => [
                'title' => $a->title,
                'body' => strip_tags((string)$a->body),
                'date' => $a->published_at?->toDateString()
            ])->all();

        // Teacher personal timetable today
        $teachingSchedule = \App\Models\TimetableEntry::where('teacher_id', $staff->id)
            ->where('day_of_week', $dayOfWeek)
            ->with(['schoolClass', 'subject'])
            ->orderBy('starts_at')
            ->get()
            ->map(fn($t) => [
                'class' => $t->schoolClass?->name,
                'subject' => $t->subject?->name,
                'time' => $t->starts_at . ' - ' . $t->ends_at,
                'room' => $t->room
            ])->all();

        $financials = [];
        if (in_array($staff->role, ['admin', 'superadmin', 'bursar'])) {
            $activeTermNumber = \App\Models\AcademicTerm::activeTermNumber();
            $activeSessionName = \App\Models\AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1);
            $totalFeesCollected = (float) \App\Models\Transaction::where('type', 'Income')
                ->where('term', $activeTermNumber)
                ->where('session', $activeSessionName)
                ->where('is_void', false)
                ->sum('amount_paid');
            $financials = [
                'term_fees_collected' => $totalFeesCollected,
                'currency_symbol' => config('academyhub.currency_symbol', '₦'),
            ];
        }

        // Active student lookup based on queryText or active session selection
        $matched = collect();
        if (!empty($queryText)) {
            $cleanText = preg_replace('/[^\w\s]/', '', $queryText);
            $words = array_filter(explode(' ', $cleanText));
            $rawWords = array_filter(explode(' ', $queryText));
            
            $searchWords = array_filter($words, fn($w) => strlen(trim($w)) >= 2);
            $rawSearchWords = array_filter($rawWords, fn($w) => strlen(trim($w)) >= 2);

            if (!empty($searchWords) || !empty($rawSearchWords)) {
                $studentQuery = \App\Models\Student::where('status', 'Active');
                $studentQuery->where(function($q) use ($searchWords, $rawSearchWords) {
                    foreach ($searchWords as $word) {
                        $q->orWhere('first_name', 'like', "%{$word}%")
                          ->orWhere('last_name', 'like', "%{$word}%")
                          ->orWhere('admission_number', 'like', "%{$word}%");
                    }
                    foreach ($rawSearchWords as $rawWord) {
                        $q->orWhere('admission_number', 'like', "%{$rawWord}%");
                    }
                });

                $matched = $studentQuery->with(['schoolClass', 'attendanceMarks' => function($q) {
                    $q->whereHas('sheet', function ($sq) {
                        $sq->whereDate('date', today());
                    });
                }, 'scores' => function($q) {
                    $q->with('subject');
                }])->limit(5)->get();
            }
        }

        // Always include the active cached student if not already matched
        $phoneClean = preg_replace('/\D/', '', $staff->whatsapp_phone ?: '');
        $activeStudentId = !empty($phoneClean) ? \Illuminate\Support\Facades\Cache::get("whatsapp_active_student_{$phoneClean}") : null;
        if ($activeStudentId) {
            $alreadyMatched = $matched->contains('id', $activeStudentId);
            if (!$alreadyMatched) {
                $activeStudent = \App\Models\Student::with(['schoolClass', 'attendanceMarks' => function($q) {
                    $q->whereHas('sheet', function ($sq) {
                        $sq->whereDate('date', today());
                    });
                }, 'scores' => function($q) {
                    $q->with('subject');
                }])->find($activeStudentId);

                if ($activeStudent && $activeStudent->status === 'Active') {
                    $matched->push($activeStudent);
                }
            }
        }

        $apiKey = config('services.whatsapp.api_key');
        $activeTermNumber = \App\Models\AcademicTerm::activeTermNumber();
        $activeSessionName = \App\Models\AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1);

        $students = $matched->map(function($student) use ($apiKey, $activeTermNumber, $activeSessionName) {
            $attendance = $student->attendanceMarks->first();
            $reportCardUrl = route('whatsapp.report-card', [
                'studentId' => $student->id,
                'key'       => $apiKey,
                'term'      => $activeTermNumber,
                'session'   => $activeSessionName,
                't'         => time()
            ]);

            $studentClassIds = \App\Models\Score::where('student_id', $student->id)
                ->pluck('class_id')
                ->unique()
                ->filter()
                ->push($student->class_id)
                ->toArray();

            $publishedResults = \App\Models\ResultPublication::whereIn('class_id', $studentClassIds)
                ->get()
                ->map(fn($pub) => [
                    'term' => $pub->term,
                    'session' => $pub->session,
                    'published_at' => $pub->published_at?->toDateString(),
                ])->all();

            return [
                'id' => $student->id,
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class' => $student->schoolClass?->name,
                'attendance_today' => $attendance ? $attendance->status : null,
                'report_card_url' => $reportCardUrl,
                'published_results' => $publishedResults,
                'recent_scores' => $student->scores->map(function ($score) {
                    return [
                        'subject' => $score->subject?->name,
                        'total_score' => $score->total,
                        'term' => $score->term ?? null,
                        'session' => $score->session ?? null,
                    ];
                })->values()->all(),
            ];
        })->all();

        return [
            'user' => [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
            ],
            'active_session_student_id' => $activeStudentId,
            'academic_system' => [
                'active_term' => \App\Models\AcademicTerm::activeTermNumber(),
                'active_term_name' => \App\Models\AcademicTerm::active() ? \App\Models\AcademicTerm::active()->name : 'First Term',
                'active_session' => \App\Models\AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1),
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
                'financials' => $financials,
            ],
            'classes' => $classesBreakdown,
            'teaching_schedule_today' => $teachingSchedule,
            'upcoming_events' => $events,
            'recent_announcements' => $announcements,
            'students' => $students,
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
        
        $dayOfWeek = now()->dayOfWeekIso;

        $events = \App\Models\SchoolEvent::where('starts_at', '>=', today())
            ->orderBy('starts_at')
            ->limit(5)
            ->get(['title', 'description', 'starts_at', 'location'])
            ->map(fn($e) => [
                'title' => $e->title,
                'description' => $e->description,
                'date' => $e->starts_at?->toDateString(),
                'location' => $e->location
            ])->all();

        $announcements = \App\Models\Announcement::whereIn('audience', ['student', 'all'])
            ->where('published_at', '<=', now())
            ->latest()
            ->limit(5)
            ->get(['title', 'body', 'published_at'])
            ->map(fn($a) => [
                'title' => $a->title,
                'body' => strip_tags((string)$a->body),
                'date' => $a->published_at?->toDateString()
            ])->all();

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

        $studentClassIds = \App\Models\Score::where('student_id', $student->id)
            ->pluck('class_id')
            ->unique()
            ->filter()
            ->push($student->class_id)
            ->toArray();

        $publishedResults = \App\Models\ResultPublication::whereIn('class_id', $studentClassIds)
            ->get()
            ->map(fn($pub) => [
                'term' => $pub->term,
                'session' => $pub->session,
                'published_at' => $pub->published_at?->toDateString(),
            ])->all();

        // Fetch recent scores/results
        $scores = $student->scores()
            ->with('subject')
            ->get()
            ->map(function ($score) {
                return [
                    'subject' => $score->subject?->name,
                    'total_score' => $score->total,
                    'term' => $score->term,
                    'session' => $score->session,
                ];
            })->values()->all();

        $timetable = \App\Models\TimetableEntry::where('class_id', $student->class_id)
            ->where('day_of_week', $dayOfWeek)
            ->with('subject')
            ->orderBy('starts_at')
            ->get()
            ->map(fn($t) => [
                'subject' => $t->subject?->name,
                'time' => $t->starts_at . ' - ' . $t->ends_at,
                'room' => $t->room
            ])->all();

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class' => $student->schoolClass?->name,
                'section' => $student->section?->name,
                'attendance_today' => $attendance ? $attendance->status : null,
            ],
            'academic_system' => [
                'active_term' => \App\Models\AcademicTerm::activeTermNumber(),
                'active_term_name' => \App\Models\AcademicTerm::active() ? \App\Models\AcademicTerm::active()->name : 'First Term',
                'active_session' => \App\Models\AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1),
            ],
            'homework' => $homework,
            'published_results' => $publishedResults,
            'recent_scores' => $scores,
            'today_timetable' => $timetable,
            'upcoming_events' => $events,
            'recent_announcements' => $announcements,
            'school' => [
                'name' => config('academyhub.school_name'),
                'phone' => config('academyhub.school_phone'),
                'email' => config('academyhub.school_email'),
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
            'phone' => config('academyhub.school_phone'),
            'email' => config('academyhub.school_email'),
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
            'target' => 'required',
            'message' => 'required'
        ]);

        $user = $request->user() ?: ($request->input('user_id') ? \App\Models\User::find($request->input('user_id')) : null);
        if (!$user || !in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized broadcast attempt.'], 403);
        }

        $target = strtolower($request->target);

        // Also save to Announcements table for portal visibility
        $title = $request->input('title', 'School Broadcast');
        $body = $request->message;
        \App\Models\Announcement::create([
            'title' => $title,
            'body' => $body,
            'audience' => $target,
            'published_at' => now(),
            'created_by' => $user->id,
        ]);

        $query = \App\Models\User::where('whatsapp_subscribed', true)
            ->whereNotNull('whatsapp_phone')
            ->where('id', '!=', $user->id);

        if ($target === 'parents' || $target === 'parent') {
            $query->where('role', 'parent');
        } elseif ($target === 'staff') {
            $query->whereIn('role', ['teacher', 'bursar', 'admin']);
        } elseif ($target === 'all') {
            // Keep all
        }

        $phones = $query->pluck('whatsapp_phone')->toArray();

        $successCount = 0;
        foreach ($phones as $phone) {
            $sent = $this->sendMetaMessage($phone, "📢 *Broadcast from Admin*\n\n" . $request->message);
            if ($sent) {
                $successCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Broadcast successfully published to portal and dispatched to {$successCount} users!"
        ]);
    }

    public function askAi(Request $request)
    {
        $request->validate([
            'parent_id' => 'required',
            'question'  => 'required|string',
        ]);

        $user = \App\Models\User::findOrFail($request->parent_id);
        
        $tenant = $user->tenant;
        if ($tenant) {
            app()->instance('currentTenant', $tenant);
            $this->loadTenantSettings($tenant);
        }
        
        if ($user->role === 'parent') {
            $context = $this->buildParentContext($user);
            $roleLabel = 'parent';
        } elseif ($user->role === 'student') {
            $context = $this->buildStudentContext($user);
            $roleLabel = 'student';
        } else {
            $context = $this->buildStaffContext($user, $request->question);
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

        $prompt = "You are HubGenie, the official school coordinator assistant for the AcademyHub school management system.\n" .
                  "You are chatting with a {$roleLabel} on WhatsApp. Here is the verified, real-time context of the logged-in user, their school, academic calendar, and system statistics:\n" .
                  "{$contextJson}\n\n" .
                  $historyText . "\n" .
                  "User's Question:\n" .
                  "\"{$request->question}\"\n\n" .
                  "STRICT RULES & CONSTRAINTS:\n" .
                  "1. CONVERSATIONAL TONE: Write in a natural, direct, human voice. Avoid any robotic or clinical language. Speak as if you are a helpful school coordinator checking the school registry.\n" .
                  "2. FORBIDDEN WORDS: NEVER use words like 'AI', 'bot', 'virtual assistant', 'context', 'database', 'system', 'limitations', or 'model'. Do not refer to yourself as a robot, software, or computer program.\n" .
                  "3. NO FLUFF: Answer the user's question directly in 1-3 sentences maximum. Remove all conversational fluff, warnings, repetitive greetings, and friendly closing remarks (e.g., do not say 'I am here to help' or 'Let me know if you need anything else' in every message).\n" .
                  "4. GREETINGS: Do NOT greet the user (e.g., 'Hello', 'Hi') unless they specifically greeted you in their question. Jump directly to the answer.\n" .
                  "5. UNKNOWN INFORMATION: If the user asks a question about something not in the provided records, do NOT say 'I don't have access to this information as an AI'. Instead, say naturally: 'I don't have that in our school records at the moment. Please contact the school administration office directly.'\n" .
                  "6. STUDENT NAMES: Always fetch and explicitly state the student(s) full name(s) (e.g. 'Abdullahi Bala') when replying to questions about children, homework, attendance, or scores. Never refer to them generically as 'your child' or 'your student' if their name is available in the context.\n" .
                  "7. SECURITY & SENSITIVE DATA: Never reveal, discuss, or query sensitive information (e.g. passwords, password hashes, login tokens, secret keys, API credentials, or internal database schemas). Under no circumstances should you retrieve or expose password details.\n" .
                  "8. PASSWORD PROTECTION: If the user asks about passwords, credentials, resetting their password, or secure keys, you MUST immediately reject it and say exactly: '🔒 For security, passwords and login credentials cannot be accessed, modified, or discussed via WhatsApp.'\n" .
                  "9. WHATSAPP FORMATTING RULES:\n" .
                  "   - BOLD: Format bold text using single asterisks (e.g., *this is bold*). NEVER use double asterisks (**).\n" .
                  "   - LISTS: Use the literal bullet character • followed by a space at the start of list items. Write each list item on a new line. Do not use markdown bullet styles like '-' or '*'.\n" .
                  "   - HEADINGS: Do NOT use markdown `#`, `##` or `###`. Use simple bold capital letters for headers (e.g. *ATTENDANCE STATUS*).\n" .
                  "   - LINE BREAKS: Use clean single or double line breaks between paragraphs/points to make the text readable on a mobile screen.\n" .
                  "10. DYNAMIC REPORT CARDS (PDF Delivery):\n" .
                  "   - If a parent or user asks to download, receive, view, or get a PDF report card for a student, you MUST check if they specified which student child they meant (if they have multiple children listed under students in the context).\n" .
                  "   - If they have multiple children, and they did NOT explicitly name the child they want the report card for, you MUST output this exact hidden tag at the very end of your response: '[AMBIGUOUS_REPORT_CARD: <term_number>|<academic_session>]' and politely ask them to choose.\n" .
                  "   - If the student is clearly identified (or they only have one child), you MUST generate and append this exact hidden tag: '[SEND_PDF: <student_id>|<term_number>|<academic_session>]'\n" .
                  "   - Resolve the <student_id> from the students in the context.\n" .
                  "   - Resolve the <term_number> strictly as a digit: 1, 2, or 3. If a previous term is requested (e.g. 'first term', 'term 2'), resolve it to the correct digit. If no term is specified, default to the current active term from the academic_system metadata.\n" .
                  "   - Resolve the <academic_session> in YYYY/YYYY format (e.g. '2026/2027'). If a previous session is mentioned (e.g. 'last year', '2025/2026'), resolve it. If no session is specified, default to the active session from the academic_system metadata.\n" .
                  "   - You can output multiple SEND_PDF tags if they request report cards for multiple children or multiple terms in a single prompt.\n" .
                  "11. INTENT DETECTION & SUPPORT TICKETS: If the user indicates they want to report an issue, log a complaint, offer feedback, report missing grades/attendance, or request a call back from the school administration, you MUST append a hidden tag at the very end of your response: '[SUPPORT_TICKET_DETECTED: <message>]' where <message> is a clear, concise 1-sentence summary of the user's actual problem or concern. Do not include this tag for standard information questions (e.g. asking for grades, schedules, or events).\n" .
                  "12. HANDLING STUDENT RESULTS: If asked about student results, academic performance, report cards, or scores: Check if the child name or admission number is specified in the question. If not, and there are multiple children listed in the context, ask the user to specify which student they are asking about. If the student is identified, verify if the results for the requested term and session are officially published (existence of a record matching that term and session in the student's `published_results` list). If the results are NOT published, you MUST politely inform the user that the academic results for that term/session have not been officially published yet by the school administration, instead of saying you don't have access to this information. If the results ARE published, list the scores from the `recent_scores` data matching the requested term/session, always listing the subject name and score (e.g., Mathematics: 85/100).\n" .
                  "13. ACTIVE SESSION STUDENT: If 'active_session_student_id' is set in the context (not null), this indicates the student the parent has been actively chatting about or selected recently. Prioritize this student in your answers unless the user names a different child. Additionally, if your answer resolves to or discusses a specific single student from the context, you MUST append a hidden tag at the very end of your response: '[ACTIVE_STUDENT_SELECTED: <student_id>]' where <student_id> is that student's ID from the context. Do not append this tag if you are talking about multiple children or general school information.";

        // Use Groq API exclusively
        $answer = $this->tryGroqAPI($prompt);

        if ($answer) {
            $ticketDetected = false;
            $ticketMessage = '';
            
            // Check for [SUPPORT_TICKET_DETECTED: ...]
            if (preg_match('/\[SUPPORT_TICKET_DETECTED:\s*(.*?)\]/i', $answer, $matches)) {
                $ticketDetected = true;
                $ticketMessage = trim($matches[1]);
                // Strip the tag from the final response
                $answer = trim(preg_replace('/\[SUPPORT_TICKET_DETECTED:\s*.*?\]/i', '', $answer));
            }
            
            if ($ticketDetected && !empty($ticketMessage)) {
                // Log support ticket in database
                $ticket = \App\Models\SupportTicket::create([
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'phone' => $user->whatsapp_phone,
                    'message' => $ticketMessage,
                    'status' => 'open',
                    'channel' => 'whatsapp',
                ]);

                // Trigger superadmin notification
                if ($user->tenant) {
                    \App\Models\SuperadminNotification::notifySupportTicket($user->tenant, $ticket);
                }
                
                // Append a friendly confirmation of ticket creation
                $answer .= "\n\n🔒 I have successfully recorded your support request in the school database. A school administrator has been notified and will contact you shortly.";
            }

            // Parse [AMBIGUOUS_REPORT_CARD: term|session] tags
            $ambiguousDetected = false;
            $ambiguousTerm = null;
            $ambiguousSession = null;
            if (preg_match('/\[AMBIGUOUS_REPORT_CARD:\s*([^\]|]+)\|([^\]|]+)\]/i', $answer, $matches)) {
                $ambiguousDetected = true;
                $ambiguousTerm = (int) trim($matches[1]);
                $ambiguousSession = trim($matches[2]);
                $answer = trim(preg_replace('/\[AMBIGUOUS_REPORT_CARD:\s*.*?\]/i', '', $answer));
            }

            if ($ambiguousDetected) {
                $answer .= "\n\n🎒 *[Select Child to Download]*\n";
                $apiKey = config('services.whatsapp.api_key');
                foreach ($context['students'] ?? [] as $s) {
                    $studentId = $s['id'];
                    $studentName = $s['name'];
                    $reportUrl = route('whatsapp.report-card', [
                        'studentId' => $studentId, 
                        'key'       => $apiKey, 
                        'term'      => $ambiguousTerm, 
                        'session'   => $ambiguousSession,
                        't'         => time()
                    ]);
                    $answer .= "• [{$studentName}]({$reportUrl}) (Term {$ambiguousTerm} - {$ambiguousSession})\n";
                }
            }

            // Parse any [SEND_PDF: student_id|term|session] tags
            $pdfs = [];
            if (preg_match_all('/\[SEND_PDF:\s*([^\]|]+)\|([^\]|]+)\|([^\]|]+)\]/i', $answer, $matches, PREG_SET_ORDER)) {
                $apiKey = config('services.whatsapp.api_key');
                foreach ($matches as $match) {
                    $pdfStudentId = trim($match[1]);
                    $pdfTerm = (int) trim($match[2]);
                    $pdfSession = trim($match[3]);
                    
                    $studentObj = \App\Models\Student::find($pdfStudentId);
                    if ($studentObj) {
                        $reportUrl = route('whatsapp.report-card', [
                            'studentId' => $studentObj->id, 
                            'key'       => $apiKey, 
                            'term'      => $pdfTerm, 
                            'session'   => $pdfSession,
                            't'         => time()
                        ]);
                        
                        $pdfs[] = [
                            'student_name' => $studentObj->full_name,
                            'term'         => $pdfTerm,
                            'session'      => $pdfSession,
                            'url'          => $reportUrl
                        ];
                        
                        $answer .= "\n\n📄 *[Download PDF Report Card]*\nStudent: *{$studentObj->full_name}*\nTerm: *Term {$pdfTerm} ({$pdfSession})*\nLink: {$reportUrl}";
                    }
                }
                // Strip all tags from final answer
                $answer = trim(preg_replace('/\[SEND_PDF:\s*.*?\]/i', '', $answer));
            }
            
            // Parse [ACTIVE_STUDENT_SELECTED: student_id] tags
            if (preg_match('/\[ACTIVE_STUDENT_SELECTED:\s*(\d+)\]/i', $answer, $matches)) {
                $selStudentId = (int) $matches[1];
                $phone = preg_replace('/\D/', '', $user->whatsapp_phone ?: '');
                if (!empty($phone)) {
                    \Illuminate\Support\Facades\Cache::put("whatsapp_active_student_{$phone}", $selStudentId, now()->addMinutes(30));
                }
                $answer = trim(preg_replace('/\[ACTIVE_STUDENT_SELECTED:\s*.*?\]/i', '', $answer));
            }

            return response()->json([
                'success' => true,
                'answer'  => $answer,
                'pdfs'    => $pdfs,
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

        // Resolve tenant context and load the latest settings so the school
        // selected report card template and display options are always used.
        $tenant = \App\Models\Tenant::find($student->tenant_id);
        if ($tenant) {
            app()->instance('currentTenant', $tenant);
            $this->loadTenantSettings($tenant, forceRefresh: true);
        }

        $term = (int) $request->query('term', 1);
        $session = (string) $request->query('session');

        if (empty($session)) {
            $active = \App\Models\AcademicSession::activeName();
            $session = $active ?: date('Y') . '/' . (date('Y') + 1);
        }

        // Verify if results are officially published for the class(es) the student has scores in for this term/session
        $scoreClassIds = \App\Models\Score::where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->pluck('class_id')
            ->unique()
            ->filter()
            ->toArray();

        if (empty($scoreClassIds)) {
            $scoreClassIds = [$student->class_id];
        }

        $isPublished = \App\Models\ResultPublication::whereIn('class_id', $scoreClassIds)
            ->where('term', $term)
            ->where('session', $session)
            ->exists();

        if (!$isPublished) {
            abort(403, 'The report card for this term and session has not been officially published yet by the school administration.');
        }

        $template = (string) config('academyhub.report_card_template', 'compact');
        $safeTemplate = preg_replace('/[^a-z0-9_-]/i', '', $template) ?: 'compact';
        $sessionSlug = str_replace('/', '-', $session);

        $cacheDir = storage_path('app/public/report-cards');
        if (!\Illuminate\Support\Facades\File::exists($cacheDir)) {
            \Illuminate\Support\Facades\File::makeDirectory($cacheDir, 0755, true);
        }

        // Include the selected template in the cache key so switching templates
        // does not serve a stale PDF generated with the old design.
        $cachedFilename = "report-card-{$studentId}-T{$term}-{$sessionSlug}-{$safeTemplate}.pdf";
        $cachedPath = "{$cacheDir}/{$cachedFilename}";

        // Cache invalidation: regenerate when scores, settings, student details, or attendance change.
        $lastScoreAt = \App\Models\Score::where('student_id', $studentId)
            ->where('term', $term)
            ->where('session', $session)
            ->max('updated_at');
        $lastScoreTimestamp = $lastScoreAt ? \Carbon\Carbon::parse($lastScoreAt)->timestamp : null;

        $lastAttendanceAt = \App\Models\AttendanceMark::where('student_id', $studentId)->max('updated_at');
        $lastAttendanceTimestamp = $lastAttendanceAt ? \Carbon\Carbon::parse($lastAttendanceAt)->timestamp : null;

        $settingsPath = $tenant
            ? storage_path('app/academyhub/tenants/' . $tenant->id . '/settings.json')
            : storage_path('app/academyhub/settings.json');
        $settingsTimestamp = \Illuminate\Support\Facades\File::exists($settingsPath)
            ? \Illuminate\Support\Facades\File::lastModified($settingsPath)
            : null;

        $studentTimestamp = $student->updated_at ? $student->updated_at->timestamp : null;

        $useCache = false; // Bypass local caching to guarantee latest data

        $student->load(['schoolClass', 'section']);
        $data = app(\App\Support\ReportCardService::class)->build($student, $term, $session);

        $view = \App\Support\ReportCardService::viewForTemplate($template);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            ...$data,
        ])->setPaper('a4');

        $pdfOutput = $pdf->output();

        $filename = 'report-card-' . $student->admission_number . '-' . $sessionSlug . '-T' . $term . '.pdf';

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
        ]);
    }

    public function getReceiptPDF(Request $request, $transactionId)
    {
        $transaction = \App\Models\Transaction::findOrFail($transactionId);
        abort_unless($transaction->type === 'Income', 404);

        $student = $transaction->student;
        if ($student) {
            $tenant = \App\Models\Tenant::find($student->tenant_id);
            if ($tenant) {
                app()->instance('currentTenant', $tenant);
                $this->loadTenantSettings($tenant);
            }
        }

        $transaction->load('student');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', [
            'transaction' => $transaction,
        ])->setPaper('a4');

        $filename = "receipt-{$transaction->receipt_number}.pdf";

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function checkout(Request $request)
    {
        $studentId = (int) $request->query('studentId');
        $term = (int) $request->query('term', 1);
        $session = (string) $request->query('session');
        $amount = (float) $request->query('amount', 0.0);
        $key = (string) $request->query('key');

        $student = \App\Models\Student::findOrFail($studentId);
        
        $tenant = \App\Models\Tenant::find($student->tenant_id);
        if ($tenant) {
            app()->instance('currentTenant', $tenant);
            $this->loadTenantSettings($tenant);
        }

        // Verify gateway status and plugin activation
        $gatewayActive = $tenant->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists();
        $subaccountStatus = $tenant->settings['payment_gateway']['subaccount_status'] ?? 'not_submitted';

        if (!$gatewayActive || $subaccountStatus !== 'approved') {
            abort(403, 'Online payments are currently disabled or pending admin verification for this school.');
        }

        $parent = \App\Models\User::where('tenant_id', $student->tenant_id)
            ->where('role', 'parent')
            ->whereHas('students', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })->first();

        $parentName = $parent ? $parent->name : 'Parent / Guardian';
        $schoolName = config('academyhub.school_name', 'AcademyHub');
        $currency = config('academyhub.currency_symbol', '₦');

        return view('whatsapp.pay', [
            'student'     => $student,
            'term'        => $term,
            'session'     => $session,
            'amount'      => $amount,
            'key'         => $key,
            'parent_name' => $parentName,
            'school_name' => $schoolName,
            'currency'    => $currency,
        ]);
    }

    public function processPayment(Request $request)
    {
        $studentId = (int) $request->input('student_id');
        $term = (int) $request->input('term', 1);
        $session = (string) $request->input('session');
        $amount = (float) $request->input('amount', 0.0);

        $student = \App\Models\Student::findOrFail($studentId);
        
        // Resolve tenant context
        $tenant = \App\Models\Tenant::find($student->tenant_id);
        if ($tenant) {
            app()->instance('currentTenant', $tenant);
            $this->loadTenantSettings($tenant);
        }

        // Verify gateway status and plugin activation
        $gatewayActive = $tenant->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists();
        $subaccountStatus = $tenant->settings['payment_gateway']['subaccount_status'] ?? 'not_submitted';

        if (!$gatewayActive || $subaccountStatus !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Online payments are currently disabled or pending admin verification for this school.'
            ], 403);
        }

        // Verify that the amount matches the student's actual outstanding balance
        $feeStructure = \App\Models\FeeStructure::where('class_id', $student->class_id)
            ->where('term', $term)
            ->where('session', $session)
            ->first();

        $amountDue = $feeStructure ? (float) $feeStructure->amount_due : 0.0;

        $amountPaid = (float) \App\Models\Transaction::where('student_id', $student->id)
            ->where('type', 'Income')
            ->where('term', $term)
            ->where('session', $session)
            ->where('is_void', false)
            ->sum('amount_paid');

        $outstandingBalance = max(0.0, $amountDue - $amountPaid);

        if ($amount <= 0.0 || abs($amount - $outstandingBalance) > 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Payment validation failed: The payment amount does not match the current outstanding balance.'
            ], 400);
        }

        // Record a transaction
        $transaction = \App\Models\Transaction::create([
            'tenant_id'      => $student->tenant_id,
            'student_id'     => $student->id,
            'type'           => 'Income',
            'category'       => 'Tuition',
            'term'           => $term,
            'session'        => $session,
            'amount_paid'    => $amount,
            'payment_method' => 'Transfer',
            'date'           => now()->toDateString(),
            'is_void'        => false,
        ]);

        // Find the parent user to send a notification
        $parent = \App\Models\User::where('tenant_id', $student->tenant_id)
            ->where('role', 'parent')
            ->whereHas('students', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })->first();

        if ($parent && $parent->whatsapp_phone) {
            $currency = config('academyhub.currency_symbol', '₦');
            $formattedAmount = number_format($amount, 2);
            $schoolName = config('academyhub.school_name', 'AcademyHub');
            
            $msg = "💳 *Payment Received Successfully!*\n\n" .
                   "Thank you, *{$parent->name}*. We have successfully processed your tuition payment via WhatsApp QuickPay:\n\n" .
                   "• *Student:* {$student->full_name}\n" .
                   "• *Class:* " . ($student->schoolClass?->name ?? 'N/A') . "\n" .
                   "• *Term / Session:* Term {$term} ({$session})\n" .
                   "• *Amount Paid:* {$currency}{$formattedAmount}\n" .
                   "• *Receipt No:* {$transaction->receipt_number}\n\n" .
                   "🏫 *{$schoolName}*";

            $this->sendMetaMessage($parent->whatsapp_phone, $msg);
        }

        return response()->json([
            'success'        => true,
            'receipt_number' => $transaction->receipt_number,
            'date'           => now()->format('F j, Y, g:i a'),
        ]);
    }


    private function tryGroqAPI(string $prompt, array $history = []): ?string
    {
        $attempts = 3;
        for ($i = 0; $i < $attempts; $i++) {
            try {
                $raw = config('services.groq.key');
                if (empty($raw)) break;

                $keys = array_filter(array_map('trim', explode(',', $raw)));
                if (empty($keys)) break;
                $apiKey = $keys[array_rand($keys)];

                $messages = [
                    ['role' => 'system', 'content' => 'You are HubGenie, the official school coordinator assistant. You MUST strictly use the verified, real-time context provided in the user prompt to answer questions. Keep your responses highly focused and straight to the point (maximum 1-3 sentences). NEVER use robotic or AI terms (like AI, bot, database, context, system, model). Speak like a human administrator checking records. Format bold text with single asterisks (e.g., *bold*) and lists with literal bullets (•). NEVER use double asterisks (**) or markdown headings.'],
                ];

                foreach ($history as $msg) {
                    if (isset($msg['role']) && isset($msg['content'])) {
                        $messages[] = [
                            'role'    => $msg['role'],
                            'content' => $msg['content']
                        ];
                    }
                }

                $messages[] = ['role' => 'user', 'content' => $prompt];

                $models = ['llama-3.3-70b-versatile', 'llama-3.1-70b-versatile', 'llama3-8b-8192'];
                $chosenModel = $models[$i % count($models)];

                $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                    ->timeout(15)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type'  => 'application/json',
                    ])->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'       => $chosenModel,
                        'messages'    => $messages,
                        'temperature' => 0.7,
                        'max_tokens'  => 1000,
                    ]);

                if ($response->successful()) {
                    $content = $response->json()['choices'][0]['message']['content'] ?? null;
                    if ($content) {
                        return $content;
                    }
                }

                \Illuminate\Support\Facades\Log::warning("WhatsApp AI: Groq API attempt " . ($i + 1) . " failed.", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("WhatsApp AI: Groq API attempt " . ($i + 1) . " error", ['error' => $e->getMessage()]);
            }

            if ($i < $attempts - 1) {
                sleep(1);
            }
        }

        // Fallback to Gemini if Groq fails
        \Illuminate\Support\Facades\Log::info("WhatsApp AI: Falling back to Gemini API.");
        return $this->tryGeminiAPI($prompt, $history);
    }

    private function tryGeminiAPI(string $prompt, array $history = []): ?string
    {
        try {
            $apiKey = config('services.gemini.key');
            if (empty($apiKey)) {
                \Illuminate\Support\Facades\Log::warning('WhatsApp AI: Gemini API key not configured.');
                return null;
            }

            $contents = [];
            foreach ($history as $msg) {
                $role = ($msg['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content'] ?? '']]
                ];
            }
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $prompt]]
            ];

            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(20)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey, [
                    'contents' => $contents,
                    'systemInstruction' => [
                        'parts' => [['text' => 'You are HubGenie, the official school coordinator assistant. You MUST strictly use the verified, real-time context provided in the user prompt to answer questions. Keep your responses highly focused and straight to the point (maximum 1-3 sentences). NEVER use robotic or AI terms (like AI, bot, database, context, system, model). Speak like a human administrator checking records. Format bold text with single asterisks (e.g., *bold*) and lists with literal bullets (•). NEVER use double asterisks (**) or markdown headings.']]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1000,
                    ]
                ]);

            if ($response->successful()) {
                $ans = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($ans) return $ans;
            }

            \Illuminate\Support\Facades\Log::error('WhatsApp AI: Gemini API failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp AI: Gemini API error', ['error' => $e->getMessage()]);
        }

        // Fallback to OpenRouter
        \Illuminate\Support\Facades\Log::info("WhatsApp AI: Falling back to OpenRouter API.");
        return $this->tryOpenRouterAPI($prompt, $history);
    }

    private function tryOpenRouterAPI(string $prompt, array $history = []): ?string
    {
        try {
            $apiKey = config('services.openrouter.key');
            if (empty($apiKey)) {
                \Illuminate\Support\Facades\Log::warning('WhatsApp AI: OpenRouter API key not configured.');
                return $this->tryCohereAPI($prompt, $history);
            }

            $messages = [
                ['role' => 'system', 'content' => 'You are HubGenie, the official school coordinator assistant. You MUST strictly use the verified, real-time context provided in the user prompt to answer questions. Keep your responses highly focused and straight to the point (maximum 1-3 sentences). NEVER use robotic or AI terms (like AI, bot, database, context, system, model). Speak like a human administrator checking records. Format bold text with single asterisks (e.g., *bold*) and lists with literal bullets (•). NEVER use double asterisks (**) or markdown headings.'],
            ];

            foreach ($history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = [
                        'role'    => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $prompt];

            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'       => 'google/gemma-2-9b-it:free',
                    'messages'    => $messages,
                    'temperature' => 0.7,
                    'max_tokens'  => 1000,
                ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? null;
                if ($content) {
                    return $content;
                }
            }

            \Illuminate\Support\Facades\Log::error('WhatsApp AI: OpenRouter API failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp AI: OpenRouter API error', ['error' => $e->getMessage()]);
        }

        // Fallback to Cohere
        \Illuminate\Support\Facades\Log::info("WhatsApp AI: Falling back to Cohere API.");
        return $this->tryCohereAPI($prompt, $history);
    }

    private function tryCohereAPI(string $prompt, array $history = []): ?string
    {
        try {
            $apiKey = config('services.cohere.key');
            if (empty($apiKey)) {
                \Illuminate\Support\Facades\Log::warning('WhatsApp AI: Cohere API key not configured.');
                return null;
            }

            $chatHistory = [];
            foreach ($history as $msg) {
                $role = ($msg['role'] ?? 'user') === 'assistant' ? 'CHATBOT' : 'USER';
                $chatHistory[] = [
                    'role' => $role,
                    'message' => $msg['content'] ?? ''
                ];
            }

            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.cohere.com/v1/chat', [
                    'message' => $prompt,
                    'preamble' => 'You are HubGenie, the official school coordinator assistant. You MUST strictly use the verified, real-time context provided in the user prompt to answer questions. Keep your responses highly focused and straight to the point (maximum 1-3 sentences). NEVER use robotic or AI terms (like AI, bot, database, context, system, model). Speak like a human administrator checking records. Format bold text with single asterisks (e.g., *bold*) and lists with literal bullets (•). NEVER use double asterisks (**) or markdown headings.',
                    'chat_history' => $chatHistory,
                ]);

            if ($response->successful()) {
                return $response->json()['text'] ?? null;
            }

            \Illuminate\Support\Facades\Log::error('WhatsApp AI: Cohere API failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp AI: Cohere API error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function transcribeAudio(string $mediaId): ?string
    {
        try {
            $token = config('services.whatsapp.token');
            if (empty($token)) {
                \Illuminate\Support\Facades\Log::warning('transcribeAudio: WhatsApp token not configured.');
                return null;
            }

            // 1. Get media URL
            $url = "https://graph.facebook.com/v19.0/{$mediaId}";
            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->withHeaders(['Authorization' => 'Bearer ' . $token])
                ->get($url);

            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::error('transcribeAudio: Failed to fetch media URL', [
                    'media_id' => $mediaId,
                    'status'   => $response->status(),
                    'body'     => $response->body()
                ]);
                return null;
            }

            $mediaData = $response->json();
            $downloadUrl = $mediaData['url'] ?? null;
            if (empty($downloadUrl)) {
                \Illuminate\Support\Facades\Log::warning('transcribeAudio: No download URL found in Meta response.');
                return null;
            }

            // 2. Download the audio file
            $downloadResponse = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->withHeaders(['Authorization' => 'Bearer ' . $token])
                ->get($downloadUrl);

            if ($downloadResponse->failed()) {
                \Illuminate\Support\Facades\Log::error('transcribeAudio: Failed to download audio content', [
                    'url'    => $downloadUrl,
                    'status' => $downloadResponse->status()
                ]);
                return null;
            }

            $audioContent = $downloadResponse->body();
            $tempDir = storage_path('app');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $tempFilePath = $tempDir . DIRECTORY_SEPARATOR . 'temp_whatsapp_' . $mediaId . '.ogg';
            file_put_contents($tempFilePath, $audioContent);

            // 3. Send to Groq Whisper
            $raw = config('services.groq.key');
            if (empty($raw)) {
                \Illuminate\Support\Facades\Log::warning('transcribeAudio: Groq API key not configured.');
                unlink($tempFilePath);
                return null;
            }
            $keys = array_filter(array_map('trim', explode(',', $raw)));
            if (empty($keys)) {
                unlink($tempFilePath);
                return null;
            }
            $groqApiKey = $keys[array_rand($keys)];

            $groqResponse = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $groqApiKey,
                ])
                ->attach('file', fopen($tempFilePath, 'r'), 'audio.ogg')
                ->post('https://api.groq.com/openai/v1/audio/transcriptions', [
                    'model' => 'whisper-large-v3',
                    'temperature' => '0.0'
                ]);

            // Clean up
            unlink($tempFilePath);

            if ($groqResponse->failed()) {
                \Illuminate\Support\Facades\Log::error('transcribeAudio: Groq transcription failed', [
                    'status' => $groqResponse->status(),
                    'body'   => $groqResponse->body()
                ]);
                return null;
            }

            $result = $groqResponse->json();
            return $result['text'] ?? null;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('transcribeAudio: Exception during transcription', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function handleWebhook(Request $request)
    {
        // 1. Verification Challenge (GET)
        if ($request->isMethod('get')) {
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            $expectedToken = config('services.whatsapp.verify_token');

            if ($mode === 'subscribe' && $token === $expectedToken) {
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }

            return response('Forbidden', 403);
        }

        // 2. Event Delivery / Message Handling (POST)
        if ($request->isMethod('post')) {
            $payload = $request->all();

            // Extract incoming message details
            if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0])) {
                $changeValue = $payload['entry'][0]['changes'][0]['value'];
                $messageData = $changeValue['messages'][0];

                $messageId = $messageData['id'] ?? null;
                if ($messageId) {
                    $cacheKey = "whatsapp_msg_{$messageId}";
                    if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                        \Illuminate\Support\Facades\Log::warning("WhatsApp duplicate webhook message detected: {$messageId}. Skipping processing.");
                        return response()->json(['status' => 'success']);
                    }
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(10));
                }

                $fromRaw = $messageData['from'];
                $from = preg_replace('/\D/', '', $fromRaw);

                $text = '';
                $buttonId = null;
                if ($messageData['type'] === 'text') {
                    $text = trim($messageData['text']['body']);
                } elseif ($messageData['type'] === 'interactive') {
                    $text = trim($messageData['interactive']['button_reply']['title'] ?? '');
                    $buttonId = trim($messageData['interactive']['button_reply']['id'] ?? '');
                } elseif ($messageData['type'] === 'audio') {
                    $mediaId = $messageData['audio']['id'] ?? null;
                    if ($mediaId) {
                        $this->sendMetaMessage($from, "🎙️ _Processing voice note, please wait..._");
                        $transcribedText = $this->transcribeAudio($mediaId);
                        if (!empty($transcribedText)) {
                            $this->sendMetaMessage($from, "🗣️ *You said:* \"{$transcribedText}\"");
                            $text = $transcribedText;
                        } else {
                            $this->sendMetaMessage($from, "⚠️ Sorry, I couldn't transcribe that voice note. Please try typing your request.");
                            return response()->json(['status' => 'success']);
                        }
                    }
                }

                if (!empty($text)) {
                    $this->processIncomingWebhookMessage($from, $text, $buttonId);
                }
            }

            return response()->json(['status' => 'success']);
        }
    }

    private function loadTenantSettings(\App\Models\Tenant $tenant, bool $forceRefresh = false): void
    {
        $cacheKey = \App\Support\TenantSettings::settingsCacheKey($tenant);

        if ($forceRefresh) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        $settings = \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function () use ($tenant) {
            $path = storage_path('app/academyhub/tenants/' . $tenant->id . '/settings.json');
            if (! \Illuminate\Support\Facades\File::exists($path)) {
                return [];
            }
            $data = json_decode(\Illuminate\Support\Facades\File::get($path), true);
            return is_array($data) ? $data : [];
        });

        foreach ($settings as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            config(["academyhub.{$key}" => $value]);
            config(["myacademy.{$key}" => $value]);
        }
    }

    private function processIncomingWebhookMessage(string $phone, string $text, ?string $buttonId = null): void
    {
        $textLower = strtolower(trim($text));

        if ($buttonId) {
            if ($buttonId === 'menu_attendance') {
                $textLower = 'attendance';
            } elseif ($buttonId === 'menu_results') {
                $textLower = 'results';
            } elseif ($buttonId === 'menu_fees') {
                $textLower = 'fees';
            }
        }

        // Check if button click is a direct report card request
        if ($buttonId && str_starts_with($buttonId, 'rc_std_')) {
            $parts = explode('|', substr($buttonId, 7));
            $studentId = (int) $parts[0];
            $phoneClean = preg_replace('/\D/', '', $phone);
            if (!empty($phoneClean)) {
                \Illuminate\Support\Facades\Cache::put("whatsapp_active_student_{$phoneClean}", $studentId, now()->addMinutes(30));
            }
            $term = isset($parts[1]) ? (int) $parts[1] : 1;
            $session = isset($parts[2]) ? $parts[2] : '';

            $studentObj = \App\Models\Student::find($studentId);
            if ($studentObj) {
                // Resolve tenant context
                $tenant = \App\Models\Tenant::find($studentObj->tenant_id);
                if ($tenant) {
                    // Check if tenant is suspended/inactive or subscription expired
                    $tenantActive = ($tenant->status === 'active') && (!$tenant->expires_at || !$tenant->expires_at->isPast());
                    $botActive = $tenant->activeMarketplaceComponents()->where('slug', 'whatsapp-bot')->exists();

                    if (!$tenantActive || !$botActive) {
                        $reason = !$tenantActive ? "suspended or expired" : "deactivated or uninstalled";
                        $this->sendMetaMessage($phone, "❌ *Service Unavailable:* This school's WhatsApp bot integration is currently {$reason}. Please contact the school administration.");
                        return;
                    }

                    app()->instance('currentTenant', $tenant);
                    $this->loadTenantSettings($tenant);
                }

                $apiKey = config('services.whatsapp.api_key');
                $reportUrl = route('whatsapp.report-card', [
                    'studentId' => $studentObj->id,
                    'key'       => $apiKey,
                    'term'      => $term,
                    'session'   => $session,
                    't'         => time()
                ]);

                $filename = "report-card-{$studentObj->first_name}-{$studentObj->last_name}-Term{$term}-" . str_replace('/', '-', $session) . ".pdf";
                
                $this->sendMetaMessage($phone, "⏳ Compiling and generating PDF report card. Please wait a moment...");
                $this->sendMetaMessage($phone, "Official Term {$term} ({$session}) Report Card for *{$studentObj->full_name}*", $reportUrl, $filename);
                return;
            }
        }

        // 1. Check Login State Machine in Cache
        $cacheKey = "whatsapp_state_{$phone}";
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            $stateObj = \Illuminate\Support\Facades\Cache::get($cacheKey);

            if ($textLower === 'cancel') {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
                $this->sendMetaMessage($phone, '🚫 Process cancelled. You can ask me anything else!');
                return;
            }

            // Handle 'back' keyword to go one step back in the login flow
            if ($textLower === 'back') {
                if (isset($stateObj['step'])) {
                    if ($stateObj['step'] === 'LOGIN_IDENTIFIER') {
                        $stateObj['step'] = 'LOGIN_SCHOOL';
                        unset($stateObj['data']['school']);
                        $stateObj['attempts'] = 0;
                        \Illuminate\Support\Facades\Cache::put($cacheKey, $stateObj, now()->addMinutes(20));
                        $this->sendMetaMessage($phone, "↩️ No problem. Please re-enter your *School Code*:\n\n_(Type *cancel* to stop)_");
                    } elseif ($stateObj['step'] === 'LOGIN_PASSWORD') {
                        $stateObj['step'] = 'LOGIN_IDENTIFIER';
                        unset($stateObj['data']['identifier']);
                        $stateObj['attempts'] = 0;
                        \Illuminate\Support\Facades\Cache::put($cacheKey, $stateObj, now()->addMinutes(20));
                        $this->sendMetaMessage($phone, "↩️ Sure. Please re-enter your *login identifier*:\n\n• *Students:* Admission Number (e.g. STU20240001)\n• *Staff/Parents:* Email Address\n\n_(Type *cancel* to stop)_");
                    } else {
                        $this->sendMetaMessage($phone, "You're at the beginning of the process. Type *cancel* to stop, or continue.");
                    }
                    return;
                }
            }

            // Step A: Enter School Code
            if ($stateObj['step'] === 'LOGIN_SCHOOL') {
                $schoolSlug = trim($textLower);
                $tenant = \App\Models\Tenant::where('slug', $schoolSlug)->first();

                if (!$tenant) {
                    $this->sendMetaMessage($phone, "❌ School Code *{$schoolSlug}* not found. Please check the spelling and try again:\n\n_(Type *cancel* to stop)_");
                    return;
                }

                // Check tenant and plugin status immediately before allowing login!
                $tenantActive = ($tenant->status === 'active') && (!$tenant->expires_at || !$tenant->expires_at->isPast());
                $botActive = $tenant->activeMarketplaceComponents()->where('slug', 'whatsapp-bot')->exists();

                if (!$tenantActive || !$botActive) {
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    $reason = !$tenantActive ? "suspended or expired" : "deactivated or uninstalled";
                    $this->sendMetaMessage($phone, "❌ *Service Unavailable:* This school's WhatsApp bot integration is currently {$reason}. Please contact the school administration.");
                    return;
                }

                $stateObj['data']['school'] = $schoolSlug;
                $stateObj['step'] = 'LOGIN_IDENTIFIER';
                $stateObj['attempts'] = 0;
                \Illuminate\Support\Facades\Cache::put($cacheKey, $stateObj, now()->addMinutes(20));

                $this->sendMetaMessage($phone, "Got it. School Code: *{$schoolSlug}*\n\n👤 Now enter your *login identifier*:\n\n• *Students:* Admission Number (e.g. STU20240001)\n• *Staff/Parents:* Email Address\n\n_(Type *back* to change school code, or *cancel* to stop)_");
                return;
            }

            // Step B: Enter Identifier
            if ($stateObj['step'] === 'LOGIN_IDENTIFIER') {
                $stateObj['data']['identifier'] = trim($text);
                $stateObj['step'] = 'LOGIN_PASSWORD';
                $stateObj['attempts'] = 0;
                \Illuminate\Support\Facades\Cache::put($cacheKey, $stateObj, now()->addMinutes(20));

                $this->sendMetaMessage($phone, "🔑 Now enter your *password*:\n\n_(Type *back* to change your identifier, or *cancel* to stop)_");
                return;
            }

            // Step C: Enter Password & Authenticate
            if ($stateObj['step'] === 'LOGIN_PASSWORD') {
                $school = $stateObj['data']['school'];
                $identifier = $stateObj['data']['identifier'];
                $password = trim($text);

                $tenant = \App\Models\Tenant::where('slug', $school)->first();
                if (!$tenant) {
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    $this->sendMetaMessage($phone, "❌ School configuration error. Please start again with *login*.");
                    return;
                }

                // Resolve tenant context
                app()->instance('currentTenant', $tenant);
                $this->loadTenantSettings($tenant);

                // Check student login
                $student = \App\Models\Student::where('admission_number', strtoupper($identifier))
                    ->where('status', 'Active')
                    ->first();

                if ($student) {
                    $valid = false;
                    if ($student->password) {
                        $valid = \Illuminate\Support\Facades\Hash::check($password, $student->password);
                    } else {
                        $suffix = substr($student->admission_number, -4);
                        $expected = strtolower($student->first_name) . $suffix;
                        $valid = $password === $expected;
                    }

                    if (!$valid) {
                        $stateObj['attempts'] = ($stateObj['attempts'] ?? 0) + 1;
                        if ($stateObj['attempts'] >= 3) {
                            \Illuminate\Support\Facades\Cache::forget($cacheKey);
                            $this->sendMetaMessage($phone, "🔒 *Too many failed attempts.* Your session has been reset for security. Type *login* to try again.");
                        } else {
                            $remaining = 3 - $stateObj['attempts'];
                            \Illuminate\Support\Facades\Cache::put($cacheKey, $stateObj, now()->addMinutes(20));
                            $this->sendMetaMessage($phone, "❌ *Incorrect password.* Please try again ({$remaining} attempt" . ($remaining === 1 ? '' : 's') . " left):\n\n_(Type *cancel* to stop or *back* to change your identifier)_");
                        }
                        return;
                    }

                    $user = null;
                    if ($student->user_id) {
                        $user = \App\Models\User::find($student->user_id);
                    }

                    if (!$user) {
                        // Check if a shadow user already exists for this email
                        $email = strtolower($student->admission_number) . '@academyhub.whatsapp';
                        $user = \App\Models\User::where('email', $email)->first();
                        
                        if (!$user) {
                            $user = \App\Models\User::create([
                                'tenant_id' => $student->tenant_id,
                                'name' => $student->full_name,
                                'email' => $email,
                                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                                'role' => 'student',
                                'is_active' => true,
                            ]);
                        }
                        
                        $student->user_id = $user->id;
                        $student->save();
                    }

                    if ($user->whatsapp_phone && $user->whatsapp_phone !== $phone) {
                        \Illuminate\Support\Facades\Cache::forget($cacheKey);
                        $this->sendMetaMessage($phone, "❌ This account is already linked to another WhatsApp number. Please contact your school administrator.");
                        return;
                    }

                    $user->whatsapp_phone = $phone;
                    $user->whatsapp_verified = true;
                    $user->whatsapp_subscribed = true;
                    $user->save();

                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    $this->sendMetaMessage($phone, "✅ *Logged in successfully!*\n\nWelcome, {$student->first_name}! You can now check your timetable, grades, homework, and upcoming events.");
                    return;
                }

                // Check other users
                $user = \App\Models\User::where('email', $identifier)
                    ->whereIn('role', ['admin', 'teacher', 'bursar', 'parent'])
                    ->first();

                if (!$user) {
                    // Go back to identifier step so they can fix it — don't reset the whole session
                    $stateObj['step'] = 'LOGIN_IDENTIFIER';
                    $stateObj['attempts'] = 0;
                    unset($stateObj['data']['identifier']);
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $stateObj, now()->addMinutes(20));
                    $this->sendMetaMessage($phone, "❌ *Account not found.* Please check your email address or admission number and try again:\n\n_(Type *cancel* to stop)_");
                    return;
                }

                if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                    $stateObj['attempts'] = ($stateObj['attempts'] ?? 0) + 1;
                    if ($stateObj['attempts'] >= 3) {
                        \Illuminate\Support\Facades\Cache::forget($cacheKey);
                        $this->sendMetaMessage($phone, "🔒 *Too many failed attempts.* Your session has been reset for security. Type *login* to try again.");
                    } else {
                        $remaining = 3 - $stateObj['attempts'];
                        \Illuminate\Support\Facades\Cache::put($cacheKey, $stateObj, now()->addMinutes(20));
                        $this->sendMetaMessage($phone, "❌ *Incorrect password.* Please try again ({$remaining} attempt" . ($remaining === 1 ? '' : 's') . " left):\n\n_(Type *cancel* to stop or *back* to change your identifier)_");
                    }
                    return;
                }

                if (!$user->is_active) {
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    $this->sendMetaMessage($phone, "❌ *Login failed:* Your account is inactive. Contact the administrator.");
                    return;
                }

                if ($user->whatsapp_phone && $user->whatsapp_phone !== $phone) {
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    $this->sendMetaMessage($phone, "❌ This account is already linked to another WhatsApp number.");
                    return;
                }

                $user->whatsapp_phone = $phone;
                $user->whatsapp_verified = true;
                $user->whatsapp_subscribed = true;
                $user->save();

                \Illuminate\Support\Facades\Cache::forget($cacheKey);
                $roleName = ucfirst($user->role);
                $this->sendMetaMessage($phone, "✅ *Logged in successfully!*\n\nWelcome, {$user->name} ({$roleName})! You can now check school updates, classes, and student records.");
                return;
            }

            // Step D: Broadcast Target
            if ($stateObj['step'] === 'BROADCAST_TARGET') {
                $target = strtolower(trim($text));
                if (!in_array($target, ['parents', 'parent', 'staff', 'all'])) {
                    $this->sendMetaMessage($phone, "❌ Invalid target. Please type *Parents*, *Staff*, or *All*:\n\n_(Type *cancel* to stop)_");
                    return;
                }

                $stateObj['data']['target'] = $target;
                $stateObj['step'] = 'BROADCAST_MSG';
                \Illuminate\Support\Facades\Cache::put($cacheKey, $stateObj, now()->addMinutes(20));

                $this->sendMetaMessage($phone, "Got it. Target audience: *{$text}*.\n\nNow, please send the *Broadcast Message*.");
                return;
            }

            // Step E: Broadcast Message Delivery
            if ($stateObj['step'] === 'BROADCAST_MSG') {
                $target = $stateObj['data']['target'];
                $broadcastMsg = trim($text);

                $user = \App\Models\User::where('whatsapp_phone', $phone)
                    ->where('whatsapp_verified', true)
                    ->first();

                if (!$user) {
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    $this->sendMetaMessage($phone, "❌ Session error. Please try again.");
                    return;
                }

                $query = \App\Models\User::where('whatsapp_subscribed', true)
                    ->whereNotNull('whatsapp_phone')
                    ->where('id', '!=', $user->id);

                if ($target === 'parents' || $target === 'parent') {
                    $query->where('role', 'parent');
                } elseif ($target === 'staff') {
                    $query->whereIn('role', ['teacher', 'bursar', 'admin']);
                }

                $phones = $query->pluck('whatsapp_phone')->toArray();
                \Illuminate\Support\Facades\Cache::forget($cacheKey);

                if (empty($phones)) {
                    $this->sendMetaMessage($phone, "⚠️ No subscribed users found in target audience: *{$target}*.");
                    return;
                }

                $this->sendMetaMessage($phone, "⏳ Sending broadcast to " . count($phones) . " users...");

                $successCount = 0;
                foreach ($phones as $p) {
                    $sent = $this->sendMetaMessage($p, "📢 *Broadcast from Admin*\n\n" . $broadcastMsg);
                    if ($sent) {
                        $successCount++;
                    }
                }

                $this->sendMetaMessage($phone, "✅ Broadcast successfully delivered to {$successCount} out of " . count($phones) . " users!");
                return;
            }
        }

        // 2. Entry Commands for Unregistered users
        if ($textLower === 'login') {
            \Illuminate\Support\Facades\Cache::put($cacheKey, ['step' => 'LOGIN_SCHOOL', 'data' => [], 'attempts' => 0], now()->addMinutes(20));
            $this->sendMetaMessage($phone, "👋 *Welcome to HubGenie!*\n\nPlease enter your *School Code* first (e.g. `demo`, `yis`):\n\n_(Type *cancel* anytime to stop)_");
            return;
        }

        // 3. Find Logged In User
        $user = \App\Models\User::where('whatsapp_phone', $phone)
            ->where('whatsapp_verified', true)
            ->first();

        if ($user) {
            // Resolve Dynamic Tenant Config & Check Active Status / Plugin Activation first!
            $tenant = $user->tenant;
            if ($tenant) {
                // Check if tenant is suspended/inactive or subscription expired
                $tenantActive = ($tenant->status === 'active') && (!$tenant->expires_at || !$tenant->expires_at->isPast());
                // Check if whatsapp-bot component is active
                $botActive = $tenant->activeMarketplaceComponents()->where('slug', 'whatsapp-bot')->exists();

                if (!$tenantActive || !$botActive) {
                    // Deactivate user session on WhatsApp to prevent further loops
                    $user->whatsapp_phone = null;
                    $user->whatsapp_verified = false;
                    $user->whatsapp_subscribed = false;
                    $user->save();

                    \Illuminate\Support\Facades\Cache::forget($cacheKey);

                    $reason = !$tenantActive ? "suspended or expired" : "uninstalled or deactivated";
                    $this->sendMetaMessage($phone, "⚠️ *Service Unavailable*\n\nThis school's WhatsApp integration has been {$reason}. All automated interactions have been stopped. Please contact your school administrator.");
                    return;
                }

                app()->instance('currentTenant', $tenant);
                $this->loadTenantSettings($tenant);
            }

            // Verify that the User account itself is active
            if (!$user->is_active) {
                $user->whatsapp_phone = null;
                $user->whatsapp_verified = false;
                $user->whatsapp_subscribed = false;
                $user->save();

                \Illuminate\Support\Facades\Cache::forget($cacheKey);
                $this->sendMetaMessage($phone, "⚠️ *Account Suspended*\n\nYour school account is currently inactive. WhatsApp access has been disconnected. Please contact the school administrator.");
                return;
            }

            // If the user is a student, verify that their student profile is active
            if ($user->role === 'student') {
                $student = \App\Models\Student::where('user_id', $user->id)->first();
                if (!$student || $student->status !== 'Active') {
                    $user->whatsapp_phone = null;
                    $user->whatsapp_verified = false;
                    $user->whatsapp_subscribed = false;
                    $user->save();

                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    $this->sendMetaMessage($phone, "⚠️ *Access Revoked*\n\nYour student profile is no longer active. WhatsApp access has been disconnected.");
                    return;
                }
            }
        }

        if (!$user) {
            if (in_array($textLower, ['hi', 'hello', 'hey', 'menu', 'start', 'help'])) {
                $this->sendMetaMessage($phone, "👋 *Welcome to HubGenie!*\n\nI don't recognize your number yet.\n\nType *login* to connect your account using the credentials you use on the school website.");
                return;
            }
            $this->sendMetaMessage($phone, "You are not logged in. Type *login* to connect your account.");
            return;
        }

        $userId = $user->id;
        $userRole = $user->role;

        // 4.3 Handle Event RSVPs from Buttons
        if ($buttonId && str_starts_with($buttonId, 'rsvp_evt_')) {
            $parts = explode('|', substr($buttonId, 9));
            $eventId = (int) $parts[0];
            $status = isset($parts[1]) ? $parts[1] : 'yes';

            $event = \App\Models\SchoolEvent::find($eventId);
            if ($event) {
                \App\Models\EventRsvp::updateOrCreate([
                    'tenant_id' => $event->tenant_id,
                    'event_id'  => $event->id,
                    'user_id'   => $userId,
                ], [
                    'status'    => $status,
                ]);

                $statusText = $status === 'yes' ? 'Attending 🙋' : 'Not Attending 🙅';
                $reply = "✅ *RSVP Recorded!*\n\nThank you, *{$user->name}*. Your response for *{$event->title}* has been set to: *{$statusText}*.\n\nWe have updated the guest list on the school servers.";
                $this->sendMetaMessage($phone, $reply);
                return;
            }
        }

        // 4.6 Handle School Stats/Summary keyword for Admins
        if (in_array($textLower, ['stats', 'summary']) && in_array($userRole, ['admin', 'superadmin'])) {
            $activeStudents = \App\Models\Student::where('status', 'Active')->count();
            $activeStaff = \App\Models\User::where('is_active', true)
                ->whereIn('role', ['teacher', 'bursar', 'admin'])
                ->count();

            $todaySheetIds = \App\Models\AttendanceSheet::whereDate('date', today())->pluck('id')->toArray();
            $todayMarksCount = 0;
            $presentMarksCount = 0;
            if (!empty($todaySheetIds)) {
                $todayMarks = \App\Models\AttendanceMark::whereIn('sheet_id', $todaySheetIds)->get();
                $todayMarksCount = $todayMarks->count();
                $presentMarksCount = $todayMarks->filter(fn($m) => in_array($m->status, ['P', 'L']))->count();
            }

            if ($todayMarksCount > 0) {
                $rate = round(($presentMarksCount / $todayMarksCount) * 100);
                $attendanceRateText = "*{$rate}%*";
            } else {
                $attendanceRateText = "_No sheets recorded today_";
            }

            $activeTermNumber = \App\Models\AcademicTerm::activeTermNumber();
            $activeSessionName = \App\Models\AcademicTerm::activeSessionName() ?: date('Y') . '/' . (date('Y') + 1);
            $currency = config('academyhub.currency_symbol', '₦');

            $totalFeesCollected = (float) \App\Models\Transaction::where('type', 'Income')
                ->where('term', $activeTermNumber)
                ->where('session', $activeSessionName)
                ->where('is_void', false)
                ->sum('amount_paid');

            $todayDate = today()->format('d M Y');

            $reply = "📊 *SCHOOL DASHBOARD SUMMARY*\n\n" .
                     "💼 *Overview:*\n" .
                     "• Active Students: *{$activeStudents}*\n" .
                     "• Active Staff: *{$activeStaff}*\n\n" .
                     "📅 *Today's Attendance ({$todayDate}):*\n" .
                     "• Rate: {$attendanceRateText}" . ($todayMarksCount > 0 ? " ({$presentMarksCount}/{$todayMarksCount} present)" : "") . "\n\n" .
                     "💰 *Financials (Term {$activeTermNumber} - {$activeSessionName}):*\n" .
                     "• Total Fees Collected: *{$currency}" . number_format($totalFeesCollected, 2) . "*";

            $this->sendMetaMessage($phone, $reply);
            return;
        }

        // 5. Handle Logout
        if ($textLower === 'logout') {
            $user->whatsapp_phone = null;
            $user->whatsapp_verified = false;
            $user->whatsapp_subscribed = false;
            $user->save();

            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            $this->sendMetaMessage($phone, "🔒 *Logged out successfully!*\n\nYour account has been disconnected from this WhatsApp number. You can type *login* anytime to connect again.");
            return;
        }

        // 6. Handle Subscription Commands
        if ($textLower === 'subscribe') {
            $user->whatsapp_subscribed = true;
            $user->save();
            $this->sendMetaMessage($phone, "✅ You are now subscribed to automated push notifications.");
            return;
        }

        if ($textLower === 'unsubscribe') {
            $user->whatsapp_subscribed = false;
            $user->save();
            $this->sendMetaMessage($phone, "✅ You have successfully unsubscribed from automated notifications.");
            return;
        }

        // 7. Handle Contact
        if ($textLower === 'contact') {
            $schoolPhone = config('academyhub.school_phone') ?: 'N/A';
            $schoolEmail = config('academyhub.school_email') ?: 'N/A';
            $this->sendMetaMessage($phone, "☎️ School Contact\nPhone: {$schoolPhone}\nEmail: {$schoolEmail}");
            return;
        }

        // 7.5. Handle Admin Broadcast Entry Command
        if ($textLower === 'broadcast' && in_array($userRole, ['admin', 'superadmin'])) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, ['step' => 'BROADCAST_TARGET', 'data' => []], now()->addMinutes(20));
            $this->sendMetaMessage($phone, "📢 Let's send a broadcast!\n\nWho should receive this? (e.g. *Parents*, *Staff*, or *All*)\n\n_(Type *cancel* anytime to stop)_");
            return;
        }

        // 9. Standard Menu / Help
        if ($textLower === 'menu' || $textLower === 'help') {
            $schoolName = config('academyhub.school_name') ?: 'AcademyHub';
            if ($userRole === 'parent') {
                $messageText = "👋 Welcome to the *{$schoolName}* WhatsApp Portal!\n\nPlease select one of the quick options below, or chat with me naturally:";
                $buttons = [
                    ['id' => 'menu_attendance', 'title' => '📅 Attendance'],
                    ['id' => 'menu_results', 'title' => '📊 Term Results'],
                    ['id' => 'menu_fees', 'title' => '💰 Fees & Invoices'],
                ];
                $this->sendMetaMessage($phone, $messageText, null, null, $buttons);
                return;
            }
            $reply = "====================================\n" .
                     "       🎒  *{$schoolName}*      \n" .
                     "====================================\n\n" .
                     "🤖 *HubGenie Virtual Assistant*\n\n" .
                     "You can chat with me naturally about schedules, events, grades, or use these keywords:\n\n";

            if ($userRole === 'parent') {
                $reply .= "📅 *attendance* - View today's child attendance\n" .
                          "📊 *results*    - View latest term scores\n" .
                          "💰 *fees*       - View outstanding tuition balance & checkout links\n" .
                          "📝 *homework*   - View active child assignments & status\n" .
                          "📅 *timetable*  - View today's class schedule for children\n" .
                          "📄 *report*     - Download Official PDF Report Card for any term\n" .
                          "☎️ *contact*     - Get school contact and details\n" .
                          "🔔 *subscribe*   - Opt-in to automatic notifications\n" .
                          "🔕 *unsubscribe* - Opt-out of notifications\n" .
                          "🔒 *logout*      - Disconnect your account\n\n" .
                          "------------------------------------\n" .
                          "💡 _E.g., try asking: \"What homework does Abdullahi Bala have due?\"_";
            } elseif (in_array($userRole, ['admin', 'superadmin'])) {
                $reply .= "📢 *broadcast*   - Send announcement to Parents/Staff\n" .
                          "🔔 *subscribe*   - Opt-in to automated alerts\n" .
                          "🔕 *unsubscribe* - Opt-out of alerts\n" .
                          "☎️ *contact*     - Get school contact details\n" .
                          "🔒 *logout*      - Disconnect your admin session\n\n" .
                          "------------------------------------\n" .
                          "💡 _E.g., try asking: \"How many students are active in the school?\"_";
            } else {
                $reply .= "🔔 *subscribe*   - Opt-in to automated alerts\n" .
                          "🔕 *unsubscribe* - Opt-out of alerts\n" .
                          "☎️ *contact*     - Get school contact details\n" .
                          "🔒 *logout*      - Disconnect your session\n\n" .
                          "------------------------------------\n" .
                          "💡 _E.g., try asking: \"What classes do I teach today?\"_";
            }
            $this->sendMetaMessage($phone, $reply);
            return;
        }

        // 10. Natural Language AI Fallback
        if ($userRole === 'parent') {
            $context = $this->buildParentContext($user);
            $roleLabel = 'parent';
        } elseif ($userRole === 'student') {
            $context = $this->buildStudentContext($user);
            $roleLabel = 'student';
        } else {
            $context = $this->buildStaffContext($user, $text);
            $roleLabel = "staff member ({$user->role})";
        }

        $contextJson = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $prompt = "You are HubGenie, the official school coordinator assistant for the AcademyHub school management system.\n" .
                  "You are chatting with a {$roleLabel} on WhatsApp. Here is the verified, real-time context of the logged-in user, their school, academic calendar, and system statistics:\n" .
                  "{$contextJson}\n\n" .
                  "User's Question:\n" .
                  "\"{$text}\"\n\n" .
                  "STRICT RULES & CONSTRAINTS:\n" .
                  "1. CONVERSATIONAL TONE: Write in a natural, direct, human voice. Avoid any robotic or clinical language. Speak as if you are a helpful school coordinator checking the school registry.\n" .
                  "2. FORBIDDEN WORDS: NEVER use words like 'AI', 'bot', 'virtual assistant', 'context', 'database', 'system', 'limitations', or 'model'. Do not refer to yourself as a robot, software, or computer program.\n" .
                  "3. NO FLUFF: Answer the user's question directly in 1-3 sentences maximum. Remove all conversational fluff, warnings, repetitive greetings, and friendly closing remarks (e.g., do not say 'I am here to help' or 'Let me know if you need anything else' in every message).\n" .
                  "4. GREETINGS: Do NOT greet the user (e.g., 'Hello', 'Hi') unless they specifically greeted you in their question. Jump directly to the answer.\n" .
                  "5. UNKNOWN INFORMATION: If the user asks a question about something not in the provided records, do NOT say 'I don't have access to this information as an AI'. Instead, say naturally: 'I don't have that in our school records at the moment. Please contact the school administration office directly.'\n" .
                  "6. STUDENT NAMES: Always fetch and explicitly state the student(s) full name(s) (e.g. 'Abdullahi Bala') when replying to questions about children, homework, attendance, or scores. Never refer to them generically as 'your child' or 'your student' if their name is available in the context.\n" .
                  "7. SECURITY & SENSITIVE DATA: Never reveal, discuss, or query sensitive information (e.g. passwords, password hashes, login tokens, secret keys, API credentials, or internal database schemas). Under no circumstances should you retrieve or expose password details.\n" .
                  "8. PASSWORD PROTECTION: If the user asks about passwords, credentials, resetting their password, or secure keys, you MUST immediately reject it and say exactly: '🔒 For security, passwords and login credentials cannot be accessed, modified, or discussed via WhatsApp.'\n" .
                  "9. WHATSAPP FORMATTING RULES:\n" .
                  "   - BOLD: Format bold text using single asterisks (e.g., *this is bold*). NEVER use double asterisks (**).\n" .
                  "   - LISTS: Use the literal bullet character • followed by a space at the start of list items. Write each list item on a new line. Do not use markdown bullet styles like '-' or '*'.\n" .
                  "   - HEADINGS: Do NOT use markdown `#`, `##` or `###`. Use simple bold capital letters for headers (e.g. *ATTENDANCE STATUS*).\n" .
                  "   - LINE BREAKS: Use clean single or double line breaks between paragraphs/points to make the text readable on a mobile screen.\n" .
                  "10. DYNAMIC REPORT CARDS (PDF Delivery):\n" .
                  "   - If a parent or user asks to download, receive, view, or get a PDF report card for a student, you MUST first verify if the results for the requested term and session are officially published (by checking if a matching term and session exists in the student's `published_results` list).\n" .
                  "   - If the results are NOT officially published, you MUST NOT generate any '[SEND_PDF]' tag or '[AMBIGUOUS_REPORT_CARD]' tag. Instead, politely inform the user that the report card for that term/session has not been officially published yet by the school administration.\n" .
                  "   - If they are published, check if they specified which student child they meant (if they have multiple children listed under students in the context).\n" .
                  "   - If they have multiple children, and they did NOT explicitly name the child they want the report card for, you MUST output this exact hidden tag: '[AMBIGUOUS_REPORT_CARD: <term_number>|<academic_session>]' and politely ask them to choose.\n" .
                  "   - If the student is clearly identified (or they only have one child), you MUST generate and append this exact hidden tag: '[SEND_PDF: <student_id>|<term_number>|<academic_session>]'\n" .
                  "   - Resolve the <student_id> from the students in the context.\n" .
                  "   - Resolve the <term_number> strictly as a digit: 1, 2, or 3. If a previous term is requested (e.g. 'first term', 'term 2'), resolve it to the correct digit. If no term is specified, default to the current active term from the academic_system metadata.\n" .
                  "   - Resolve the <academic_session> in YYYY/YYYY format (e.g. '2026/2027'). If a previous session is mentioned (e.g. 'last year', '2025/2026'), resolve it. If no session is specified, default to the active session from the academic_system metadata.\n" .
                  "   - You can output multiple SEND_PDF tags if they request report cards for multiple children or multiple terms in a single prompt.\n" .
                  "11. INTENT DETECTION & SUPPORT TICKETS: If the user indicates they want to report an issue, log a complaint, offer feedback, report missing grades/attendance, or request a call back from the school administration, you MUST append a hidden tag at the very end of your response: '[SUPPORT_TICKET_DETECTED: <message>]' where <message> is a clear, concise 1-sentence summary of the user's actual problem or concern. Do not include this tag for standard information questions (e.g. asking for grades, schedules, or events).\n" .
                  "12. HANDLING STUDENT RESULTS: If asked about student results, academic performance, report cards, or scores: Check if the child name or admission number is specified in the question. If not, and there are multiple children listed in the context, ask the user to specify which student they are asking about. If the student is identified, verify if the results for the requested term and session are officially published (existence of a record matching that term and session in the student's `published_results` list). If the results are NOT published, you MUST politely inform the user that the academic results for that term/session have not been officially published yet by the school administration, instead of saying you don't have access to this information. If the results ARE published, list the scores from the `recent_scores` data matching the requested term/session, always listing the subject name and score (e.g., Mathematics: 85/100).\n" .
                  "13. ACTIVE SESSION STUDENT: If 'active_session_student_id' is set in the context (not null), this indicates the student the parent has been actively chatting about or selected recently. Prioritize this student in your answers unless the user names a different child. Additionally, if your answer resolves to or discusses a specific single student from the context, you MUST append a hidden tag at the very end of your response: '[ACTIVE_STUDENT_SELECTED: <student_id>]' where <student_id> is that student's ID from the context. Do not append this tag if you are talking about multiple children or general school information.\n" .
                  "14. BROADCASTS & ANNOUNCEMENTS: If the user is an admin or superadmin and explicitly requests to send a broadcast, post a notice, publish an announcement, or notify a group of users (students, parents, staff, or everyone) about a message:\n" .
                  "    - First check if the user has provided the actual content/body text of the broadcast/announcement. If they have NOT provided the message content yet, do NOT generate any '[CREATE_ANNOUNCEMENT]' tag. Instead, politely ask them what the message content is first.\n" .
                  "    - Only when you have BOTH the target audience and the actual message content/body text, append a hidden tag at the very end of your response: '[CREATE_ANNOUNCEMENT: <audience>|<title>|<body_text>]' where:\n" .
                  "        - <audience> MUST be one of: 'all', 'student', 'parent', 'staff'.\n" .
                  "        - <title> is a short (2-6 words) title for the announcement.\n" .
                  "        - <body_text> is the message content to broadcast.\n" .
                  "    - Politely confirm in your conversational response that you have published the announcement to the school portal.\n" .
                  "15. PROACTIVE FEE ALERTS & GATEWAY RESILIENCY: When a parent asks about fees or outstanding balances and a student has an outstanding_balance greater than 0: Check if 'online_payment_enabled' is true in the child's `tuition_fees` context. If it is true, add a polite, warm urgency note encouraging online payment and mention that they can pay using the checkout link provided. If it is false or disabled, you MUST NOT offer or link to any online payment checkout; instead, politely state the outstanding balance, tell them that online payment setup is currently pending review by the school administration, and advise them to pay manually at the school finance office. If the balance is 0, congratulate them warmly. Never be harsh or threatening — use empathetic, supportive language only.\n" .
                  "16. HOMEWORK DEADLINE AWARENESS: When a parent or student asks about homework, check the due_date of each assignment against today's date. If any assignment's due date is today, flag it with '⚠️ *DUE TODAY*'. If the due date has already passed (overdue), flag it with '🔴 *OVERDUE*'. Never treat overdue assignments the same as regular pending ones.\n" .
                  "17. TYPO & LANGUAGE TOLERANCE: Users may send informal, broken English, Pidgin English, or messages with spelling mistakes (e.g. 'wats my pikin score', 'I wan see fee', 'wetin be attendance'). You MUST interpret the intent correctly, respond in standard English, and never ask the user to rephrase unless the message is completely ambiguous even after reasonable interpretation.\n" .
                  "18. ATTENDANCE PATTERN INSIGHT: When asked about attendance (or follow-up questions like 'check again' or 'check my child\'s attendance for today'): Check each student's `attendance_today` and `recent_attendance_history` in the context. Always explicitly state the student's full name, the date (e.g. Monday, Aug 3), and plain language status: '✅ Present', '⚠️ Late', '❌ Absent', or '⏳ Not marked yet today'. If a parent says 'check again' or asks for re-checking, reassure them naturally that you have re-verified today's class roll call, state the current recorded status clearly, and suggest contacting the school office if the child is actually in school." .
                  "19. STAFF ROLE SENSITIVITY: When responding to a teacher, focus answers on their teaching schedule, the classes they manage, and student academic data relevant to them. When responding to a bursar or admin, include financial summaries where relevant. Do not show sensitive financial data to teachers. Do not show academic scores to bursars unless they specifically ask. Always respect the role boundary defined in the context.\n" .
                  "20. AVOID REPETITION IN MULTI-CHILD RESPONSES: If a parent has multiple children and the answer is the same for all of them (e.g. 'No timetable today', 'All fees paid'), do NOT repeat the same line for each child separately. Instead, summarize once naturally (e.g. 'None of your children have classes today' or 'All your children's fees are fully paid').\n" .
                  "21. SMART CLARIFICATION (NOT INTERROGATION): If you genuinely cannot determine the user's intent from their message, ask exactly ONE short, clear clarifying question. Never ask multiple questions at once. Identify the single most critical missing piece of information and ask only that.\n" .
                  "22. UPCOMING EVENTS AWARENESS: If the context contains upcoming school events within the next 7 days and the user sends a general greeting or asks 'what's new', 'anything happening', or similar, proactively mention the upcoming event(s) as a helpful, natural heads-up — without being explicitly asked.\n" .
                  "23. MULTI-INTENT HANDLING: If the user asks about multiple topics in one message (e.g. 'show me attendance and fees'), address ALL parts in a single structured reply. Use bold headings to separate each topic. Do not ignore any part of a multi-part question.\n" .
                  "24. EMOTIONAL INTELLIGENCE: If the user's message expresses frustration, worry, or distress (e.g. 'I am worried about my child', 'this is not fair', 'nobody is helping me'), acknowledge their feeling briefly with one empathetic sentence FIRST, then provide the factual answer. Never jump straight into data when the user is clearly emotional or upset.\n" .
                  "25. TIMETABLE WEEKEND HANDLING: If a parent or student asks about today's timetable and today is Saturday or Sunday, do NOT show an empty schedule or a blank list. Instead, respond naturally: 'It's the weekend — no classes are scheduled today. School resumes on Monday.'\n" .
                  "26. SCORE SUMMARY WITH GRADE INSIGHT: When listing a student's academic scores, after showing all the scores, calculate and state the student's overall average percentage and attach a one-word performance label: *Excellent* (80%+), *Good* (65–79%), *Average* (50–64%), or *Needs Improvement* (below 50%). Keep the tone encouraging and constructive, never discouraging.";

        $phoneClean = preg_replace('/\D/', '', $user->whatsapp_phone ?: '');
        $historyKey = "whatsapp_chat_history_{$phoneClean}";
        $history = \Illuminate\Support\Facades\Cache::get($historyKey, []);

        $answer = $this->tryGroqAPI($prompt, $history);

        if ($answer) {
            // Parse [ACTIVE_STUDENT_SELECTED: student_id] tags
            if (preg_match('/\[ACTIVE_STUDENT_SELECTED:\s*(\d+)\]/i', $answer, $matches)) {
                $selStudentId = (int) $matches[1];
                $phone = preg_replace('/\D/', '', $user->whatsapp_phone ?: '');
                if (!empty($phone)) {
                    \Illuminate\Support\Facades\Cache::put("whatsapp_active_student_{$phone}", $selStudentId, now()->addMinutes(30));
                }
                $answer = trim(preg_replace('/\[ACTIVE_STUDENT_SELECTED:\s*.*?\]/i', '', $answer));
            }

            $ticketDetected = false;
            $ticketMessage = '';

            if (preg_match('/\[SUPPORT_TICKET_DETECTED:\s*(.*?)\]/i', $answer, $matches)) {
                $ticketDetected = true;
                $ticketMessage = trim($matches[1]);
                $answer = trim(preg_replace('/\[SUPPORT_TICKET_DETECTED:\s*.*?\]/i', '', $answer));
            }

            if ($ticketDetected && !empty($ticketMessage)) {
                $ticket = \App\Models\SupportTicket::create([
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'phone' => $user->whatsapp_phone,
                    'message' => $ticketMessage,
                    'status' => 'open',
                    'channel' => 'whatsapp',
                ]);

                // Trigger superadmin notification
                if ($user->tenant) {
                    \App\Models\SuperadminNotification::notifySupportTicket($user->tenant, $ticket);
                }
                $answer .= "\n\n🔒 I have successfully recorded your support request in the school database. A school administrator has been notified and will contact you shortly.";

                // Real-time WhatsApp alert to school admins
                $admins = \App\Models\User::where('tenant_id', $user->tenant_id)
                    ->whereIn('role', ['admin', 'superadmin'])
                    ->whereNotNull('whatsapp_phone')
                    ->where('whatsapp_verified', true)
                    ->get();

                foreach ($admins as $admin) {
                    $adminAlert = "🔔 *New Support Ticket [ID: {$ticket->id}]*\n\n" .
                                  "👤 *Parent:* {$user->name}\n" .
                                  "☎️ *Phone:* {$user->whatsapp_phone}\n" .
                                  "📝 *Issue:* {$ticketMessage}\n\n" .
                                  "💡 _Reply to this user to assist them._";
                    $this->sendMetaMessage($admin->whatsapp_phone, $adminAlert);
                }
            }

            // Parse [CREATE_ANNOUNCEMENT: audience|title|body] tags
            if (preg_match('/\[CREATE_ANNOUNCEMENT:\s*(.*?)\|(.*?)\|(.*?)\]/is', $answer, $matches)) {
                $annAudience = trim($matches[1]);
                $annTitle = trim($matches[2]);
                $annBody = trim($matches[3]);

                if (in_array($annAudience, ['all', 'student', 'parent', 'staff']) && !empty($annBody)) {
                    $this->createAnnouncement($annAudience, $annTitle, $annBody, $user);
                }

                $answer = trim(preg_replace('/\[CREATE_ANNOUNCEMENT:\s*.*?\]/is', '', $answer));
            }

            // Parse [AMBIGUOUS_REPORT_CARD: term|session] tags
            $ambiguousDetected = false;
            $ambiguousTerm = null;
            $ambiguousSession = null;
            if (preg_match('/\[AMBIGUOUS_REPORT_CARD:\s*([^\]|]+)\|([^\]|]+)\]/i', $answer, $matches)) {
                $ambiguousDetected = true;
                $ambiguousTerm = (int) trim($matches[1]);
                $ambiguousSession = trim($matches[2]);
                $answer = trim(preg_replace('/\[AMBIGUOUS_REPORT_CARD:\s*.*?\]/i', '', $answer));
            }

            if ($ambiguousDetected) {
                $buttons = [];
                foreach ($user->students as $s) {
                    $buttons[] = [
                        'id'    => "rc_std_{$s->id}|{$ambiguousTerm}|{$ambiguousSession}",
                        'title' => $s->first_name . ' ' . substr($s->last_name, 0, 1) . '.'
                    ];
                }

                // Save to history before early return
                $history[] = ['role' => 'user', 'content' => $text];
                $history[] = ['role' => 'assistant', 'content' => $answer];
                if (count($history) > 10) {
                    $history = array_slice($history, -10);
                }
                \Illuminate\Support\Facades\Cache::put($historyKey, $history, now()->addMinutes(30));

                // Send body text first with child quick-reply buttons (up to 3 is supported by Meta)
                $this->sendMetaMessage($phone, $answer, null, null, $buttons);
                return;
            }

            // Parse [SEND_PDF: student_id|term|session] tags
            $pdfRequests = [];
            if (preg_match_all('/\[SEND_PDF:\s*([^\]|]+)\|([^\]|]+)\|([^\]|]+)\]/i', $answer, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $pdfStudentId = trim($match[1]);
                    $pdfTerm = (int) trim($match[2]);
                    $pdfSession = trim($match[3]);
                    
                    $studentObj = \App\Models\Student::find($pdfStudentId);
                    if ($studentObj) {
                        $pdfRequests[] = [
                            'student' => $studentObj,
                            'term'    => $pdfTerm,
                            'session' => $pdfSession,
                        ];
                    }
                }
                // Strip all SEND_PDF tags from final response text
                $answer = trim(preg_replace('/\[SEND_PDF:\s*.*?\]/i', '', $answer));
            }

            // Save to history before text response
            $history[] = ['role' => 'user', 'content' => $text];
            $history[] = ['role' => 'assistant', 'content' => $answer];
            if (count($history) > 10) {
                $history = array_slice($history, -10);
            }
            \Illuminate\Support\Facades\Cache::put($historyKey, $history, now()->addMinutes(30));

            // Send the text answer first
            if (!empty($answer)) {
                $this->sendMetaMessage($phone, $answer);
            }

            // Natively dispatch each requested PDF report card
            if (!empty($pdfRequests)) {
                $apiKey = config('services.whatsapp.api_key');
                foreach ($pdfRequests as $req) {
                    $studentObj = $req['student'];
                    $pdfTerm = $req['term'];
                    $pdfSession = $req['session'];

                    $reportUrl = route('whatsapp.report-card', [
                        'studentId' => $studentObj->id,
                        'key'       => $apiKey,
                        'term'      => $pdfTerm,
                        'session'   => $pdfSession,
                        't'         => time()
                    ]);

                    $filename = "report-card-{$studentObj->first_name}-{$studentObj->last_name}-Term{$pdfTerm}-" . str_replace('/', '-', $pdfSession) . ".pdf";
                    
                    $this->sendMetaMessage($phone, "Official Term {$pdfTerm} ({$pdfSession}) Report Card for *{$studentObj->full_name}*", $reportUrl, $filename);
                }
            }
        } else {
            $this->sendMetaMessage($phone, "🤷 I'm sorry, I'm currently having trouble connecting to the school servers. Please try again in a few moments.");
        }
    }

    private function createAnnouncement(string $audience, string $title, string $body, \App\Models\User $user): void
    {
        $announcement = \App\Models\Announcement::create([
            'title' => $title,
            'body' => $body,
            'audience' => $audience,
            'published_at' => now(),
            'created_by' => $user->id,
        ]);

        // 1. Notify parents/staff in-app if targeted
        $query = \App\Models\User::query()->where('tenant_id', $user->tenant_id)->where('is_active', true);

        if ($audience === 'staff') {
            $query->whereIn('role', ['admin', 'teacher', 'bursar']);
        } elseif ($audience !== 'all' && $audience !== 'student') {
            $query->where('role', $audience);
        }

        if ($audience !== 'student') {
            $users = $query->get(['id']);
            foreach ($users as $u) {
                \App\Models\InAppNotification::create([
                    'user_id' => $u->id,
                    'title' => 'New announcement: ' . $title,
                    'body' => $body,
                    'link' => '/announcements',
                ]);
            }
        }

        // 2. Notify students in-app if targeted (student or all)
        if ($audience === 'student' || $audience === 'all') {
            $students = \App\Models\Student::where('tenant_id', $user->tenant_id)->get(['id']);
            foreach ($students as $s) {
                \App\Models\StudentNotification::create([
                    'tenant_id' => $user->tenant_id,
                    'student_id' => $s->id,
                    'title' => 'New Announcement: ' . $title,
                    'body' => $body,
                    'type' => 'general',
                    'link' => null,
                ]);
            }
        }
    }

    private function sendMetaMessage(string $toPhone, string $messageText, ?string $mediaUrl = null, ?string $filename = null, ?array $buttons = null): bool
    {
        try {
            $token = config('services.whatsapp.token');
            $phoneNumberId = config('services.whatsapp.phone_number_id');

            if (empty($token) || empty($phoneNumberId)) {
                \Illuminate\Support\Facades\Log::warning('WhatsApp Cloud API: Token or Phone Number ID not configured.');
                return false;
            }

            $url = "https://graph.facebook.com/v19.0/{$phoneNumberId}/messages";

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $toPhone,
            ];

            if ($buttons) {
                $payload['type'] = 'interactive';
                $payload['interactive'] = [
                    'type' => 'button',
                    'body' => [
                        'text' => $messageText
                    ],
                    'action' => [
                        'buttons' => array_map(fn($btn) => [
                            'type' => 'reply',
                            'reply' => [
                                'id' => $btn['id'],
                                'title' => substr($btn['title'], 0, 20)
                            ]
                        ], $buttons)
                    ]
                ];
            } elseif ($mediaUrl) {
                $payload['type'] = 'document';
                $payload['document'] = [
                    'link' => $mediaUrl,
                    'filename' => $filename ?: 'document.pdf',
                    'caption' => $messageText
                ];
            } else {
                $payload['type'] = 'text';
                $payload['text'] = [
                    'preview_url' => false,
                    'body' => $messageText
                ];
            }

            $response = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])
                ->post($url, $payload);

            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::error('WhatsApp Cloud API: Failed to send message', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp Cloud API: Exception during message send', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

