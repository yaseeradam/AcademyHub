<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class AdmissionFormController extends Controller
{
    public function download(Student $student)
    {
        $user = auth()->user();
        if ($user?->role === 'parent') {
            $isMyChild = $student->parents()->where('users.id', $user->id)->exists();
            abort_unless($isMyChild, 403, 'Unauthorized access to student record.');
        }

        $pdf = Pdf::loadView('pdf.admission-form', compact('student'));
        return $pdf->download("admission-form-{$student->admission_number}.pdf")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
