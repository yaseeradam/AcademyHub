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
use Illuminate\Http\Response;

class ReportCardController extends Controller
{
    public function download(Request $request, Student $student): Response
    {
        $user = $request->user();

        if ($user->role === 'parent') {
            abort_unless($user->students()->where('students.id', $student->id)->exists(), 403);
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

        $data = app(ReportCardService::class)->build($student, $term, $session);

        $template = (string) config('myacademy.report_card_template', 'compact');
        $view = match ($template) {
            'compact' => 'pdf.report-card-compact',
            'elegant' => 'pdf.report-card-elegant',
            'modern' => 'pdf.report-card-modern',
            'classic' => 'pdf.report-card-classic',
            'aurora' => 'pdf.report-card-aurora',
            'heritage' => 'pdf.report-card-heritage',
            'nordic' => 'pdf.report-card-nordic',
            'vanguard' => 'pdf.report-card-vanguard',
            'signature' => 'pdf.report-card-signature',
            default => 'pdf.report-card-compact',
        };

        $pdf = Pdf::loadView($view, [
            ...$data,
        ])->setPaper('a4');

        $filename = 'report-card-' . $student->admission_number . '-' . $session . '-T' . $term . '.pdf';

        Audit::log('results.report_card_downloaded', $student, [
            'term' => $term,
            'session' => $session,
        ]);

        return response($pdf->output(), 200, [
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
