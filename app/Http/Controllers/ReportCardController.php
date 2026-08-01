<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Student;
use App\Models\SubjectAllocation;
use App\Models\ResultPublication;
use App\Support\Audit;
use App\Support\ReportCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportCardController extends Controller
{
    public function download(Request $request, Student $student): Response
    {
        $user = $request->user();
        $tenant = $user ? $user->tenant : null;

        if ($tenant && $tenant->isSubscriptionExpired()) {
            abort(403, 'Your school\'s subscription has expired. Please contact school administration.');
        }

        if ($user->role === 'parent') {
            abort_unless($user->students()->where('students.id', $student->id)->exists(), 403);

            // Check if child's class is allowed by student-dashboard
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
            abort_unless($user && $user->hasPermission('results.broadsheet'), 403);

            if ($user->role === 'teacher') {
                $allowed = SubjectAllocation::query()
                    ->where('teacher_id', $user->id)
                    ->where('class_id', $student->class_id)
                    ->exists();

                abort_unless($allowed, 403);
            }
        }

        $term = (int) $request->query('term', 1);
        $session = (string) $request->query('session', $this->defaultSession());

        abort_unless($term >= 1 && $term <= 3, 422);
        abort_unless(preg_match('/^\d{4}\/\d{4}$/', $session) === 1, 422);

        if ($user->role === 'teacher' || $user->role === 'parent') {
            $published = ResultPublication::query()
                ->where('class_id', $student->class_id)
                ->where('term', $term)
                ->where('session', $session)
                ->whereNotNull('published_at')
                ->exists();

            abort_unless($published, 403);
        }

        $student->load(['schoolClass', 'section']);

        $template = (string) config('academyhub.report_card_template', 'compact');
        $safeTemplate = preg_replace('/[^a-z0-9_-]/i', '', $template) ?: 'compact';
        $sessionSlug = str_replace('/', '-', $session);
        $storageDir = storage_path("app/public/report-cards/{$sessionSlug}/T{$term}/{$safeTemplate}");
        $filename = 'report-card-' . $student->admission_number . '-' . $sessionSlug . '-T' . $term . '.pdf';
        $fullPath = "{$storageDir}/{$filename}";

        // Cache invalidation: regenerate when scores, settings, student details, or attendance change.
        $lastScoreAt = \App\Models\Score::where('student_id', $student->id)
            ->where('term', $term)
            ->where('session', $session)
            ->max('updated_at');
        $lastScoreTimestamp = $lastScoreAt ? \Carbon\Carbon::parse($lastScoreAt)->timestamp : null;

        $lastAttendanceAt = \App\Models\AttendanceMark::where('student_id', $student->id)->max('updated_at');
        $lastAttendanceTimestamp = $lastAttendanceAt ? \Carbon\Carbon::parse($lastAttendanceAt)->timestamp : null;

        $settingsPath = storage_path('app/academyhub/tenants/' . $student->tenant_id . '/settings.json');
        if (!file_exists($settingsPath)) {
            $settingsPath = storage_path('app/academyhub/settings.json');
        }
        $settingsTimestamp = file_exists($settingsPath) ? filemtime($settingsPath) : null;

        $studentTimestamp = $student->updated_at ? $student->updated_at->timestamp : null;

        $useCache = false;
        if (file_exists($fullPath) && app()->environment() !== 'testing') {
            $cacheTimestamp = filemtime($fullPath);
            $viewPath = resource_path('views/' . str_replace('.', '/', $view) . '.blade.php');
            if (file_exists($viewPath) && $cacheTimestamp < filemtime($viewPath)) {
                $stale = true;
            }

            if ($lastScoreTimestamp !== null && $cacheTimestamp < $lastScoreTimestamp) {
                $stale = true;
            }

            if ($lastAttendanceTimestamp !== null && $cacheTimestamp < $lastAttendanceTimestamp) {
                $stale = true;
            }

            if ($settingsTimestamp !== null && $cacheTimestamp < $settingsTimestamp) {
                $stale = true;
            }

            if ($studentTimestamp !== null && $cacheTimestamp < $studentTimestamp) {
                $stale = true;
            }

            if (!$stale) {
                $useCache = true;
            }
        }

        if ($useCache) {
            Audit::log('results.report_card_downloaded_from_cache', $student, [
                'term' => $term,
                'session' => $session,
            ]);

            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        $data = app(ReportCardService::class)->build($student, $term, $session);

        $view = ReportCardService::viewForTemplate($template);

        $pdf = Pdf::loadView($view, [
            ...$data,
        ])->setPaper('a4', 'portrait')->setOptions([
            'dpi' => 72,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'defaultFont' => 'dejavu sans',
        ]);

        $output = $pdf->output();

        // Write the PDF cache file for subsequent millisecond serving
        \Illuminate\Support\Facades\File::ensureDirectoryExists($storageDir);
        @file_put_contents($fullPath, $output);

        Audit::log('results.report_card_downloaded', $student, [
            'term' => $term,
            'session' => $session,
        ]);

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function defaultSession(): string
    {
        $active = AcademicSession::activeName();
        if ($active) {
            return $active;
        }

        $year = (int) now()->format('Y');
        $next = $year + 1;

        return "{$year}/{$next}";
    }
}
