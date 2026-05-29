<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all students where password is null or empty
        $students = DB::table('students')
            ->whereNull('password')
            ->orWhere('password', '')
            ->get();

        foreach ($students as $student) {
            if (empty($student->first_name) || empty($student->admission_number)) {
                continue;
            }

            $admissionSuffix = substr($student->admission_number, -4);
            $defaultPassword = strtolower($student->first_name) . $admissionSuffix;
            $hashedPassword  = Hash::make($defaultPassword);

            DB::table('students')
                ->where('id', $student->id)
                ->update([
                    'password' => $hashedPassword,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op (we cannot un-hash passwords, they will remain hashed)
    }
};
