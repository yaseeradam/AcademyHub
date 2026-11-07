<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Student;
use App\Models\SubjectAllocation;
use App\Support\Audit;
use App\Support\StudentDossierService;
use App\Support\TenantSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class ReportBookletController extends Controller
{
    public function download(Request $request, Student $student): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        $tenant = $user->tenant;
        if ($tenant && $tenant->isSubscriptionExpired()) {
            abort(403, 'Your school\'s subscription has expired. Please contact school administration.');
        }

        if ($user->role === 'parent') {
            abort_unless($user->students()->where('students.id', $student->id)->exists(), 403);

            if ($tenant) {
                $dbComponent = $tenant->activeMarketplaceComponents()->where('slug', 'student-dashboard')->first();
                if (!$dbComponent) {
                    abort(403, 'Student Dashboard is not active.');
                }
                $allowedClassIds = $dbComponent->pivot->allowed_class_ids ?? [];
                if (is_string($allowedClassIds)) {
                    $allowedClassIds = json_decode($allowedClassIds, true) ?: [];
                }
                $allowedClassIds = is_array($allowedClassIds) ? $allowedClassIds : [];
                abort_unless(in_array($student->class_id, $allowedClassIds), 403, 'Student Dashboard is not active for your child\'s class.');
            }
        } else {
            abort_unless($user->hasPermission('results.broadsheet') || in_array($user->role, ['admin', 'proprietor'], true), 403);

            if ($user->role === 'teacher') {
                $allowed = SubjectAllocation::query()
                    ->where('teacher_id', $user->id)
                    ->where('class_id', $student->class_id)
                    ->exists();

                abort_unless($allowed, 403);
            }
        }

        $session = (string) $request->query('session', $this->defaultSession());
        abort_unless(preg_match('/^\d{4}\/\d{4}$/', $session) === 1, 422, 'Invalid session format.');

        $isParentOrStudent = in_array($user->role, ['parent', 'student'], true);

        if ($isParentOrStudent) {
            // Check publication: must have at least one term published for this student's class
            $publishedTerms = \App\Models\ResultPublication::publishedTermsForClass($student->class_id, $session);
            if (empty($publishedTerms)) {
                abort(403, 'Academic results for this session have not yet been published by the school administration.');
            }

            // Check debtor gate
            $blockDebtors = (bool) TenantSettings::get('rc_block_debtors', true);
            if ($blockDebtors) {
                $ledger = \App\Support\BillingService::getStudentTermLedger($student, $session);
                if (($ledger['total_balance'] ?? 0.0) > 0) {
                    abort(403, 'Academic report booklet access is restricted due to outstanding school fees. Please settle the balance with the bursary/accounts department.');
                }
            }
        }

        $student->load(['schoolClass', 'section']);
        $service = app(StudentDossierService::class);

        $safeSession = str_replace('/', '-', $session);
        $safeAdmissionNo = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) ($student->admission_number ?? $student->id));
        $filename = "academic-booklet-{$safeAdmissionNo}-{$safeSession}.pdf";
        $storageDir = storage_path('app/academyhub/report_booklets');
        $fullPath = "{$storageDir}/{$filename}";

        $coverViewPath = resource_path('views/pdf/partials/book-cover.blade.php');
        $ledgerViewPath = resource_path('views/pdf/partials/book-dossier-ledger.blade.php');
        $coverTimestamp = file_exists($coverViewPath) ? filemtime($coverViewPath) : null;
        $ledgerTimestamp = file_exists($ledgerViewPath) ? filemtime($ledgerViewPath) : null;
        $viewTimestamp = max((int) $coverTimestamp, (int) $ledgerTimestamp);

        // Cache Invalidation Check
        $lastScoreAt = \App\Models\Score::where('student_id', $student->id)
            ->where('session', $session)
            ->max('updated_at');
        $lastScoreTimestamp = $lastScoreAt ? Carbon::parse($lastScoreAt)->timestamp : null;

        $settingsPath = TenantSettings::settingsPath();
        $settingsTimestamp = file_exists($settingsPath) ? filemtime($settingsPath) : null;
        $studentTimestamp = $student->updated_at ? $student->updated_at->timestamp : null;

        $useCache = false;
        if (file_exists($fullPath) && app()->environment() !== 'testing' && !$request->has('nocache')) {
            $cacheTimestamp = filemtime($fullPath);
            $stale = false;

            if ($lastScoreTimestamp !== null && $cacheTimestamp < $lastScoreTimestamp) {
                $stale = true;
            }
            if ($settingsTimestamp !== null && $cacheTimestamp < $settingsTimestamp) {
                $stale = true;
            }
            if ($studentTimestamp !== null && $cacheTimestamp < $studentTimestamp) {
                $stale = true;
            }
            if ($viewTimestamp > 0 && $cacheTimestamp < $viewTimestamp) {
                $stale = true;
            }

            if (!$stale) {
                $useCache = true;
            }
        }

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        if ($useCache && file_exists($fullPath)) {
            Audit::log('results.report_booklet_downloaded_from_cache', $student, [
                'session' => $session,
            ]);

            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        $compiledHtml = $service->compileBookletHtml($student, $session, $request->all(), $isParentOrStudent);

        if ($request->boolean('html')) {
            return response($compiledHtml, 200, ['Content-Type' => 'text/html']);
        }

        $pdf = Pdf::loadHTML($compiledHtml)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 72,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => false,
                'defaultFont' => 'dejavu sans',
            ]);

        $output = $pdf->output();

        // Write disk cache for subsequent rapid requests
        File::ensureDirectoryExists(dirname($fullPath));
        @file_put_contents($fullPath, $output);

        Audit::log('results.report_booklet_downloaded', $student, [
            'session' => $session,
        ]);

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function preview(Request $request, Student $student): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        $session = (string) $request->query('session', $this->defaultSession());
        abort_unless(preg_match('/^\d{4}\/\d{4}$/', $session) === 1, 422, 'Invalid session format.');

        $isParentOrStudent = in_array($user->role, ['parent', 'student'], true);

        if ($isParentOrStudent) {
            $publishedTerms = \App\Models\ResultPublication::publishedTermsForClass($student->class_id, $session);
            if (empty($publishedTerms)) {
                abort(403, 'Academic results for this session have not yet been published by the school administration.');
            }

            $blockDebtors = (bool) TenantSettings::get('rc_block_debtors', true);
            if ($blockDebtors) {
                $ledger = \App\Support\BillingService::getStudentTermLedger($student, $session);
                if (($ledger['total_balance'] ?? 0.0) > 0) {
                    abort(403, 'Academic report booklet access is restricted due to outstanding school fees. Please settle the balance with the bursary/accounts department.');
                }
            }
        }

        $service = app(StudentDossierService::class);
        $compiledHtml = $service->compileBookletHtml($student, $session, $request->all(), $isParentOrStudent);

        if ($request->boolean('html')) {
            return response($compiledHtml, 200, ['Content-Type' => 'text/html']);
        }

        $pdf = Pdf::loadHTML($compiledHtml)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 72,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => false,
                'defaultFont' => 'dejavu sans',
            ]);

        return $pdf->stream("preview-booklet-{$student->id}.pdf");
    }

    private function defaultSession(): string
    {
        return AcademicSession::activeName() ?: (now()->format('Y') . '/' . (now()->format('Y') + 1));
    }
}
