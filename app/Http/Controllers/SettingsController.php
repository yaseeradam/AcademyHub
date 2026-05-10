<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Support\CertificatePdf;
use App\Support\TenantSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;

class SettingsController extends Controller
{
    private const CERTIFICATE_TEMPLATES = ['modern', 'classic', 'elegant', 'vibrant', 'minimal', 'royal', 'obsidian', 'sahara', 'oceanic', 'crimson', 'ivory'];
    private const REPORT_CARD_TEMPLATES = ['standard', 'compact', 'elegant', 'modern', 'classic', 'vibrant', 'professional', 'royal', 'fresh', 'sunset'];

    private function settingsPath(): string
    {
        return TenantSettings::settingsPath();
    }

    private function settingsCacheKey(): string
    {
        return TenantSettings::settingsCacheKey();
    }

    private function loadSettings(string $settingsPath): array
    {
        if (! File::exists($settingsPath)) {
            return [];
        }

        $existing = json_decode(File::get($settingsPath), true);

        return is_array($existing) ? $existing : [];
    }

    private function persistSettings(string $settingsPath, array $settings): void
    {
        File::ensureDirectoryExists(dirname($settingsPath));
        File::put($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function refreshSettingsCache(): void
    {
        Cache::forget($this->settingsCacheKey());
    }

    public function updateSchool(Request $request)
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_address' => ['nullable', 'string', 'max:255'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'school_email' => ['nullable', 'email', 'max:255'],
            'school_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $settingsPath = $this->settingsPath();
        $settings = $this->loadSettings($settingsPath);

        $settings['school_name'] = $data['school_name'];
        $settings['school_address'] = $data['school_address'] ?? null;
        $settings['school_phone'] = $data['school_phone'] ?? null;
        $settings['school_email'] = $data['school_email'] ?? null;

        if ($request->hasFile('school_logo')) {
            $file = $request->file('school_logo');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'school-logo-' . now()->format('YmdHis') . '.' . $ext;
            $path = $file->storeAs(TenantSettings::uploadsSubdir('school-assets'), $filename, 'uploads');
            $path = str_replace('\\', '/', (string) $path);

            $old = $settings['school_logo'] ?? null;
            if ($old && $old !== $path) {
                Storage::disk('uploads')->delete($old);
            }

            $settings['school_logo'] = $path;
        }

        $this->persistSettings($settingsPath, $settings);
        
        $this->refreshSettingsCache();
        
        // Force config reload by clearing config cache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return back()->with('status', 'School settings saved.');
    }

    public function updateResults(Request $request)
    {
        $data = $request->validate([
            'results_ca1_max' => ['required', 'integer', 'min:0', 'max:200'],
            'results_ca2_max' => ['required', 'integer', 'min:0', 'max:200'],
            'results_exam_max' => ['required', 'integer', 'min:0', 'max:200'],
        ]);

        $total = (int) $data['results_ca1_max'] + (int) $data['results_ca2_max'] + (int) $data['results_exam_max'];
        if ($total <= 0) {
            throw ValidationException::withMessages([
                'results_ca1_max' => 'At least one assessment component must be greater than 0.',
            ]);
        }

        $settingsPath = $this->settingsPath();
        $settings = $this->loadSettings($settingsPath);

        $settings['results_ca1_max'] = (int) $data['results_ca1_max'];
        $settings['results_ca2_max'] = (int) $data['results_ca2_max'];
        $settings['results_exam_max'] = (int) $data['results_exam_max'];

        $this->persistSettings($settingsPath, $settings);
        
        $this->refreshSettingsCache();
        
        // Force config reload by clearing config cache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return back()->with('status', 'Result scoring marks saved.');
    }

    public function updateCertificates(Request $request)
    {
        $data = $request->validate([
            'certificate_orientation' => ['required', 'string', 'in:landscape,portrait'],
            'certificate_border_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'certificate_accent_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'certificate_show_logo' => ['required', 'boolean'],
            'certificate_show_watermark' => ['required', 'boolean'],
            'certificate_watermark_image' => ['nullable', 'image', 'max:4096'],
            'certificate_watermark_remove' => ['nullable', 'boolean'],

            'certificate_signature_label' => ['nullable', 'string', 'max:50'],
            'certificate_signature_name' => ['nullable', 'string', 'max:100'],
            'certificate_signature_image' => ['nullable', 'image', 'max:4096'],
            'certificate_signature_remove' => ['nullable', 'boolean'],

            'certificate_signature2_label' => ['nullable', 'string', 'max:50'],
            'certificate_signature2_name' => ['nullable', 'string', 'max:100'],
            'certificate_signature2_image' => ['nullable', 'image', 'max:4096'],
            'certificate_signature2_remove' => ['nullable', 'boolean'],

            'certificate_default_type' => ['nullable', 'string', 'max:50'],
            'certificate_default_title' => ['nullable', 'string', 'max:255'],
            'certificate_default_body' => ['nullable', 'string', 'max:8000'],
            'certificate_template' => ['required', 'string', 'in:' . implode(',', self::CERTIFICATE_TEMPLATES)],
        ]);

        $settingsPath = $this->settingsPath();
        $settings = $this->loadSettings($settingsPath);

        $settings['certificate_orientation'] = $data['certificate_orientation'];
        $settings['certificate_border_color'] = $data['certificate_border_color'];
        $settings['certificate_accent_color'] = $data['certificate_accent_color'];
        $settings['certificate_show_logo'] = $request->has('certificate_show_logo') && $request->input('certificate_show_logo') == '1';
        $settings['certificate_show_watermark'] = $request->has('certificate_show_watermark') && $request->input('certificate_show_watermark') == '1';

        // All templates are now free
        $settings['certificate_template'] = (string) $data['certificate_template'];

        if ($request->boolean('certificate_watermark_remove')) {
            $old = $settings['certificate_watermark_image'] ?? null;
            if ($old) {
                Storage::disk('uploads')->delete((string) $old);
            }
            $settings['certificate_watermark_image'] = null;
        }

        if ($request->hasFile('certificate_watermark_image')) {
            $file = $request->file('certificate_watermark_image');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'certificate-watermark-' . now()->format('YmdHis') . '.' . $ext;
            $path = $file->storeAs(TenantSettings::uploadsSubdir('school-assets'), $filename, 'uploads');
            $path = str_replace('\\', '/', (string) $path);

            $old = $settings['certificate_watermark_image'] ?? null;
            if ($old && $old !== $path) {
                Storage::disk('uploads')->delete((string) $old);
            }

            $settings['certificate_watermark_image'] = $path;
        }

        $settings['certificate_signature_label'] = $data['certificate_signature_label'] ?: null;
        $settings['certificate_signature_name'] = $data['certificate_signature_name'] ?: null;

        if ($request->boolean('certificate_signature_remove')) {
            $old = $settings['certificate_signature_image'] ?? null;
            if ($old) {
                Storage::disk('uploads')->delete((string) $old);
            }
            $settings['certificate_signature_image'] = null;
        }

        if ($request->hasFile('certificate_signature_image')) {
            $file = $request->file('certificate_signature_image');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'certificate-signature-' . now()->format('YmdHis') . '.' . $ext;
            $path = $file->storeAs(TenantSettings::uploadsSubdir('school-assets'), $filename, 'uploads');
            $path = str_replace('\\', '/', (string) $path);

            $old = $settings['certificate_signature_image'] ?? null;
            if ($old && $old !== $path) {
                Storage::disk('uploads')->delete((string) $old);
            }

            $settings['certificate_signature_image'] = $path;
        }

        $settings['certificate_signature2_label'] = $data['certificate_signature2_label'] ?: null;
        $settings['certificate_signature2_name'] = $data['certificate_signature2_name'] ?: null;

        if ($request->boolean('certificate_signature2_remove')) {
            $old = $settings['certificate_signature2_image'] ?? null;
            if ($old) {
                Storage::disk('uploads')->delete((string) $old);
            }
            $settings['certificate_signature2_image'] = null;
        }

        if ($request->hasFile('certificate_signature2_image')) {
            $file = $request->file('certificate_signature2_image');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'certificate-signature2-' . now()->format('YmdHis') . '.' . $ext;
            $path = $file->storeAs(TenantSettings::uploadsSubdir('school-assets'), $filename, 'uploads');
            $path = str_replace('\\', '/', (string) $path);

            $old = $settings['certificate_signature2_image'] ?? null;
            if ($old && $old !== $path) {
                Storage::disk('uploads')->delete((string) $old);
            }

            $settings['certificate_signature2_image'] = $path;
        }

        $settings['certificate_default_type'] = $data['certificate_default_type'] ?: null;
        $settings['certificate_default_title'] = $data['certificate_default_title'] ?: null;
        $settings['certificate_default_body'] = $data['certificate_default_body'] ?: null;

        $this->persistSettings($settingsPath, $settings);
        
        $this->refreshSettingsCache();
        
        // Force config reload by clearing config cache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return back()->with('status', 'Certificate settings saved.');
    }

    public function showTemplates()
    {
        $classes = SchoolClass::query()->orderBy('level')->orderBy('name')->get();
        return view('pages.settings.templates', compact('classes'));
    }

    public function updateTemplates(Request $request)
    {
        try {
            $data = $request->validate([
                'report_card_template' => ['required', 'string', 'in:' . implode(',', self::REPORT_CARD_TEMPLATES)],
                // Toggle options
                'rc_show_position' => ['nullable', 'boolean'],
                'rc_show_attendance' => ['nullable', 'boolean'],
                'rc_show_grading_key' => ['nullable', 'boolean'],
                'rc_show_class_average' => ['nullable', 'boolean'],
                'rc_show_watermark' => ['nullable', 'boolean'],
                'rc_show_next_term_date' => ['nullable', 'boolean'],
                'rc_show_teacher_remarks' => ['nullable', 'boolean'],
                'rc_show_principal_remarks' => ['nullable', 'boolean'],
                'rc_show_psychomotor' => ['nullable', 'boolean'],
                'rc_show_school_fees' => ['nullable', 'boolean'],
                'rc_school_fees_account_number' => ['nullable', 'string', 'max:50'],
                'rc_school_fees_bank_name' => ['nullable', 'string', 'max:100'],
                'rc_school_fees_account_name' => ['nullable', 'string', 'max:150'],
                'rc_school_fees_by_class' => ['nullable', 'array'],
                'rc_school_fees_by_class.*' => ['nullable', 'numeric', 'min:0'],
                'rc_show_signatures' => ['nullable', 'boolean'],
                'rc_principal_signature_image' => ['nullable', 'image', 'max:2048'],
                'rc_teacher_signature_image' => ['nullable', 'image', 'max:2048'],
                'rc_principal_signature_remove' => ['nullable', 'boolean'],
                'rc_teacher_signature_remove' => ['nullable', 'boolean'],
            ]);

            $settingsPath = $this->settingsPath();
            $settings = $this->loadSettings($settingsPath);

            // All templates are now free
            $settings['report_card_template'] = (string) $data['report_card_template'];

            // Boolean toggles - explicitly handle unchecked state
            $boolKeys = [
                'rc_show_position', 'rc_show_attendance', 'rc_show_grading_key',
                'rc_show_class_average', 'rc_show_watermark', 'rc_show_next_term_date',
                'rc_show_teacher_remarks', 'rc_show_principal_remarks',
                'rc_show_psychomotor', 'rc_show_school_fees', 'rc_show_signatures',
            ];
            foreach ($boolKeys as $key) {
                // A hidden input with value="0" is always submitted; the checkbox
                // with value="1" is only submitted when checked. So has($key)==true
                // always — we compare input($key) directly to '1'.
                $settings[$key] = $request->input($key) === '1';
            }

            // School fees details
            $settings['rc_school_fees_account_number'] = $data['rc_school_fees_account_number'] ?? null;
            $settings['rc_school_fees_bank_name'] = $data['rc_school_fees_bank_name'] ?? null;
            $settings['rc_school_fees_account_name'] = $data['rc_school_fees_account_name'] ?? null;

            // School fees per class (stored as JSON object)
            if (isset($data['rc_school_fees_by_class']) && is_array($data['rc_school_fees_by_class'])) {
                $fees = [];
                foreach ($data['rc_school_fees_by_class'] as $classId => $amount) {
                    if ($amount !== null && $amount !== '') {
                        $fees[(string) $classId] = (float) $amount;
                    }
                }
                $settings['rc_school_fees_by_class'] = !empty($fees) ? $fees : null;
            }

            // Principal signature image
            if ($request->boolean('rc_principal_signature_remove')) {
                $old = $settings['rc_principal_signature_image'] ?? null;
                if ($old) {
                    Storage::disk('uploads')->delete((string) $old);
                }
                $settings['rc_principal_signature_image'] = null;
            }
            if ($request->hasFile('rc_principal_signature_image')) {
                $file = $request->file('rc_principal_signature_image');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'rc-principal-signature-' . now()->format('YmdHis') . '.' . $ext;
                $path = $file->storeAs(TenantSettings::uploadsSubdir('school-assets'), $filename, 'uploads');
                $path = str_replace('\\', '/', (string) $path);
                $old = $settings['rc_principal_signature_image'] ?? null;
                if ($old && $old !== $path) {
                    Storage::disk('uploads')->delete((string) $old);
                }
                $settings['rc_principal_signature_image'] = $path;
            }

            // Teacher signature image
            if ($request->boolean('rc_teacher_signature_remove')) {
                $old = $settings['rc_teacher_signature_image'] ?? null;
                if ($old) {
                    Storage::disk('uploads')->delete((string) $old);
                }
                $settings['rc_teacher_signature_image'] = null;
            }
            if ($request->hasFile('rc_teacher_signature_image')) {
                $file = $request->file('rc_teacher_signature_image');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'rc-teacher-signature-' . now()->format('YmdHis') . '.' . $ext;
                $path = $file->storeAs(TenantSettings::uploadsSubdir('school-assets'), $filename, 'uploads');
                $path = str_replace('\\', '/', (string) $path);
                $old = $settings['rc_teacher_signature_image'] ?? null;
                if ($old && $old !== $path) {
                    Storage::disk('uploads')->delete((string) $old);
                }
                $settings['rc_teacher_signature_image'] = $path;
            }

            $this->persistSettings($settingsPath, $settings);

            $this->refreshSettingsCache();

            return back()->with('status', 'Report card settings saved.');
            
        } catch (\Exception $e) {
            \Log::error('Template settings update error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Failed to save settings: ' . $e->getMessage()]);
        }
    }

