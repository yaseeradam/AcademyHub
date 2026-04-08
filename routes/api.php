<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\AnnouncementController;

use App\Http\Controllers\Api\SyncController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Students
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}/report-card', [StudentController::class, 'reportCard']);

    // Billing
    Route::get('/billing', [BillingController::class, 'index']);
    
    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index']);

    // Offline Sync Engine
    Route::post('/sync', [SyncController::class, 'handleSync']);
});

// WhatsApp Bot Routes
Route::prefix('whatsapp')->group(function () {
    Route::get('parent/{phone}', [\App\Http\Controllers\Api\WhatsAppController::class, 'getParent']);
    Route::get('attendance/{parentId}', [\App\Http\Controllers\Api\WhatsAppController::class, 'getAttendance']);
    Route::get('results/{parentId}', [\App\Http\Controllers\Api\WhatsAppController::class, 'getResults']);
    Route::get('fees/{parentId}', [\App\Http\Controllers\Api\WhatsAppController::class, 'getFees']);
    Route::post('register', [\App\Http\Controllers\Api\WhatsAppController::class, 'registerParent']);
    Route::post('verify', [\App\Http\Controllers\Api\WhatsAppController::class, 'verifyOTP']);
});
