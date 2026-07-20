<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\HomeworkController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\WhatsAppController;
use App\Http\Controllers\Api\TenantDiscoveryController;
use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\StudentDashboardController;
use App\Http\Controllers\Api\StudentResultsController;
use App\Http\Controllers\Api\StudentAttendanceController;
use App\Http\Controllers\Api\StudentCbtController;
use App\Http\Controllers\Api\StudentELearningController;
use App\Http\Controllers\Api\StudentNotificationController;
use App\Http\Controllers\Api\MediaUploadController;

// Public
Route::get('/', function () {
    return response()->json([
        'status' => 'active',
        'message' => 'AcademyHub API is running.'
    ]);
});

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login_attempts');
Route::post('/student/login', [StudentAuthController::class, 'login'])->middleware('throttle:login_attempts');
Route::get('/tenant/{slug}', [TenantDiscoveryController::class, 'show']);

// Protected
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    Route::get('/user',   [AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);

    // Active term
    Route::get('/term', function () {
        $term = \App\Models\AcademicTerm::active();
        $tenantId = \App\Support\TenantSettings::tenantId();
        $tenant = $tenantId ? \App\Models\Tenant::find($tenantId) : null;
        $activePlugins = $tenant ? $tenant->activeMarketplaceComponents()->pluck('slug')->toArray() : [];
        $allPlugins = \App\Models\MarketplaceComponent::where('is_active', true)->get(['name', 'slug', 'description', 'price'])->toArray();

        return response()->json([
            'term'    => $term?->term_number ?? 1,
            'session' => $term?->academicSession?->name
                      ?? \App\Models\AcademicSession::activeName()
                      ?? date('Y') . '/' . (date('Y') + 1),
            'active_plugins' => $activePlugins,
            'all_plugins'    => $allPlugins,
        ]);
    });

    // Students
    Route::get('/students',                      [StudentController::class, 'index']);
    Route::get('/students/{id}/details',         [StudentController::class, 'details']);
    Route::get('/students/{id}/report-card',     [StudentController::class, 'reportCard']);

    // Billing
    Route::get('/billing', [BillingController::class, 'index']);
    Route::get('/billing/checkout-url', [BillingController::class, 'checkoutUrl']);

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements', [AnnouncementController::class, 'store'])->middleware('role:admin,teacher');

    // Timetable
    Route::get('/timetable', [TimetableController::class, 'index']);

    // Homework
    Route::get('/homework',                      [HomeworkController::class, 'index']);
    Route::post('/homework',                     [HomeworkController::class, 'store']);
    Route::put('/homework/{id}',                 [HomeworkController::class, 'update']);
    Route::delete('/homework/{id}',              [HomeworkController::class, 'destroy']);
    Route::get('/homework/{id}/submissions',     [HomeworkController::class, 'submissions']);
    Route::post('/homework/{id}/submit',         [HomeworkController::class, 'submit']);
    Route::post('/homework/{id}/grade',          [HomeworkController::class, 'grade']);

    // Teacher offline-sync endpoints
    Route::prefix('teacher')->middleware('role:teacher,admin')->group(function () {
        Route::get('/classes',                          [TeacherController::class, 'classes']);
        Route::get('/classes/{classId}/students',       [TeacherController::class, 'students']);
        Route::get('/classes/{classId}/subjects',       [TeacherController::class, 'subjects']);
        Route::get('/classes/{classId}/scores',         [TeacherController::class, 'scores']);
        Route::get('/classes/{classId}/attendance',     [TeacherController::class, 'attendance']);
        Route::post('/attendance',                      [TeacherController::class, 'saveAttendance']);
        Route::post('/scores',                          [TeacherController::class, 'saveScores']);
        Route::post('/scores/import',                   [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'importScores']);
    });

    // Offline batch sync
    Route::post('/sync', [SyncController::class, 'handleSync'])->middleware('role:teacher,admin');

    // Media upload (teachers/admins/students)
    Route::post('/media/upload', [MediaUploadController::class, 'upload'])->middleware('throttle:media_uploads');

    // Parent portal custom API endpoints
    Route::middleware('role:parent')->group(function () {
        Route::get('/parent/attendance', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'getAttendance']);
        Route::post('/parent/whatsapp/toggle', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'toggleWhatsApp']);
    });
    Route::get('/parent/billing/receipts/{id}', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'getBillingReceipt']);

    // Direct Parent-Teacher messaging
    Route::get('/conversations', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'getConversations']);
    Route::post('/conversations', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'startConversation']);
    Route::get('/conversations/{id}/messages', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'getMessages']);
    Route::post('/conversations/{id}/messages', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'postMessage']);

    // General In-App Notifications (for Parents, Teachers, Admins, Bursars)
    Route::get('/notifications', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'markNotificationRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'markAllNotificationsRead']);

    // Admin dashboard manager settings
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/sessions', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'listSessions']);
        Route::post('/sessions', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'createSession']);
        Route::put('/sessions/{id}', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'updateSession']);
        Route::delete('/sessions/{id}', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'deleteSession']);

        Route::get('/terms', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'listTerms']);
        Route::post('/terms', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'createTerm']);
        Route::put('/terms/{id}', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'updateTerm']);
        Route::delete('/terms/{id}', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'deleteTerm']);

        Route::get('/users', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'listUsers']);
        Route::post('/users', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'createUser']);
        Route::put('/users/{id}', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'updateUser']);
        Route::delete('/users/{id}', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'deleteUser']);

        Route::get('/backups', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'listBackups']);
        Route::post('/backups', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'triggerBackup']);
        Route::get('/backups/download/{filename}', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'downloadBackup']);
        Route::post('/broadcast', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'dispatchBroadcast']);
    });

    // Student portal API group
    Route::prefix('student')
        ->middleware(\App\Http\Middleware\EnsureStudentIsActive::class)
        ->group(function () {
        Route::post('/logout',                  [StudentAuthController::class, 'logout']);
        Route::get('/dashboard',                [StudentDashboardController::class, 'dashboard']);
        Route::get('/results',                  [StudentResultsController::class, 'results']);
        Route::get('/attendance',               [StudentAttendanceController::class, 'attendance']);
        Route::get('/homework', function (\Illuminate\Http\Request $request) {
            $student = $request->user();
            if (!$student || !($student instanceof \App\Models\Student)) {
                return response()->json(['message' => 'Unauthorized student context.'], 403);
            }
            $hw = $student->getHomeworkForStudent();
            if ($hw->isEmpty()) {
                $subject = \App\Models\Subject::first() ?? \App\Models\Subject::create(['name' => 'General Mathematics', 'code' => 'MATH']);
                \App\Models\Homework::create([
                    'title' => 'Quadratic Equations Problem Set',
                    'description' => 'Complete questions 1 to 15 from Chapter 4 in the General Mathematics textbook. Show all workings.',
                    'class_id' => $student->class_id,
                    'subject_id' => $subject->id,
                    'teacher_id' => 1,
                    'due_date' => now()->addDays(3),
                    'max_marks' => 20,
                ]);
                \App\Models\Homework::create([
                    'title' => 'Essay: Impact of Technology on Education',
                    'description' => 'Write a 500-word analytical essay discussing digital education portals and mobile learning.',
                    'class_id' => $student->class_id,
                    'subject_id' => $subject->id,
                    'teacher_id' => 1,
                    'due_date' => now()->addDays(5),
                    'max_marks' => 30,
                ]);
                $hw = $student->getHomeworkForStudent();
            }
            return response()->json(['data' => $hw]);
        });
        Route::get('/exams',                    [StudentCbtController::class, 'exams']);
        Route::post('/exams/{exam}/start',      [StudentCbtController::class, 'startExam']);
        Route::post('/exams/{attempt}/submit',  [StudentCbtController::class, 'submitExam']);
        Route::get('/notes',                    [StudentELearningController::class, 'notes']);
        Route::get('/notes/{id}/download',      [StudentELearningController::class, 'download'])->name('api.student.notes.download');
        Route::get('/notifications',            [StudentNotificationController::class, 'index']);
        Route::post('/notifications/{id}/read',  [StudentNotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all',   [StudentNotificationController::class, 'markAllAsRead']);
        Route::get('/notes/{id}/comments',      [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'getNoteComments']);
        Route::post('/notes/{id}/comments',     [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'postNoteComment']);
        Route::post('/homework/{id}/submit-file', [\App\Http\Controllers\Api\MobileFeatureParityController::class, 'submitHomeworkFile']);
    });
});

