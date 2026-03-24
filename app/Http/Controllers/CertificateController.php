<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Support\CertificatePdf;
use Illuminate\Http\Response;

class CertificateController extends Controller
{
    public function download(Certificate $certificate): Response
    {
        $user = auth()->user();
        abort_unless($user, 403);

        abort_unless(in_array($user->role, ['admin', 'teacher'], true), 403);

        $certificate->load(['student.schoolClass', 'student.section', 'issuer']);

        $student = $certificate->student;

        $templateViews = [
            'modern' => 'pdf.certificate',
            'classic' => 'pdf.certificate-classic',
            'elegant' => 'pdf.certificate-elegant',
            'vibrant' => 'pdf.certificate-vibrant',
            'minimal' => 'pdf.certificate-minimal',
            'royal' => 'pdf.certificate-royal',
            'prestige' => 'pdf.certificate-prestige',
            'botanical' => 'pdf.certificate-botanical',
            'aurora' => 'pdf.certificate-aurora',
            'heritage' => 'pdf.certificate-heritage',
            'obsidian' => 'pdf.certificate-obsidian',
            'sahara' => 'pdf.certificate-sahara',
            'oceanic' => 'pdf.certificate-oceanic',
            'crimson' => 'pdf.certificate-crimson',
            'ivory' => 'pdf.certificate-ivory',
        ];

        $template = $certificate->template ?: (string) config('myacademy.certificate_template', 'modern');
        $view = $templateViews[$template] ?? 'pdf.certificate';

        $orientation = (string) config('myacademy.certificate_orientation', 'landscape');
        $orientation = in_array($orientation, ['landscape', 'portrait'], true) ? $orientation : 'landscape';

        $pdfContent = CertificatePdf::fromView($view, [
            'certificate' => $certificate,
            'student' => $student,
        ], $orientation);

        $filename = 'certificate-' . $certificate->serial_number . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
