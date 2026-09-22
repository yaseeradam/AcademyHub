<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_attendance_marks', function (Blueprint $table) {
            // Dedicated time columns for sign-in and sign-out punches
            $table->time('punch_in_time')->nullable()->after('note');
            $table->time('punch_out_time')->nullable()->after('punch_in_time');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_attendance_marks', function (Blueprint $table) {
            $table->dropColumn(['punch_in_time', 'punch_out_time']);
        });
    }
};
