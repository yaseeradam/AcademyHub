<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AcademicTerm;
use App\Models\AttendanceMark;
use App\Models\ClassNoteComment;
use App\Models\Conversation;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Message;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MobileFeatureParityController extends Controller
{
    /* ====================================================================== */
    /*  1. PARENT PORTAL GAP ENDPOINTS                                        */
    /* ====================================================================== */

    /**
     * GET /api/parent/attendance
     * List detailed date-by-date attendance logs for a parent's children.
     */
    public function getAttendance(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'parent') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $studentIds = $user->students()->pluck('students.id');

        $marks = AttendanceMark::whereIn('student_id', $studentIds)
            ->with(['sheet', 'student'])
            ->get()
            ->map(function ($mark) {
                return [
                    'student_id'   => $mark->student_id,
                    'student_name' => $mark->student->full_name,
                    'date'         => $mark->sheet->date,
                    'status'       => $mark->status,
                    'note'         => $mark->note,
                    'term'         => $mark->sheet->term,
                    'session'      => $mark->sheet->session,
                ];
            });

        return response()->json($marks);
    }

    /**
     * GET /api/parent/billing/receipts/{id}
     * Download transaction receipt as a PDF.
     */
    public function getBillingReceipt(Request $request, $id)
    {
        $user = $request->user();
        $transaction = Transaction::findOrFail($id);

        // Verify authorization
        if ($user->role === 'parent') {
            $childIds = $user->students()->pluck('students.id')->toArray();
            if (!in_array($transaction->student_id, $childIds)) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        } elseif ($user->role === 'student') {
            if ($transaction->student_id !== $user->student_id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        } elseif (!in_array($user->role, ['admin', 'bursar'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        abort_unless($transaction->type === 'Income', 404, 'Receipt only available for income transactions.');
        abort_unless($transaction->receipt_number, 404, 'Receipt number not generated yet.');

        $transaction->load('student');

        $pdf = Pdf::loadView('pdf.receipt', [
            'transaction' => $transaction,
        ])->setPaper('a4');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$transaction->receipt_number}.pdf\"",
        ]);
    }

    /**
     * POST /api/parent/whatsapp/toggle
     * Subscribe/unsubscribe parent user to WhatsApp bot alerts.
     */
    public function toggleWhatsApp(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'parent') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user->whatsapp_subscribed = !$user->whatsapp_subscribed;
        $user->save();

        return response()->json([
            'status' => 'success',
            'whatsapp_subscribed' => $user->whatsapp_subscribed,
        ]);
    }

    /* ====================================================================== */
    /*  2. GENERAL CHAT ENDPOINTS (Parent-Teacher, Staff-Admin)               */
    /* ====================================================================== */

    /**
     * GET /api/conversations
     * List all conversations for the authenticated user.
     */
    public function getConversations(Request $request)
    {
        $user = $request->user();

        $pivots = DB::table('conversation_user')
            ->where('user_id', $user->id)
            ->get(['conversation_id', 'last_read_at'])
            ->keyBy('conversation_id');

        $conversations = Conversation::query()
            ->whereIn('id', $pivots->keys())
            ->with([
                'participants:id,name,role,profile_photo',
                'messages' => fn ($q) => $q->latest('id')->limit(1)->with('sender:id,name,profile_photo'),
            ])
            ->get();

        $formatted = $conversations->map(function ($c) use ($user, $pivots) {
            $pivot = $pivots->get($c->id);
            $lastReadAt = $pivot?->last_read_at ? Carbon::parse($pivot->last_read_at) : null;
            $lastMessage = $c->messages->first();
            $lastMessageAt = $lastMessage?->created_at;

            $unread = $lastMessageAt && (!$lastReadAt || $lastMessageAt->gt($lastReadAt));

            $others = $c->participants->where('id', '!=', $user->id)->values();
            $otherUser = $others->first();
            $title = $others->isEmpty() ? 'Conversation' : $otherUser->name;

            return [
                'id' => $c->id,
                'title' => $title,
                'other_user_id' => $otherUser ? (int) $otherUser->id : null,
                'other_user_role' => $otherUser ? $otherUser->role : null,
                'other_user_photo_url' => $otherUser ? $otherUser->profile_photo_url : null,
                'unread' => $unread,
                'last_message' => $lastMessage?->body,
                'last_message_at' => $lastMessageAt ? $lastMessageAt->toIso8601String() : null,
            ];
        })->sortByDesc('last_message_at')->values();

        return response()->json($formatted);
    }

    /**
     * POST /api/conversations
     * Start a new chat conversation with a specific recipient.
     */
    public function startConversation(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
        ]);

        $recipientId = (int) $request->recipient_id;
        if ($recipientId === $user->id) {
            return response()->json(['message' => 'You cannot message yourself.'], 422);
        }

        $existing = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
            ->whereHas('participants', fn ($q) => $q->where('users.id', $recipientId))
            ->whereDoesntHave('participants', fn ($q) => $q->whereNotIn('users.id', [$user->id, $recipientId]))
            ->first();

        if ($existing) {
            return response()->json(['id' => $existing->id]);
        }

        $conversation = DB::transaction(function () use ($user, $recipientId) {
            $c = Conversation::query()->create(['created_by' => $user->id]);
            $c->participants()->attach([$user->id, $recipientId]);
            return $c;
        });

        return response()->json(['id' => $conversation->id], 201);
    }

    /**
     * GET /api/conversations/{id}/messages
     * Retrieve message thread of a conversation.
     */
    public function getMessages(Request $request, $id)
    {
        $user = $request->user();
        $conversation = Conversation::findOrFail($id);

        $isParticipant = DB::table('conversation_user')
            ->where('conversation_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($isParticipant, 403);

        // Mark as read
        DB::table('conversation_user')
            ->where('conversation_id', $id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        $messages = Message::where('conversation_id', $id)
            ->with('sender:id,name,role,profile_photo')
            ->orderBy('id')
            ->get();

        return response()->json($messages);
    }

    /**
     * POST /api/conversations/{id}/messages
     * Post a new message to a conversation.
     */
    public function postMessage(Request $request, $id)
    {
        $user = $request->user();
        $conversation = Conversation::findOrFail($id);

        $isParticipant = DB::table('conversation_user')
            ->where('conversation_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($isParticipant, 403);

        $request->validate([
            'body' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:20480',
        ]);

        $body = trim((string) $request->body);
        $hasFile = $request->hasFile('file');

        if ($body === '' && !$hasFile) {
            return response()->json(['message' => 'Type a message or attach a file.'], 422);
        }

        $filePath = null;
        $fileName = null;
        $fileMime = null;
        $fileSize = null;

        if ($hasFile) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileMime = $file->getMimeType();
            $fileSize = $file->getSize();
            $filePath = $file->store('academyhub/messages/' . $id, 'public');
        }

        $message = DB::transaction(function () use ($id, $user, $body, $filePath, $fileName, $fileMime, $fileSize) {
            $m = Message::create([
                'conversation_id' => $id,
                'sender_id'       => $user->id,
                'body'            => $body,
                'attachment_path' => $filePath,
                'attachment_name' => $fileName,
                'attachment_mime' => $fileMime,
                'attachment_size' => $fileSize,
            ]);

            DB::table('conversation_user')
                ->where('conversation_id', $id)
                ->where('user_id', $user->id)
                ->update(['last_read_at' => now()]);

            return $m;
        });

        return response()->json($message, 201);
    }

    /* ====================================================================== */
    /*  3. STUDENT NOTES DISCUSSION COMMENTS & FILE SUBMISSION                */
    /* ====================================================================== */

    /**
     * GET /api/student/notes/{id}/comments
     * Get discussion thread comments under a class note.
     */
    public function getNoteComments(Request $request, $id)
    {
        $comments = ClassNoteComment::where('class_note_id', $id)
            ->with(['user:id,name,role', 'student:id,first_name,last_name'])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($comments);
    }

    /**
     * POST /api/student/notes/{id}/comments
     * Post a comment under a class note.
     */
    public function postNoteComment(Request $request, $id)
    {
        $user = $request->user();
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        // Student model resolves dynamically for student endpoint
        $studentId = null;
        $userId = null;

        if ($user instanceof Student) {
            $studentId = $user->id;
        } else {
            $userId = $user->id;
        }

        $comment = ClassNoteComment::create([
            'class_note_id' => $id,
            'user_id'       => $userId,
            'student_id'    => $studentId,
            'comment'       => $request->comment,
        ]);

        return response()->json($comment->load(['user:id,name,role', 'student:id,first_name,last_name']), 201);
    }

    /**
     * POST /api/student/homework/{id}/submit-file
     * Accept file attachment uploads for homework submissions.
     */
    public function submitHomeworkFile(Request $request, $id)
    {
        $student = $request->user();
        if (!$student || !($student instanceof Student)) {
            return response()->json(['message' => 'Unauthorized student context.'], 403);
        }

        $request->validate([
            'file' => 'nullable|file|max:20480', // 20MB limit
            'submission' => 'nullable|string',
        ]);

        $homework = Homework::findOrFail($id);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('homework_attachments/' . $id, 'public');
        }

        $sub = HomeworkSubmission::updateOrCreate(
            ['homework_id' => $id, 'student_id' => $student->id],
            [
                'submission'   => $request->input('submission', ''),
                'attachment'   => $filePath,
                'submitted_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'data'   => $sub,
        ]);
    }

    /* ====================================================================== */
    /*  4. TEACHER CSV & ADVANCED SCORES ENTRY                                */
    /* ====================================================================== */

    /**
     * POST /api/teacher/scores/import
     * Batch import scores parsed from CSV data on mobile.
     */
    public function importScores(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['teacher', 'admin'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'scores'              => 'required|array',
            'scores.*.subject_id' => 'required|integer',
            'scores.*.class_id'   => 'required|integer',
            'scores.*.term'       => 'required|integer',
            'scores.*.session'    => 'required|string',
            'scores.*.admission_number' => 'nullable|string',
            'scores.*.student_id' => 'nullable|integer',
            'scores.*.ca1'        => 'nullable|integer|min:0',
            'scores.*.ca2'        => 'nullable|integer|min:0',
            'scores.*.exam'       => 'nullable|integer|min:0',
        ]);

        $imported = 0;
        foreach ($request->scores as $s) {
            $studentId = $s['student_id'] ?? null;
            if (!$studentId && !empty($s['admission_number'])) {
                $studentId = Student::where('admission_number', $s['admission_number'])->value('id');
            }

            if (!$studentId) {
                continue;
            }

            // Simple authorization bypass for admin, verify allocation for teacher
            if ($user->role === 'teacher') {
                $allowed = \App\Models\SubjectAllocation::where('teacher_id', $user->id)
                    ->where('class_id', $s['class_id'])
                    ->where('subject_id', $s['subject_id'])
                    ->exists();
                if (!$allowed) continue;
            }

            \App\Models\Score::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $s['subject_id'],
                    'class_id'   => $s['class_id'],
                    'term'       => $s['term'],
                    'session'    => $s['session'],
                ],
                [
                    'ca1'  => $s['ca1'] ?? 0,
                    'ca2'  => $s['ca2'] ?? 0,
                    'exam' => $s['exam'] ?? 0,
                ]
            );
            $imported++;
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Successfully imported {$imported} student scores.",
        ]);
    }

    /* ====================================================================== */
    /*  5. ADMIN PANEL CRUD & SETTINGS ENDPOINTS                              */
    /* ====================================================================== */

    private function authorizeAdmin(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);
    }

    // Sessions CRUD
    public function listSessions(Request $request)
    {
        $this->authorizeAdmin($request);
        return response()->json(AcademicSession::orderByDesc('name')->get());
    }

    public function createSession(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'name'      => 'required|string|max:9|unique:academic_sessions,name',
            'starts_on' => 'nullable|date',
            'ends_on'   => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($data['is_active'] ?? false) {
            AcademicSession::query()->update(['is_active' => false]);
        }

        $session = AcademicSession::create($data);
        return response()->json($session, 201);
    }

    public function updateSession(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $session = AcademicSession::findOrFail($id);
        $data = $request->validate([
            'name'      => 'required|string|max:9|unique:academic_sessions,name,' . $id,
            'starts_on' => 'nullable|date',
            'ends_on'   => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($data['is_active'] ?? false) {
            AcademicSession::query()->where('id', '!=', $id)->update(['is_active' => false]);
        }

        $session->update($data);
        return response()->json($session);
    }

    public function deleteSession(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        AcademicSession::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    // Terms CRUD
    public function listTerms(Request $request)
    {
        $this->authorizeAdmin($request);
        return response()->json(AcademicTerm::with('academicSession')->orderByDesc('id')->get());
    }

    public function createTerm(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'academic_session_id' => 'required|integer|exists:academic_sessions,id',
            'name'                => 'required|string|max:255',
            'term_number'         => 'required|integer|min:1|max:3',
            'starts_on'           => 'nullable|date',
            'ends_on'             => 'nullable|date',
            'is_active'           => 'boolean',
        ]);

        if ($data['is_active'] ?? false) {
            AcademicTerm::query()->update(['is_active' => false]);
        }

        $term = AcademicTerm::create($data);
        return response()->json($term, 201);
    }

    public function updateTerm(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $term = AcademicTerm::findOrFail($id);
        $data = $request->validate([
            'academic_session_id' => 'required|integer|exists:academic_sessions,id',
            'name'                => 'required|string|max:255',
            'term_number'         => 'required|integer|min:1|max:3',
            'starts_on'           => 'nullable|date',
            'ends_on'             => 'nullable|date',
            'is_active'           => 'boolean',
        ]);

        if ($data['is_active'] ?? false) {
            AcademicTerm::query()->where('id', '!=', $id)->update(['is_active' => false]);
        }

        $term->update($data);
        return response()->json($term);
    }

    public function deleteTerm(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        AcademicTerm::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    // User Management CRUD
    public function listUsers(Request $request)
    {
        $this->authorizeAdmin($request);
        return response()->json(User::orderBy('name')->get());
    }

    public function createUser(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users,email',
            'password'          => 'required|string|min:6',
            'role'              => 'required|string|in:admin,teacher,parent,bursar',
            'is_active'         => 'boolean',
            'whatsapp_phone'    => 'nullable|string',
            'is_class_teacher'  => 'boolean',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function updateUser(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:users,email,' . $id,
            'password'         => 'nullable|string|min:6',
            'role'             => 'required|string|in:admin,teacher,parent,bursar',
            'is_active'        => 'boolean',
            'whatsapp_phone'   => 'nullable|string',
            'is_class_teacher' => 'boolean',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function deleteUser(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted.']);
    }

    // Backups Trigger & List
    public function listBackups(Request $request)
    {
        $this->authorizeAdmin($request);
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            return response()->json([]);
        }

        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size'     => $file->getSize(),
                    'created'  => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        return response()->json($backups);
    }

    public function triggerBackup(Request $request)
    {
        $this->authorizeAdmin($request);

        // Check mysqldump availability
        $mysqldump = shell_exec('which mysqldump');
        if (empty(trim((string)$mysqldump))) {
            return response()->json(['message' => 'mysqldump utility is not available on this server.'], 500);
        }

        $db       = config('database.connections.mysql.database');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $user     = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $filename  = 'backup_' . $db . '_' . date('Y-m-d_His') . '.sql';
        $tmpPath   = storage_path('app/backups/' . $filename);

        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg((string)$port),
            escapeshellarg($user),
            escapeshellarg($password),
            escapeshellarg($db),
            escapeshellarg($tmpPath)
        );

        shell_exec($cmd);

        if (!file_exists($tmpPath) || filesize($tmpPath) === 0) {
            return response()->json(['message' => 'Backup operation failed.'], 500);
        }

        return response()->json([
            'status'    => 'success',
            'filename'  => $filename,
            'size'      => filesize($tmpPath),
            'download_url' => url('api/admin/backups/download/' . $filename),
        ]);
    }

    public function downloadBackup(Request $request, $filename)
    {
        $this->authorizeAdmin($request);

        // Prevent path traversal — only allow bare filenames
        $filename = basename($filename);
        if (empty($filename) || $filename === '.' || $filename === '..') {
            abort(400, 'Invalid filename.');
        }

        $path = storage_path('app/backups/' . $filename);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    // Mass announcements dispatch dispatcher
    public function dispatchBroadcast(Request $request)
    {
        $this->authorizeAdmin($request);
        $request->validate([
            'target'  => 'required|string|in:parents,staff,all',
            'message' => 'required|string|max:5000',
        ]);

        $target = strtolower($request->target);
        $query = User::where('whatsapp_subscribed', true)
            ->whereNotNull('whatsapp_phone')
            ->where('id', '!=', $request->user()->id);

        if ($target === 'parents') {
            $query->where('role', 'parent');
        } elseif ($target === 'staff') {
            $query->whereIn('role', ['teacher', 'bursar', 'admin']);
        }

        $phones = $query->pluck('whatsapp_phone')->toArray();
        $successCount = 0;

        // Reuse sendMetaMessage logic if configured in WhatsAppController
        $whatsAppController = new WhatsAppController();
        
        foreach ($phones as $phone) {
            try {
                // Safely invoke sendMetaMessage via Reflection or duplicate light-weight curl helper
                $sent = $this->sendSMSBroadcast($phone, "📢 *Broadcast Notification*\n\n" . $request->message);
                if ($sent) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                // Continue
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Broadcast successfully sent to {$successCount} of " . count($phones) . " subscribed WhatsApp phone(s).",
        ]);
    }

    private function sendSMSBroadcast($phone, $message)
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_number_id');

        if (empty($token) || empty($phoneId)) {
            return false;
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ])->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
            'messaging_product' => 'whatsapp',
            'to'                => $phone,
            'type'              => 'text',
            'text'              => ['body' => $message],
        ]);

        return $response->successful();
    }

    public function getNotifications(Request $request)
    {
        $user = $request->user();
        $notifications = \App\Models\InAppNotification::where('user_id', $user->id)
            ->latest()
            ->paginate((int) $request->query('per_page', 20));

        $formatted = collect($notifications->items())->map(function ($notif) {
            return [
                'id' => $notif->id,
                'title' => $notif->title,
                'body' => $notif->body,
                'type' => 'general',
                'link' => $notif->link,
                'read_at' => $notif->read_at?->toIso8601String(),
                'created_at' => $notif->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'notifications' => $formatted,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
            'unread_count' => \App\Models\InAppNotification::where('user_id', $user->id)->whereNull('read_at')->count(),
        ]);
    }

    public function markNotificationRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = \App\Models\InAppNotification::where('user_id', $user->id)->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $user = $request->user();
        \App\Models\InAppNotification::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}

