<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_attendance_sheets', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedTinyInteger('term')->default(1);
            $table->string('session', 9);
            $table->foreignId('taken_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['date', 'term', 'session'], 'teacher_attendance_sheet_unique');
            $table->index(['date', 'term', 'session']);
        });

        Schema::create('teacher_attendance_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sheet_id')->constrained('teacher_attendance_sheets')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['Present', 'Absent', 'Late', 'Excused'])->default('Present');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['sheet_id', 'teacher_id'], 'teacher_attendance_mark_unique');
            $table->index(['teacher_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance_marks');
        Schema::dropIfExists('teacher_attendance_sheets');
    }
};

