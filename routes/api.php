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
Route::post('/login', [AuthController::class, 'login']);
Route::post('/student/login', [StudentAuthController::class, 'login']);
Route::get('/tenant/{slug}', [TenantDiscoveryController::class, 'show']);

// Protected
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user',   [AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);

    // Active term
    Route::get('/term', function () {
        $term = \App\Models\AcademicTerm::active();
        return response()->json([
            'term'    => $term?->term_number ?? 1,
            'session' => $term?->academicSession?->name
                      ?? \App\Models\AcademicSession::activeName()
                      ?? date('Y') . '/' . (date('Y') + 1),
        ]);
    });

    // Students
    Route::get('/students',                      [StudentController::class, 'index']);
    Route::get('/students/{id}/report-card',     [StudentController::class, 'reportCard']);

    // Billing
    Route::get('/billing', [BillingController::class, 'index']);

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index']);

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
    Route::prefix('teacher')->group(function () {
        Route::get('/classes',                          [TeacherController::class, 'classes']);
        Route::get('/classes/{classId}/students',       [TeacherController::class, 'students']);
        Route::get('/classes/{classId}/subjects',       [TeacherController::class, 'subjects']);
        Route::get('/classes/{classId}/scores',         [TeacherController::class, 'scores']);
        Route::get('/classes/{classId}/attendance',     [TeacherController::class, 'attendance']);
        Route::post('/attendance',                      [TeacherController::class, 'saveAttendance']);
        Route::post('/scores',                          [TeacherController::class, 'saveScores']);
    });

    // Offline batch sync
    Route::post('/sync', [SyncController::class, 'handleSync']);

    // Media upload (teachers/admins/students)
    Route::post('/media/upload', [MediaUploadController::class, 'upload']);

    // Student portal API group
    Route::prefix('student')
        ->middleware(\App\Http\Middleware\EnsureStudentIsActive::class)
        ->group(function () {
        Route::post('/logout',                  [StudentAuthController::class, 'logout']);
        Route::get('/dashboard',                [StudentDashboardController::class, 'dashboard']);
        Route::get('/results',                  [StudentResultsController::class, 'results']);
        Route::get('/attendance',               [StudentAttendanceController::class, 'attendance']);
        Route::get('/exams',                    [StudentCbtController::class, 'exams']);
        Route::post('/exams/{exam}/start',      [StudentCbtController::class, 'startExam']);
        Route::post('/exams/{attempt}/submit',  [StudentCbtController::class, 'submitExam']);
        Route::get('/notes',                    [StudentELearningController::class, 'notes']);
        Route::get('/notes/{id}/download',      [StudentELearningController::class, 'download'])->name('api.student.notes.download');
        Route::get('/notifications',            [StudentNotificationController::class, 'index']);
        Route::post('/notifications/{id}/read',  [StudentNotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all',   [StudentNotificationController::class, 'markAllAsRead']);
    });
});

// Public Meta WhatsApp Webhook
Route::match(['get', 'post'], 'whatsapp/webhook', [WhatsAppController::class, 'handleWebhook']);

// WhatsApp Bot — authenticated via shared API key
Route::prefix('whatsapp')
    ->middleware(\App\Http\Middleware\VerifyWhatsAppApiKey::class)
    ->group(function () {
        Route::get('user/{phone}',          [WhatsAppController::class, 'getUser']);
        Route::get('attendance/{parentId}', [WhatsAppController::class, 'getAttendance']);
        Route::get('results/{parentId}',    [WhatsAppController::class, 'getResults']);
        Route::get('report-card/{studentId}', [WhatsAppController::class, 'getReportCardPDF'])->name('whatsapp.report-card');
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
        Route::get('checkout',              [WhatsAppController::class, 'checkout'])->name('whatsapp.pay');
        Route::post('checkout/process',     [WhatsAppController::class, 'processPayment'])->name('whatsapp.pay.process');
    });
