<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingReceiptController;
use App\Http\Controllers\BillingExportController;
use App\Http\Controllers\CbtExportController;
use App\Http\Controllers\AdmissionFormController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\UtilityController;

use App\Http\Controllers\BulkReportCardsController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentsExportController;
use App\Http\Controllers\MessageAttachmentController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\PaystackCallbackController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\PhotoRandomizerController;
use App\Livewire\Classes\ManageSubjects;
use App\Livewire\Cbt\ExamEditor as CbtExamEditor;
use App\Livewire\Cbt\Index as CbtIndex;
use App\Livewire\Cbt\Portal\Start as CbtPortalStart;
use App\Livewire\Cbt\Portal\Take as CbtPortalTake;
use App\Livewire\Academics\Sessions as AcademicSessions;
use App\Livewire\Announcements\Index as AnnouncementsIndex;
use App\Livewire\AuditLogs\Index as AuditLogsIndex;
use App\Livewire\Billing\Index as BillingIndex;
use App\Livewire\Attendance\Index as AttendanceIndex;
use App\Livewire\Attendance\Teachers as TeacherAttendance;
use App\Livewire\Certificates\Index as CertificatesIndex;
use App\Livewire\Certificates\Manager as CertificatesManager;
use App\Livewire\Events\Index as EventsIndex;
use App\Livewire\Homework\Index as HomeworkIndex;
use App\Livewire\Results\Entry as ResultsEntry;
use App\Livewire\Results\Broadsheet as ResultsBroadsheet;
use App\Livewire\Results\Submissions as ResultsSubmissions;
use App\Livewire\DataCollection\Weekly as DataCollectionWeekly;
use App\Livewire\DataCollection\Submissions as DataCollectionSubmissions;
use App\Livewire\Messages\Index as MessagesIndex;
use App\Livewire\Marketplace\Index as MarketplaceIndex;
use App\Livewire\Notifications\Index as NotificationsIndex;
use App\Livewire\Promotions\Index as PromotionsIndex;
use App\Livewire\SavingsLoan\Index as SavingsLoanIndex;
use App\Livewire\Students\Form as StudentsForm;
use App\Livewire\Students\Index as StudentsIndex;
use App\Livewire\Timetable\Index as TimetableIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Imports\Index as ImportsIndex;
use App\Livewire\Imports\Students as ImportsStudents;
use App\Livewire\Imports\Teachers as ImportsTeachers;
use App\Livewire\Settings\CustomFields;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [UtilityController::class, 'welcome']);

Route::get('/home', [UtilityController::class, 'home']);

// CBT Portal Start Routes (public, no student session required to enter code/admission no)
Route::middleware(['plugin:cbt', 'throttle:cbt_portal'])->group(function () {
    Route::get('/cbt/portal', CbtPortalStart::class)->name('cbt.portal');
    Route::get('/cbt/student', CbtPortalStart::class)->name('cbt.student');
});

// CBT Portal Take Routes (student session required to take the exam)
Route::middleware(['student.session', 'plugin:cbt', 'throttle:cbt_attempt'])->group(function () {
    Route::get('/cbt/portal/{attempt}', CbtPortalTake::class)->name('cbt.portal.take');
    Route::get('/cbt/student/{attempt}', CbtPortalTake::class)->name('cbt.student.take');
});

// Fresh CSRF token endpoint — used by JS logout to prevent 419 Page Expired
Route::get('/csrf-token', [UtilityController::class, 'csrfToken']);
Route::post('/log-error', [UtilityController::class, 'logClientError']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware('throttle:auth_views')
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login_attempts')
        ->name('login.store');

    // Password Reset
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
        ->middleware('throttle:auth_views')
        ->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:login_attempts')
        ->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->middleware('throttle:auth_views')
        ->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->middleware('throttle:login_attempts')
        ->name('password.update');
});


Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::post('/student/logout', [AuthenticatedSessionController::class, 'studentLogout'])
    ->name('student.logout');

