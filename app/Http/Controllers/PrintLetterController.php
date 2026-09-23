<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class PrintLetterController extends Controller
{
    public function printAdmissionLetters(Request $request)
    {
        $rawIds = $request->query('ids', '');
        $ids = array_filter(array_map('intval', explode(',', $rawIds)));

        if (empty($ids)) {
            abort(404, 'No student IDs specified.');
        }

        $students = Student::with(['schoolClass', 'section'])->whereIn('id', $ids)->get();

        $academicSession = $request->query('session', '2026/2027');
        $resumptionDate  = $request->query('resumption', '14th September, 2026');
        $admissionDate   = $request->query('date', now()->format('jS F, Y'));
        $signatoryName   = $request->query('signatory', "Prof. Murtala Ahmed Rufa'i");
        $signatoryTitle  = $request->query('title', 'Executive Director');

        $schoolName = config('academyhub.school_name');
        if (empty($schoolName) || $schoolName === 'AcademyHub') {
            $schoolName = 'AI INTEGRATED ACADEMY ARGUNGU';
        }

        $schoolMotto = config('academyhub.school_motto') ?: 'Learning Today Leading Tomorrow';
        $schoolAddress = config('academyhub.school_address') ?: "Behind Buben Ta'Ololo's Residence, Tudun Wada, Argungu, Kebbi State";
        $schoolPhone = config('academyhub.school_phone') ?: '08069676697, 07034784861';
        $schoolEmail = config('academyhub.school_email') ?: 'alijabaintegratedacademyarg@gmail.com';
        $logoUrl = config('academyhub.school_logo') ?: '/logo.jpg';

        return view('print.admission-letters', compact(
            'students',
            'academicSession',
            'resumptionDate',
            'admissionDate',
            'signatoryName',
            'signatoryTitle',
            'schoolName',
            'schoolMotto',
            'schoolAddress',
            'schoolPhone',
            'schoolEmail',
            'logoUrl'
        ));
    }
}
