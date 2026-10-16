<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Hardware UID assigned on the ZKTeco K40 device for this student.
            // Used to match biometric punches to the correct student record.
            $table->unsignedInteger('k40_uid')->nullable()->after('admission_number');
            $table->index('k40_uid', 'students_k40_uid_index');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_k40_uid_index');
            $table->dropColumn('k40_uid');
        });
    }
};