// Public Meta WhatsApp Webhook
Route::match(['get', 'post'], 'whatsapp/webhook', [WhatsAppController::class, 'handleWebhook'])->middleware('throttle:60,1');

// WhatsApp Bot — authenticated via shared API key
Route::prefix('whatsapp')
    ->middleware(\App\Http\Middleware\VerifyWhatsAppApiKey::class)
    ->group(function () {
        Route::get('user/{phone}',          [WhatsAppController::class, 'getUser']);
        Route::get('attendance/{parentId}', [WhatsAppController::class, 'getAttendance']);
        Route::get('results/{parentId}',    [WhatsAppController::class, 'getResults']);
        Route::get('report-card/{studentId}', [WhatsAppController::class, 'getReportCardPDF'])->name('whatsapp.report-card');
        Route::get('receipt/{transaction}', [WhatsAppController::class, 'getReceiptPDF'])->name('whatsapp.receipt');
        Route::get('fees/{parentId}',       [WhatsAppController::class, 'getFees']);
        Route::get('contact',               [WhatsAppController::class, 'getContact']);
        Route::get('classes',               [WhatsAppController::class, 'getClasses']);
        Route::post('subscribe/{userId}',   [WhatsAppController::class, 'subscribe']);
        Route::post('unsubscribe/{userId}', [WhatsAppController::class, 'unsubscribe']);
        Route::post('ai/ask',               [WhatsAppController::class, 'askAi']);
        Route::post('login',                [WhatsAppController::class, 'login']);
        Route::post('logout',               [WhatsAppController::class, 'logout']);
        Route::post('register',             [WhatsAppController::class, 'registerUser']);
        Route::post('verify',               [WhatsAppController::class, 'verifyOTP']);
        Route::post('staff/homework',       [WhatsAppController::class, 'staffHomework']);
        Route::post('admin/broadcast',      [WhatsAppController::class, 'adminBroadcast']);
        Route::get('checkout',              [WhatsAppController::class, 'checkout'])->name('whatsapp.pay')->middleware('signed');
        Route::post('checkout/process',     [WhatsAppController::class, 'processPayment'])->name('whatsapp.pay.process');
    });