// Student dashboard route (session-based, not auth-based)
Route::get('/student/dashboard', \App\Livewire\Student\Dashboard::class)
    ->middleware('student.session')
    ->name('student.dashboard');

Route::get('/student/homework', \App\Livewire\Student\Homework::class)
    ->middleware(['student.session', 'plugin:homework'])
    ->name('student.homework');

Route::get('/student/e-learning', \App\Livewire\Student\ELearning::class)
    ->middleware(['student.session', 'plugin:e-learning'])
    ->name('student.e-learning');

Route::get('/student/exams', \App\Livewire\Student\Exams::class)
    ->middleware(['student.session', 'plugin:cbt'])
    ->name('student.exams');

Route::get('/student/results', \App\Livewire\Student\Results::class)
    ->middleware('student.session')
    ->name('student.results');

Route::get('/student/report-card', [UtilityController::class, 'studentReportCard'])
    ->middleware('student.session')
    ->name('student.report-card');

Route::get('/student/attendance', \App\Livewire\Student\Attendance::class)
    ->middleware('student.session')
    ->name('student.attendance');

Route::get('/student/performance', \App\Livewire\Student\Performance::class)
    ->middleware('student.session')
    ->name('student.performance');

Route::get('/student/notifications', \App\Livewire\Student\Notifications::class)
    ->middleware('student.session')
    ->name('student.notifications');

