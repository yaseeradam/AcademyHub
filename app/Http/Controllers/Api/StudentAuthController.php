<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'admission_number' => 'required|string',
            'password'         => 'required|string',
            'device_name'      => 'nullable|string',
        ]);

        $tenantId = TenantSettings::tenantId();

        if (!$tenantId) {
            throw ValidationException::withMessages([
                'admission_number' => ['Tenant context not resolved. Please specify school domain or X-Tenant-Slug header.'],
            ]);
        }

        $student = Student::where('admission_number', $request->admission_number)
            ->where('tenant_id', $tenantId)
            ->where('status', 'Active')
            ->first();

        if (!$student) {
            throw ValidationException::withMessages([
                'admission_number' => ['Student not found or account is inactive. Please check your admission number.'],
            ]);
        }

        $passwordValid = false;
        if ($student->password) {
            $passwordValid = Hash::check($request->password, $student->password);
            
            // Progressive upgrade: if the database has a legacy unhashed password, upgrade it
            if (!$passwordValid && !str_starts_with($student->password, '$2y$')) {
                $admissionSuffix = substr($student->admission_number, -4);
                $expectedPassword = strtolower($student->first_name) . $admissionSuffix;
                if (hash_equals($expectedPassword, $request->password)) {
                    $passwordValid = true;
                    $student->password = Hash::make($request->password);
                    $student->save();
                }
            }
        } else {
            // Database fallback: if password column is empty, check and upgrade
            $admissionSuffix = substr($student->admission_number, -4);
            $expectedPassword = strtolower($student->first_name) . $admissionSuffix;
            if (hash_equals($expectedPassword, $request->password)) {
                $passwordValid = true;
                $student->password = Hash::make($request->password);
                $student->save();
            }
        }

        if (!$passwordValid) {
            throw ValidationException::withMessages([
                'password' => ['Invalid password. Please try again or contact your teacher.'],
            ]);
        }

        $deviceName = $request->device_name ?? 'student_mobile_app';
        $token = $student->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'student' => [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'full_name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class_id' => $student->class_id,
                'class_name' => $student->schoolClass?->name,
                'section_id' => $student->section_id,
                'section_name' => $student->section?->name,
                'passport_photo_url' => $student->passport_photo_url,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
