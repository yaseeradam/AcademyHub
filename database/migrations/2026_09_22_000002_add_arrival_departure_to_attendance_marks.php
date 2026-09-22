<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_marks', function (Blueprint $table) {
            // Dedicated arrival and departure time columns for students
            $table->time('arrived_at')->nullable()->after('note');
            $table->time('departed_at')->nullable()->after('arrived_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_marks', function (Blueprint $table) {
            $table->dropColumn(['arrived_at', 'departed_at']);
        });
    }
};
