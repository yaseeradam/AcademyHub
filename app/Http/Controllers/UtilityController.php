<?php

namespace App\Http\Controllers;

use App\Models\ResultPublication;
use App\Models\Student;
use App\Models\AcademicTerm;
use App\Support\ReportCardService;
use App\Support\TenantSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UtilityController extends Controller
{
    public function welcome()
    {
        if (config('academyhub.mode') === 'cbt') {
            return redirect()->route('cbt.student');
        }

        return redirect()->route('login');
    }

    public function home()
    {
        if (config('academyhub.mode') === 'cbt') {
            return redirect()->route('cbt.student');
        }

        return auth()->check()
            ? redirect()->route('dashboard')
            : redirect()->route('login');
    }

    public function csrfToken()
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function dashboard()
    {
        $user = auth()->user();
        \Illuminate\Support\Facades\Log::debug('UtilityController: Dashboard hit', [
            'auth_check' => auth()->check(),
            'user_id' => $user?->id,
            'email' => $user?->email,
            'role' => $user?->role,
            'tenant_id' => $user?->tenant_id,
            'resolved_tenant_id' => \App\Support\TenantSettings::tenantId(),
        ]);

        if ($user?->role === 'admin') {
            return view('pages.dashboard');
        }
        if ($user?->role === 'teacher') {
            return view('pages.dashboard-teacher');
        }
        if ($user?->role === 'parent') {
            return redirect()->route('parents.dashboard');
        }
        if ($user?->role === 'bursar') {
            return view('pages.dashboard-bursar');
        }

        return view('pages.dashboard');
    }

    public function studentReportCard(Request $request, ReportCardService $reportCardService)
    {
        $studentId = session('student_id');
        abort_unless($studentId, 403);

        $student = Student::with(['schoolClass', 'section'])->find($studentId);
        abort_unless($student, 403);

        $term = (int) $request->query('term', AcademicTerm::activeTermNumber());
        $session = (string) $request->query('session', AcademicTerm::activeSessionName() ?? '');

        abort_unless($term >= 1 && $term <= 3, 422);

        $published = ResultPublication::where('class_id', $student->class_id)
            ->where('term', $term)->where('session', $session)
            ->whereNotNull('published_at')->exists();
        abort_unless($published, 403);

        $data = $reportCardService->build($student, $term, $session);

        $template = (string) config('academyhub.report_card_template', 'compact');
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
            'riverdale', 'riverdale-burgundy', 'riverdale-emerald', 'riverdale-purple' => 'pdf.report-card-riverdale',
            'greenwood' => 'pdf.report-card-greenwood',
            default => 'pdf.report-card-compact',
        };

        $pdf = Pdf::loadView($view, $data)->setPaper('a4');
        $filename = 'report-card-' . $student->admission_number . '-' . $session . '-T' . $term . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function cbtSampleDownload()
    {
        $content = "1. What is the powerhouse of the cell?\nA. Nucleus\nB. Mitochondria\nC. Ribosome\nD. Golgi body\nANS: B\nMARKS: 2\n\n2. Which planet is closest to the sun?\nA. Earth\nB. Venus\nC. Mercury\nD. Mars\nANS: C\nMARKS: 1\n\n3. What is the chemical symbol for water?\nA. CO2\nB. H2O\nC. NaCl\nD. O2\nANS: B\nMARKS: 1\n\n4. Define photosynthesis and explain its importance to plants.\nTYPE: theory\nMARKS: 5\n\n5. Describe the water cycle.\nTYPE: theory\nMARKS: 4\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="sample_questions.txt"',
        ]);
    }

    public function settingsDebug()
    {
        $cacheKey = TenantSettings::settingsCacheKey();
        $settingsPath = TenantSettings::settingsPath();

        return response()->json([
            'config_values' => [
                'rc_show_position' => config('academyhub.rc_show_position'),
                'rc_show_attendance' => config('academyhub.rc_show_attendance'),
                'rc_show_next_term_date' => config('academyhub.rc_show_next_term_date'),
                'rc_show_teacher_remarks' => config('academyhub.rc_show_teacher_remarks'),
                'rc_show_principal_remarks' => config('academyhub.rc_show_principal_remarks'),
            ],
            'cache_key' => $cacheKey,
            'cache_key_exists' => Cache::has($cacheKey),
            'settings_file_path' => $settingsPath,
            'settings_file_exists' => file_exists($settingsPath),
            'settings_file_content' => file_exists($settingsPath)
                ? json_decode(file_get_contents($settingsPath), true)
                : null,
        ]);
    }

    public function logClientError(Request $request)
    {
        \Illuminate\Support\Facades\Log::error('CLIENT JS ERROR DETECTED', [
            'message' => $request->input('message'),
            'source' => $request->input('source'),
            'line' => $request->input('line'),
            'col' => $request->input('col'),
            'stack' => $request->input('stack'),
            'url' => $request->input('url'),
            'user_agent' => $request->header('User-Agent'),
            'user' => auth()->check() ? auth()->user()->email : 'guest',
        ]);

        return response()->json(['success' => true]);
    }
}
