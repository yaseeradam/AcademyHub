<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class AdmissionFormController extends Controller
{
    public function download(Student $student)
    {
        $pdf = Pdf::loadView('pdf.admission-form', compact('student'));
        return $pdf->download("admission-form-{$student->admission_number}.pdf")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
