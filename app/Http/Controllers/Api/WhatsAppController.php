<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function getParent($phone)
    {
        $parent = \App\Models\User::where('whatsapp_phone', $phone)
            ->where('role', 'parent')
            ->with('students.schoolClass')
            ->first();

        if (!$parent) {
            return response()->json(['success' => false, 'message' => 'Parent not found'], 404);
        }

        return response()->json(['success' => true, 'parent' => $parent]);
    }

    public function registerParent(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'admission_number' => 'required',
            'phone' => 'required'
        ]);

        $parent = \App\Models\User::where('email', $request->email)->where('role', 'parent')->first();
        if (!$parent) {
            return response()->json(['success' => false, 'message' => 'Parent email not found'], 404);
        }

        $student = \App\Models\Student::where('admission_number', $request->admission_number)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        if (!$parent->students()->where('student_id', $student->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Student not linked to this parent email'], 403);
        }

        $otp = rand(100000, 999999);
        \Illuminate\Support\Facades\Cache::put('whatsapp_otp_' . $request->phone, [
            'parent_id' => $parent->id,
            'otp' => $otp
        ], now()->addMinutes(10));

        return response()->json(['success' => true, 'otp' => $otp]);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required'
        ]);

        $cached = \Illuminate\Support\Facades\Cache::get('whatsapp_otp_' . $request->phone);
        if (!$cached || $cached['otp'] != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 400);
        }

        $parent = \App\Models\User::find($cached['parent_id']);
        $parent->whatsapp_phone = $request->phone;
        $parent->whatsapp_verified = true;
        $parent->whatsapp_subscribed = true;
        $parent->save();

        \Illuminate\Support\Facades\Cache::forget('whatsapp_otp_' . $request->phone);

        $parent->load('students.schoolClass');

        return response()->json(['success' => true, 'message' => 'Registration successful', 'parent' => $parent]);
    }

    public function getAttendance($parentId)
    {
        $parent = \App\Models\User::findOrFail($parentId);
        $students = $parent->students()->with(['attendanceMarks' => function ($q) {
            $q->whereHas('sheet', function ($sq) {
                $sq->whereDate('date', today());
            });
        }])->get();

        return response()->json(['success' => true, 'students' => $students]);
    }

    public function getResults($parentId)
    {
        $parent = \App\Models\User::findOrFail($parentId);
        $students = $parent->students()->with(['scores' => function($q) {
            $q->latest()->limit(5)->with('subject');
        }])->get();

        return response()->json(['success' => true, 'students' => $students]);
    }

    public function getFees($parentId)
    {
        $parent = \App\Models\User::findOrFail($parentId);
        // Getting due transactions or fee structures
        // We'll return empty data for now or a simple summary
        return response()->json(['success' => true, 'data' => []]);
    }
}
