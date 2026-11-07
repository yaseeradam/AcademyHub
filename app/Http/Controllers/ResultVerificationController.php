<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ResultPublication;
use App\Models\Student;
use App\Support\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultVerificationController extends Controller
{
    /**
     * Public endpoint to verify the authenticity of printed academic report cards and booklets.
     */
    public function verify(Request $request, string $admission): View
    {
        $cleanAdmission = trim(urldecode($admission));

        // Locate student across admission number variations
        $student = Student::query()
            ->with(['schoolClass', 'section'])
            ->where('admission_number', $cleanAdmission)
            ->first();

        if (!$student) {
            return view('pages.results.verify', [
                'found'            => false,
                'admissionNumber'  => $cleanAdmission,
                'schoolName'       => config('academyhub.school_name', config('app.name', 'AcademyHub')),
            ]);
        }

        $session = AcademicSession::activeName() ?: (now()->format('Y') . '/' . (now()->format('Y') + 1));

        // Retrieve published terms for student's class
        $publishedTerms = ResultPublication::publishedTermsForClass($student->class_id, $session);

        // Generate deterministic anti-tamper authenticity seal
        $hashSeed = ($student->tenant_id ?? '0') . '|' . $student->id . '|' . $student->admission_number . '|' . $student->created_at?->timestamp;
        $digitalSeal = 'SEAL-' . strtoupper(substr(hash('sha256', $hashSeed), 0, 12));

        $schoolName = TenantSettings::get('school_name', config('academyhub.school_name', config('app.name', 'AcademyHub')));
        $schoolLogo = TenantSettings::get('school_logo', config('academyhub.school_logo'));
        $schoolMotto = TenantSettings::get('school_motto', config('academyhub.school_motto', 'Excellence and Integrity'));

        return view('pages.results.verify', [
            'found'                => true,
            'student'              => $student,
            'session'              => $session,
            'publishedTerms'       => $publishedTerms,
            'digitalSeal'          => $digitalSeal,
            'schoolName'           => $schoolName,
            'schoolLogo'           => $schoolLogo,
            'schoolMotto'          => $schoolMotto,
            'verifiedAt'           => now(),
        ]);
    }
}
