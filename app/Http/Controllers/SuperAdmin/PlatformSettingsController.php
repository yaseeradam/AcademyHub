<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Support\PlatformSettings;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    /**
     * Show the platform pricing settings form.
     */
    public function edit()
    {
        $currentFee = PlatformSettings::getStudentTermlyFee();

        return view('superadmin.settings.pricing', compact('currentFee'));
    }

    /**
     * Update the platform pricing settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'student_termly_fee' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        $fee = (float) $request->input('student_termly_fee');
        PlatformSettings::setStudentTermlyFee($fee);

        return back()->with('success', 'Platform pricing settings updated successfully!');
    }
}
