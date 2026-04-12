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

// Public
Route::post('/login', [AuthController::class, 'login']);

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
});

// WhatsApp Bot
Route::prefix('whatsapp')->group(function () {
    Route::get('user/{phone}',          [\App\Http\Controllers\Api\WhatsAppController::class, 'getUser']);
    Route::get('attendance/{parentId}', [\App\Http\Controllers\Api\WhatsAppController::class, 'getAttendance']);
    Route::get('results/{parentId}',    [\App\Http\Controllers\Api\WhatsAppController::class, 'getResults']);
    Route::get('fees/{parentId}',       [\App\Http\Controllers\Api\WhatsAppController::class, 'getFees']);
    Route::get('contact',               [\App\Http\Controllers\Api\WhatsAppController::class, 'getContact']);
    Route::post('subscribe/{userId}',   [\App\Http\Controllers\Api\WhatsAppController::class, 'subscribe']);
    Route::post('unsubscribe/{userId}', [\App\Http\Controllers\Api\WhatsAppController::class, 'unsubscribe']);
    Route::post('ai/ask',               [\App\Http\Controllers\Api\WhatsAppController::class, 'askAi']);
    Route::post('register',             [\App\Http\Controllers\Api\WhatsAppController::class, 'registerUser']);
    Route::post('verify',               [\App\Http\Controllers\Api\WhatsAppController::class, 'verifyOTP']);
    Route::post('staff/homework',       [\App\Http\Controllers\Api\WhatsAppController::class, 'staffHomework']);
    Route::post('admin/broadcast',      [\App\Http\Controllers\Api\WhatsAppController::class, 'adminBroadcast']);
});
