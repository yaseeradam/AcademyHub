<?php

namespace App\Http\Controllers;

use App\Models\ResultPublication;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\Audit;
use App\Support\ReportCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class BulkReportCardsController extends Controller
{
    public function index(): \Illuminate\Http\Response
    {
        $user = request()->user();
        abort_unless($user?->hasPermission('results.publish'), 403);

        $classes = SchoolClass::query()->orderBy('level')->orderBy('name')->get();

        return response()->view('pages.results.bulk-report-cards', compact('classes'));
    }

    public function getStudents(SchoolClass $class): \Illuminate\Http\JsonResponse
    {
        $students = $class->students()
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'admission_number']);

        return response()->json($students);
    }

    public function generate(Request $request, ReportCardService $service): BinaryFileResponse|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->hasPermission('results.publish'), 403);

        set_time_limit(0);

        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'session' => ['required', 'string', 'max:9', 'regex:/^\\d{4}\\/\\d{4}$/'],
            'term' => ['required', 'integer', 'between:1,3'],
            'template' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $classId = (int) $data['class_id'];
        $session = (string) $data['session'];
        $term = (int) $data['term'];
        $studentIds = $data['student_ids'] ?? [];
        $safeSession = str_replace('/', '-', $session);

        $query = Student::query()
            ->where('class_id', $classId)
            ->where('status', 'Active');
            
        if (!empty($studentIds)) {
            $query->whereIn('id', $studentIds);
        }

        $students = $query->orderBy('last_name')->get();

        if ($students->isEmpty()) {
            return back()->withErrors(['class_id' => 'No students selected or found in the selected class.']);
        }

        $published = ResultPublication::query()
            ->where('class_id', $classId)
            ->where('term', $term)
            ->where('session', $session)
            ->whereNotNull('published_at')
            ->exists();

        if (!$published) {
            return back()->withErrors(['class_id' => 'Results are not published for this class / term / session. Publish results first.']);
        }

        $tmpDir = storage_path('app/_bulk_report_cards/' . Str::random(12));
        $pdfDir = $tmpDir . DIRECTORY_SEPARATOR . 'pdfs';
        File::ensureDirectoryExists($pdfDir);

        $timestamp = now()->format('Y-m-d_H-i-s');
        $zipDir = storage_path('app/report-cards');
        File::ensureDirectoryExists($zipDir);
        $zipPath = $zipDir . DIRECTORY_SEPARATOR . "report_cards_class{$classId}_{$safeSession}_T{$term}_{$timestamp}.zip";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            File::deleteDirectory($tmpDir);
            abort(500, 'Unable to create zip archive.');
        }

        try {
            foreach ($students as $student) {
                $optionsOverrides = $request->input('options', []);
                $optionsOverrides = [
                    'show_psychomotor' => isset($optionsOverrides['show_psychomotor']),
                    'show_attendance' => isset($optionsOverrides['show_attendance']),
                    'show_position' => isset($optionsOverrides['show_position']),
                    'show_class_average' => isset($optionsOverrides['show_class_average']),
                ];

                $payload = $service->build($student, $term, $session, $optionsOverrides);

                $template = $request->filled('template') ? $request->input('template') : (string) config('myacademy.report_card_template', 'standard');
                $view = match ($template) {
                    'compact' => 'pdf.report-card-compact',
                    'elegant' => 'pdf.report-card-elegant',
                    'modern' => 'pdf.report-card-modern',
                    'classic' => 'pdf.report-card-classic',
                    'vibrant' => 'pdf.report-card-vibrant',
                    'professional' => 'pdf.report-card-professional',
                    'royal' => 'pdf.report-card-royal',
                    'fresh' => 'pdf.report-card-fresh',
                    'sunset' => 'pdf.report-card-sunset',
                    default => 'pdf.report-card',
                };

                $pdf = Pdf::loadView($view, [
                    ...$payload,
                ])->setPaper('a4');

                $safeAdm = preg_replace('/[^A-Za-z0-9\\-_.]+/', '-', (string) $student->admission_number) ?: (string) $student->id;
                $filename = "report-card-{$safeAdm}-{$safeSession}-T{$term}.pdf";
                $path = $pdfDir . DIRECTORY_SEPARATOR . $filename;
                File::put($path, $pdf->output());

                $zip->addFile($path, $filename);
            }

            $zip->close();
        } catch (\Throwable $e) {
            $zip->close();
            throw $e;
        } finally {
            File::deleteDirectory($tmpDir);
        }

        Audit::log('results.bulk_report_cards_generated', null, [
            'class_id' => $classId,
            'term' => $term,
            'session' => $session,
            'count' => (int) $students->count(),
        ]);

        return response()->download($zipPath)->deleteFileAfterSend(true)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function preview(Request $request, ReportCardService $service): \Illuminate\Http\Response
    {
        $user = $request->user();
        abort_unless($user?->hasPermission('results.publish'), 403);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'session' => ['required', 'string', 'max:9'],
            'term' => ['required', 'integer', 'between:1,3'],
            'template' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $session = $data['session'];
        $term = (int) $data['term'];

        $optionsOverrides = $request->input('options', []);
        $optionsOverrides = [
            'show_psychomotor' => isset($optionsOverrides['show_psychomotor']),
            'show_attendance' => isset($optionsOverrides['show_attendance']),
            'show_position' => isset($optionsOverrides['show_position']),
            'show_class_average' => isset($optionsOverrides['show_class_average']),
        ];

        $payload = $service->build($student, $term, $session, $optionsOverrides);

        $template = $request->filled('template') ? $request->input('template') : (string) config('myacademy.report_card_template', 'standard');
        $view = match ($template) {
            'compact' => 'pdf.report-card-compact',
            'elegant' => 'pdf.report-card-elegant',
            'modern' => 'pdf.report-card-modern',
            'classic' => 'pdf.report-card-classic',
            'vibrant' => 'pdf.report-card-vibrant',
            'professional' => 'pdf.report-card-professional',
            'royal' => 'pdf.report-card-royal',
            'fresh' => 'pdf.report-card-fresh',
            'sunset' => 'pdf.report-card-sunset',
            default => 'pdf.report-card',
        };

        $pdf = Pdf::loadView($view, [
            ...$payload,
        ])->setPaper('a4');

        return $pdf->stream("preview-report-card-{$student->id}.pdf");
    }
}