Route::get('/student/profile', \App\Livewire\Student\Profile::class)
    ->middleware('student.session')
    ->name('student.profile');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/paystack/callback', [PaystackCallbackController::class, 'handleCallback'])
        ->middleware('throttle:payment_callback')
        ->name('paystack.callback');
    Route::get('/dashboard', [UtilityController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::post('/profile/details', [ProfileController::class, 'updateDetails'])->name('profile.details');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');

    Route::view('/more-features', 'pages.more-features.index')->name('more-features');

    Route::middleware('role:admin')->group(function () {
        Route::post('/randomize-photos', [PhotoRandomizerController::class, 'randomize'])->name('photos.randomize');
        
        Route::get('/marketplace', MarketplaceIndex::class)->name('marketplace');
        Route::get('/marketplace/product/{product}', \App\Livewire\Marketplace\ProductDetail::class)->name('marketplace.product');

        Route::get('/parents', \App\Livewire\Parents\Management::class)->name('parents.index');

        // Parent Portal manage plugin route
        Route::middleware('plugin:parent-portal')->group(function () {
            Route::get('/parent-portal', \App\Livewire\Parents\Management::class)->name('parent-portal.index');
            Route::get('/parent', \App\Livewire\Parents\Management::class); // Fallback matching DB route_name
        });


        Route::get('/students/create', StudentsForm::class)->name('students.create');
        Route::get('/students/{student}/edit', StudentsForm::class)->name('students.edit')->where('student', '[A-Za-z0-9\/\-\.]+');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy')->where('student', '[A-Za-z0-9\/\-\.]+');

        Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::patch('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::post('/teachers/{teacher}/photo', [TeacherController::class, 'updatePhoto'])->name('teachers.photo');
        Route::post('/teachers/{teacher}/allocations', [TeacherController::class, 'storeAllocation'])->name('teachers.allocations.store');
        Route::delete('/teachers/{teacher}/allocations/{allocation}', [TeacherController::class, 'destroyAllocation'])->name('teachers.allocations.destroy');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

        Route::get('/classes/manage', [SchoolClassController::class, 'manage'])->name('classes.manage');
        Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
        Route::patch('/classes/{class}', [SchoolClassController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{class}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');
        Route::post('/classes/{class}/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::patch('/classes/{class}/sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/classes/{class}/sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::patch('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        Route::get('/users', UsersIndex::class)->middleware('permission:users.manage')->name('users.index');

        Route::get('/imports', ImportsIndex::class)->name('imports.index');
        Route::get('/imports/students', ImportsStudents::class)->name('imports.students');
        Route::get('/imports/teachers', ImportsTeachers::class)->name('imports.teachers');

        Route::get('/results/bulk-report-cards', [BulkReportCardsController::class, 'index'])
            ->middleware('permission:results.publish')
            ->name('results.bulk-report-cards');
        Route::get('/api/classes/{class}/students', [BulkReportCardsController::class, 'getStudents'])
            ->name('api.classes.students');
        Route::post('/results/bulk-report-cards', [BulkReportCardsController::class, 'generate'])
            ->middleware('permission:results.publish')
            ->name('results.bulk-report-cards.generate');
        Route::get('/results/bulk-report-cards/preview', [BulkReportCardsController::class, 'preview'])
            ->middleware('permission:results.publish')
            ->name('results.bulk-report-cards.preview');

        Route::post('/settings/school', [SettingsController::class, 'updateSchool'])->name('settings.update-school');
        Route::post('/settings/results', [SettingsController::class, 'updateResults'])->name('settings.update-results');
        Route::post('/settings/certificates', [SettingsController::class, 'updateCertificates'])->name('settings.update-certificates');
    });

    Route::get('/students', StudentsIndex::class)->name('students.index');
    Route::get('/students/export', [StudentsExportController::class, 'export'])->name('students.export');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show')->where('student', '[A-Za-z0-9\/\-\.]+');
    Route::get('/students/{student}/admission-form', [AdmissionFormController::class, 'download'])->name('students.admission-form')->where('student', '[A-Za-z0-9\/\-\.]+');

    Route::middleware('role:admin,bursar')->group(function () {
        Route::get('/billing', BillingIndex::class)->middleware('permission:billing.transactions,fees.manage')->name('billing.index');
        Route::get('/billing/receipt/{transaction}', [BillingReceiptController::class, 'download'])
            ->middleware('permission:billing.transactions')
            ->name('billing.receipt');
        Route::get('/billing/export/transactions', [BillingExportController::class, 'transactions'])
            ->middleware('permission:billing.export')
            ->name('billing.export.transactions');

        Route::get('/savings-loan', SavingsLoanIndex::class)
            ->name('savings-loan.index');
    });

    Route::middleware('role:parent')->group(function () {
        Route::get('/parents/dashboard', \App\Livewire\Parents\Dashboard::class)->name('parents.dashboard');
        Route::get('/parents/performance', \App\Livewire\Student\Performance::class)->name('parents.performance');
    });

    // Payment Gateway — only accessible after purchasing/installing the Payment Gateway plugin
    Route::middleware('plugin:payment-gateway')->group(function () {
        Route::middleware('role:admin,bursar')->group(function () {
            Route::get('/payment-gateway', \App\Livewire\PaymentGateway\Index::class)->name('payment-gateway.index');
        });
        Route::middleware('role:parent')->group(function () {
            Route::get('/parent/pay', \App\Livewire\PaymentGateway\ParentPay::class)->name('parent.pay');
        });
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::view('/institute', 'pages.institute.index')->name('institute');
        Route::view('/teachers', 'pages.teachers.index')->name('teachers');
        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
        Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/{class}/subjects', ManageSubjects::class)->middleware('role:admin')->name('classes.subjects');
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/attendance', AttendanceIndex::class)->name('attendance');
        Route::get('/attendance/teachers', TeacherAttendance::class)->name('attendance.teachers');
        Route::get('/data-collection', DataCollectionWeekly::class)->middleware('permission:data_collection.submit')->name('data-collection');
        Route::view('/examination', 'pages.examination.index')->name('examination');
        // CBT — only accessible after purchasing/installing the CBT plugin
        Route::middleware('plugin:cbt')->group(function () {
            Route::get('/cbt', CbtIndex::class)
                ->name('cbt.index');
            Route::get('/cbt/exams/{exam}', CbtExamEditor::class)
                ->name('cbt.exams.edit');
            Route::get('/cbt/exams/{exam}/theory', \App\Livewire\Cbt\TheoryReview::class)
                ->name('cbt.exams.theory');
            Route::get('/cbt/exams/{exam}/pdf', [CbtExportController::class, 'examPdf'])
                ->name('cbt.exams.pdf');
            Route::get('/cbt/sample-download', [UtilityController::class, 'cbtSampleDownload'])
                ->name('cbt.sample-download');
            Route::get('/cbt/attempt/{attempt}/export-pdf', [CbtExportController::class, 'exportAttemptResultPdf'])
                ->name('cbt.attempt.export-pdf');
        });

        // E-Learning — only accessible after purchasing/installing the E-Learning plugin
        Route::middleware('plugin:e-learning')->group(function () {
            Route::get('/e-learning', \App\Livewire\ELearning\Index::class)->name('e-learning.index');
        });


        Route::get('/analytics', \App\Livewire\Analytics\Dashboard::class)
            ->middleware('permission:analytics.view')
            ->name('analytics.dashboard');
        
        Route::get('/analytics/student-performance', \App\Livewire\Analytics\StudentPerformance::class)
            ->middleware('permission:analytics.view')
            ->name('analytics.student-performance');
        
        Route::get('/analytics/export/performance', [\App\Http\Controllers\AnalyticsExportController::class, 'exportPerformanceData'])
            ->middleware('permission:analytics.view')
            ->name('analytics.export.performance');
        
        Route::get('/analytics/export/attendance', [\App\Http\Controllers\AnalyticsExportController::class, 'exportAttendanceData'])
            ->middleware('permission:analytics.view')
            ->name('analytics.export.attendance');
        
        Route::get('/analytics/export/financial', [\App\Http\Controllers\AnalyticsExportController::class, 'exportFinancialData'])
            ->middleware('role:admin,bursar')
            ->name('analytics.export.financial');
        
        Route::get('/analytics/export/cbt', [\App\Http\Controllers\AnalyticsExportController::class, 'exportCbtData'])
            ->middleware('permission:analytics.view')
            ->name('analytics.export.cbt');

        Route::get('/results/entry', ResultsEntry::class)->middleware('permission:results.entry,results.review')->name('results.entry');

        Route::get('/homework', HomeworkIndex::class)->middleware('plugin:homework')->name('homework.index');
        Route::get('/homework/{id}/submissions', \App\Livewire\Homework\Submissions::class)->middleware('plugin:homework')->name('homework.submissions');

        
        if (config('app.debug')) {
            Route::get('/settings/debug', [UtilityController::class, 'settingsDebug'])
                ->middleware('role:admin')
                ->name('settings.debug');
        }
        Route::get('/results/broadsheet', ResultsBroadsheet::class)->middleware('permission:results.broadsheet')->name('results.broadsheet');
        Route::get('/results/submissions', ResultsSubmissions::class)->middleware('role:admin')->name('results.submissions');

        Route::get('/events', EventsIndex::class)->name('events');
        Route::get('/timetable', TimetableIndex::class)->name('timetable');
        Route::get('/timetable/pdf', [\App\Http\Controllers\TimetableController::class, 'downloadPdf'])->name('timetable.pdf');
        Route::get('/certificates', CertificatesManager::class)->name('certificates');
        Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    });

    // Results (Shared between Admin, Teacher, and Parent)
    Route::get('/results/report-card/{student}', [ReportCardController::class, 'download'])
        ->middleware('role:admin,teacher,parent')
        ->name('results.report-card')
        ->where('student', '[A-Za-z0-9\/\-\.]+');

    Route::middleware('role:bursar')->group(function () {
        Route::view('/accounts', 'pages.accounts.index')->middleware('permission:billing.transactions')->name('accounts');
    });

    Route::middleware('role:admin,teacher,bursar')->group(function () {
        Route::get('/messages', MessagesIndex::class)->middleware('permission:messages.access')->name('messages');
        Route::get('/messages/attachments/{message}', [MessageAttachmentController::class, 'download'])->name('messages.attachments.download');
        Route::get('/announcements', AnnouncementsIndex::class)->name('announcements');
        Route::get('/notifications', NotificationsIndex::class)->name('notifications');
    });

    Route::middleware('role:admin')->group(function () {
        Route::view('/settings', 'pages.settings.index')->name('settings.index');
        Route::view('/settings/results', 'pages.settings.results')->name('settings.results');
        Route::view('/settings/certificates', 'pages.settings.certificates')->name('settings.certificates');
        Route::get('/settings/templates', [SettingsController::class, 'showTemplates'])->name('settings.templates');
        Route::post('/settings/templates', [SettingsController::class, 'updateTemplates'])->name('settings.update-templates');
        Route::get('/settings/templates/preview/{type}/{template}', [SettingsController::class, 'previewTemplate'])->name('settings.templates.preview');

        Route::get('/settings/audit-logs', AuditLogsIndex::class)->middleware('permission:audit.view')->name('settings.audit-logs');
        Route::get('/settings/custom-fields', CustomFields::class)->name('settings.custom-fields');
        Route::get('/settings/subscription', \App\Livewire\Admin\SubscriptionBilling::class)->name('settings.subscription');

        Route::get('/promotions', PromotionsIndex::class)->name('promotions');
        Route::get('/academic-sessions', AcademicSessions::class)->name('academic-sessions');

        Route::get('/data-collection/submissions', DataCollectionSubmissions::class)
            ->middleware('permission:data_collection.review')
            ->name('data-collection.submissions');

        Route::get('/cbt/exams/{exam}/export', [CbtExportController::class, 'examResults'])
            ->name('cbt.exams.export');

        // System Backup
        Route::get('/backup/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])
            ->name('backup.download');

        // School Admin friendly diagnostics
        Route::get('/settings/health', [\App\Http\Controllers\Admin\HealthController::class, 'index'])->name('admin.health');
        Route::post('/settings/health/diagnose', [\App\Http\Controllers\Admin\HealthController::class, 'diagnose'])->name('admin.health.diagnose');
    });

    // Marketplace product detail alias (used by bursar upgrade link)
    Route::get('/marketplace/product/{product}', \App\Livewire\Marketplace\ProductDetail::class)
        ->name('marketplace.show')
        ->middleware('role:admin');
});

// ══════════════════════════════════════════════════════════════════════
// SUPER ADMIN ROUTES (Multi-Tenant Management)
// ══════════════════════════════════════════════════════════════════════
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\SuperAdmin\LoginController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\SuperAdmin\LoginController::class, 'store'])->name('login.store');
    Route::post('/logout', [\App\Http\Controllers\SuperAdmin\LoginController::class, 'destroy'])->name('logout');

    Route::middleware(['auth', 'superadmin'])->group(function () {
        Route::get('/', \App\Http\Controllers\SuperAdmin\DashboardController::class)->name('dashboard');
        Route::post('/verify-password', [\App\Http\Controllers\SuperAdmin\PasswordConfirmationController::class, 'verify'])->name('verify-password');
        Route::resource('tenants', \App\Http\Controllers\SuperAdmin\TenantController::class)->except(['show']);
        Route::put('tenants/{tenant}/admins/{admin}', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'updateAdmin'])->name('tenants.admins.update');
        Route::resource('marketplace', \App\Http\Controllers\SuperAdmin\MarketplaceController::class)->except(['show']);

        // Platform backup
        Route::get('/backup/download', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'download'])
            ->name('backup.download');

        // Platform Health & Console
        Route::get('/health', [\App\Http\Controllers\SuperAdmin\HealthController::class, 'index'])->name('health');
        Route::post('/health/clear-cache', [\App\Http\Controllers\SuperAdmin\HealthController::class, 'clearCache'])->name('health.clear-cache');
        Route::post('/health/clear-logs', [\App\Http\Controllers\SuperAdmin\HealthController::class, 'clearLogs'])->name('health.clear-logs');
        Route::post('/impersonate/{user}', [\App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'start'])->name('impersonate.start');
        
        // Advanced Tenant Controls & Customization
        Route::get('tenants/{tenant}/check-dns', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'checkDns'])->name('tenants.check-dns');
        Route::post('tenants/{tenant}/impersonate', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'impersonate'])->name('tenants.impersonate');
        Route::post('tenants/{tenant}/save-flags', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'saveFlags'])->name('tenants.save-flags');
        Route::post('tenants/{tenant}/save-broadcast', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'saveBroadcast'])->name('tenants.save-broadcast');
        Route::post('tenants/{tenant}/approve-subaccount', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'approveSubaccount'])->name('tenants.approve-subaccount');
        Route::post('tenants/{tenant}/reject-subaccount', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'rejectSubaccount'])->name('tenants.reject-subaccount');
        Route::put('tenants/{tenant}/plugins/{component}', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'updatePluginPricing'])->name('tenants.plugins.update');
        Route::post('tenants/{tenant}/plugins/{component}/activate', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'activatePlugin'])->name('tenants.plugins.activate');
        Route::post('tenants/{tenant}/plugins/{component}/deactivate', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'deactivatePlugin'])->name('tenants.plugins.deactivate');
        Route::post('tenants/{tenant}/backup', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'exportBackup'])->name('tenants.backup');
        Route::post('tenants/{tenant}/restore', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'importBackup'])->name('tenants.restore');
        
        // Dynamic Invoicing Ledger
        Route::post('tenants/{tenant}/bills/generate', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'generateBill'])->name('tenants.bills.generate');
        Route::post('tenants/{tenant}/bills/{bill}/pay', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'payBill'])->name('tenants.bills.pay');
        Route::post('tenants/{tenant}/bills/{bill}/void', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'voidBill'])->name('tenants.bills.void');

        // Real-Time Endpoint Monitor Health Suite
        Route::get('/health/endpoints', [\App\Http\Controllers\SuperAdmin\HealthController::class, 'endpoints'])->name('health.endpoints');
        Route::get('/health/ping-endpoint', [\App\Http\Controllers\SuperAdmin\HealthController::class, 'pingEndpoint'])->name('health.ping-endpoint');
        Route::post('/health/start-background-ping', [\App\Http\Controllers\SuperAdmin\HealthController::class, 'startBackgroundPing'])->name('health.start-background-ping');
        Route::get('/health/ping-status', [\App\Http\Controllers\SuperAdmin\HealthController::class, 'pingStatus'])->name('health.ping-status');

        // Notification Bell API
        Route::get('/notifications', [\App\Http\Controllers\SuperAdmin\NotificationsController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\SuperAdmin\NotificationsController::class, 'markRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\SuperAdmin\NotificationsController::class, 'markAllRead'])->name('notifications.mark-all-read');
        
        // Notifications Web Console
        Route::get('/notifications-list', [\App\Http\Controllers\SuperAdmin\NotificationsController::class, 'listView'])->name('notifications.list');
        Route::get('/notifications/{notification}/open', [\App\Http\Controllers\SuperAdmin\NotificationsController::class, 'open'])->name('notifications.open');

        // Platform Pricing Settings
        Route::get('/settings/pricing', [\App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'edit'])->name('settings.pricing');
        Route::post('/settings/pricing', [\App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'update'])->name('settings.pricing.update');
    });
});

// Impersonation entry/return routes (accessible globally on any subdomain)
Route::get('/impersonate/login', [\App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'login'])->name('impersonate.login');
Route::get('/impersonate/return', [\App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'stop'])->name('impersonate.stop');

// ── Deploy Cache Clear (no auth, secret key only) ──
Route::get('/deploy-clear-cache/{key}', function (string $key) {
    $validKey = config('services.deploy.cache_clear_key');

    // Abort if no key is configured or the provided key doesn't match
    if (! $validKey || ! hash_equals($validKey, $key)) {
        abort(403);
    }

    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    \Illuminate\Support\Facades\Artisan::call('optimize');
    \Illuminate\Support\Facades\Artisan::call('view:cache');

    return response()->json([
        'status'  => 'done',
        'message' => 'All caches cleared and rebuilt successfully.',
        'time'    => now()->toDateTimeString(),
    ]);
})->withoutMiddleware(\App\Http\Middleware\TenantDiscovery::class);