    public function previewTemplate(string $type, string $template): \Illuminate\Http\Response
    {
        $type = strtolower(trim($type));
        $template = strtolower(trim($template));

        if ($type === 'certificate') {
            abort_unless(in_array($template, self::CERTIFICATE_TEMPLATES, true), 404);

            $view = match ($template) {
                'classic' => 'pdf.certificate-classic',
                'elegant' => 'pdf.certificate-elegant',
                'vibrant' => 'pdf.certificate-vibrant',
                'minimal' => 'pdf.certificate-minimal',
                'royal' => 'pdf.certificate-royal',
                'obsidian' => 'pdf.certificate-obsidian',
                'sahara' => 'pdf.certificate-sahara',
                'oceanic' => 'pdf.certificate-oceanic',
                'crimson' => 'pdf.certificate-crimson',
                'ivory' => 'pdf.certificate-ivory',
                default => 'pdf.certificate',
            };

            $student = new Fluent([
                'full_name' => 'Jane Doe',
                'admission_number' => 'ADM/001',
                'schoolClass' => new Fluent(['name' => 'JSS 1']),
                'section' => new Fluent(['name' => 'A']),
            ]);

            $certificate = new Fluent([
                'title' => 'Certificate of Achievement',
                'type' => 'General',
                'body' => 'This certificate is proudly presented to {student_name} for outstanding performance and dedication.',
                'serial_number' => 'CERT-0001',
                'issued_on' => Carbon::now(),
            ]);

            $orientation = (string) config('myacademy.certificate_orientation', 'landscape');
            $orientation = in_array($orientation, ['landscape', 'portrait'], true) ? $orientation : 'landscape';

            $data = [
                'certificate' => $certificate,
                'student' => $student,
            ];

            if (request()->boolean('html')) {
                return response(view($view, $data));
            }

            $pdfContent = CertificatePdf::fromView($view, $data, $orientation);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="certificate-template-preview.pdf"',
            ]);
        }

        if ($type === 'report-card') {
            abort_unless(in_array($template, self::REPORT_CARD_TEMPLATES, true), 404);

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

            $student = new Fluent([
                'full_name' => 'Jane Doe',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'admission_number' => 'ADM/001',
                'schoolClass' => new Fluent(['name' => 'JSS 1']),
                'section' => new Fluent(['name' => 'A']),
            ]);

            $rows = collect([
                ['subject' => new Fluent(['name' => 'Mathematics']), 'ca1' => 18, 'ca2' => 19, 'exam' => 55, 'total' => 92, 'grade' => 'A'],
                ['subject' => new Fluent(['name' => 'English']), 'ca1' => 16, 'ca2' => 18, 'exam' => 50, 'total' => 84, 'grade' => 'A'],
                ['subject' => new Fluent(['name' => 'Basic Science']), 'ca1' => 15, 'ca2' => 14, 'exam' => 49, 'total' => 78, 'grade' => 'B'],
                ['subject' => new Fluent(['name' => 'Social Studies']), 'ca1' => 17, 'ca2' => 15, 'exam' => 45, 'total' => 77, 'grade' => 'B'],
                ['subject' => new Fluent(['name' => 'Computer Studies']), 'ca1' => 19, 'ca2' => 18, 'exam' => 52, 'total' => 89, 'grade' => 'A'],
            ]);

            $grandTotal = (int) $rows->sum(fn($r) => (int) ($r['total'] ?? 0));
            $subjectCount = max(1, (int) $rows->count());
            $average = round($grandTotal / $subjectCount, 2);

            $data = [
                'student' => $student,
                'term' => 1,
                'session' => '2025/2026',
                'rows' => $rows,
                'grandTotal' => $grandTotal,
                'average' => $average,
                'position' => 1,
                'classAverage' => $average,
                'highestAverage' => 95,
                'lowestAverage' => 65,
                'totalStudents' => 35,
                'timesOpened' => 65,
                'timesPresent' => 60,
                'timesAbsent' => 5,
                'teacherRemarks' => 'An excellent student with outstanding academic performance. Keep it up!',
                'principalRemarks' => 'A commendable result. Continue to strive for excellence.',
                'nextTermDate' => 'September 8, 2025',
                'rcOptions' => [
                    'show_position' => (bool) config('myacademy.rc_show_position', true),
                    'show_attendance' => (bool) config('myacademy.rc_show_attendance', true),
                    'show_grading_key' => (bool) config('myacademy.rc_show_grading_key', true),
                    'show_class_average' => (bool) config('myacademy.rc_show_class_average', true),
                    'show_watermark' => (bool) config('myacademy.rc_show_watermark', true),
                    'show_next_term_date' => (bool) config('myacademy.rc_show_next_term_date', true),
                    'show_teacher_remarks' => (bool) config('myacademy.rc_show_teacher_remarks', true),
                    'show_principal_remarks' => (bool) config('myacademy.rc_show_principal_remarks', true),
                    'show_psychomotor' => (bool) config('myacademy.rc_show_psychomotor', false),
                    'show_school_fees' => (bool) config('myacademy.rc_show_school_fees', false),
                    'show_signatures' => (bool) config('myacademy.rc_show_signatures', false),
                ],
                'schoolFees' => null,
                'signatureImages' => null,
            ];

            if (request()->boolean('html')) {
                return response(view($view, $data));
            }

            $pdf = Pdf::loadView($view, $data)->setPaper('a4');

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="report-card-template-preview.pdf"',
            ]);
        }

        abort(404);
    }

    // License functionality removed - all features are free
}
