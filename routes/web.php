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
use App\Livewire\Imports\Subjects as ImportsSubjects;
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

// CBT Portal Routes (No restrictions)
Route::get('/cbt/portal', CbtPortalStart::class)->name('cbt.portal');
Route::get('/cbt/portal/{attempt}', CbtPortalTake::class)->name('cbt.portal.take');

Route::get('/cbt/student', CbtPortalStart::class)->name('cbt.student');
Route::get('/cbt/student/{attempt}', CbtPortalTake::class)->name('cbt.student.take');

// Fresh CSRF token endpoint — used by JS logout to prevent 419 Page Expired
Route::get('/csrf-token', [UtilityController::class, 'csrfToken']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')  // 5 attempts per 1 minute per IP
        ->name('login.store');
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
    ->middleware('student.session')
    ->name('student.homework');

Route::get('/student/exams', \App\Livewire\Student\Exams::class)
    ->middleware('student.session')
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

        Route::get('/students/create', StudentsForm::class)->name('students.create');
        Route::get('/students/{student}/edit', StudentsForm::class)->name('students.edit');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::patch('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::post('/teachers/{teacher}/photo', [TeacherController::class, 'updatePhoto'])->name('teachers.photo');
        Route::post('/teachers/{teacher}/allocations', [TeacherController::class, 'storeAllocation'])->name('teachers.allocations.store');
        Route::delete('/teachers/{teacher}/allocations/{allocation}', [TeacherController::class, 'destroyAllocation'])->name('teachers.allocations.destroy');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

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
        Route::get('/imports/subjects', ImportsSubjects::class)->name('imports.subjects');

        Route::get('/results/bulk-report-cards', [BulkReportCardsController::class, 'index'])
            ->middleware('permission:results.publish')
            ->name('results.bulk-report-cards');
        Route::post('/results/bulk-report-cards', [BulkReportCardsController::class, 'generate'])
            ->middleware('permission:results.publish')
            ->name('results.bulk-report-cards.generate');

        Route::post('/settings/school', [SettingsController::class, 'updateSchool'])->name('settings.update-school');
        Route::post('/settings/results', [SettingsController::class, 'updateResults'])->name('settings.update-results');
        Route::post('/settings/certificates', [SettingsController::class, 'updateCertificates'])->name('settings.update-certificates');
    });

    Route::get('/students', StudentsIndex::class)->name('students.index');
    Route::get('/students/export', [StudentsExportController::class, 'export'])->name('students.export');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/admission-form', [AdmissionFormController::class, 'download'])->name('students.admission-form');

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
        Route::get('/cbt', CbtIndex::class)
            ->name('cbt.index');
        Route::get('/cbt/exams/{exam}', CbtExamEditor::class)
            ->name('cbt.exams.edit');
        Route::get('/cbt/exams/{exam}/pdf', [CbtExportController::class, 'examPdf'])
            ->name('cbt.exams.pdf');
        Route::get('/cbt/sample-download', [UtilityController::class, 'cbtSampleDownload'])
            ->name('cbt.sample-download');

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

        Route::get('/homework', HomeworkIndex::class)->name('homework.index');
        Route::get('/homework/{id}/submissions', \App\Livewire\Homework\Submissions::class)->name('homework.submissions');

        
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
        ->name('results.report-card');

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
    });
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
        Route::resource('tenants', \App\Http\Controllers\SuperAdmin\TenantController::class)->except(['show']);

        // Subscription
        Route::post('tenants/{tenant}/subscription', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'updateSubscription'])->name('tenants.subscription');

        // Feature flags
        Route::post('tenants/{tenant}/features',     [\App\Http\Controllers\SuperAdmin\TenantController::class, 'updateFeatureFlags'])->name('tenants.features');

        // Broadcast
        Route::post('broadcast',                     [\App\Http\Controllers\SuperAdmin\TenantController::class, 'broadcast'])->name('broadcast');

        // Reset school data
        Route::post('tenants/{tenant}/reset',        [\App\Http\Controllers\SuperAdmin\TenantController::class, 'resetData'])->name('tenants.reset');

        // Health check
        Route::get('tenants/{tenant}/health',        [\App\Http\Controllers\SuperAdmin\TenantController::class, 'health'])->name('tenants.health');

        // Auto-suspend
        Route::post('auto-suspend',                  [\App\Http\Controllers\SuperAdmin\TenantController::class, 'autoSuspend'])->name('auto-suspend');

        // Force password reset for all users
        Route::post('tenants/{tenant}/force-reset',  [\App\Http\Controllers\SuperAdmin\TenantController::class, 'forcePasswordReset'])->name('tenants.force-reset');

        // Reset admin password directly
        Route::post('tenants/{tenant}/reset-admin-password', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'resetAdminPassword'])->name('tenants.reset-admin-password');

        // Backup
        Route::post('tenants/{tenant}/backup',       [\App\Http\Controllers\SuperAdmin\TenantController::class, 'triggerBackup'])->name('tenants.backup');

        // Clone
        Route::post('tenants/{tenant}/clone',        [\App\Http\Controllers\SuperAdmin\TenantController::class, 'clone'])->name('tenants.clone');

        // Global user search
        Route::get('users/search',                   [\App\Http\Controllers\SuperAdmin\TenantController::class, 'searchUsers'])->name('users.search');

        // Billing overview
        Route::get('billing',                        [\App\Http\Controllers\SuperAdmin\BillingController::class, 'index'])->name('billing');
        Route::post('tenants/{tenant}/payment',      [\App\Http\Controllers\SuperAdmin\BillingController::class, 'recordPayment'])->name('tenants.payment');
    });
});
