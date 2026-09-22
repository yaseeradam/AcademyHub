<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_data_collections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();

            $table->unsignedTinyInteger('term')->default(1);
            $table->string('session', 9);

            $table->date('week_start');
            $table->date('week_end');

            $table->unsignedSmallInteger('boys_present')->default(0);
            $table->unsignedSmallInteger('boys_absent')->default(0);
            $table->unsignedSmallInteger('girls_present')->default(0);
            $table->unsignedSmallInteger('girls_absent')->default(0);

            $table->unsignedTinyInteger('school_days')->nullable();
            $table->string('note', 500)->nullable();

            $table->enum('status', ['submitted', 'approved', 'rejected'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('rejection_note', 500)->nullable();

            $table->timestamps();

            $table->unique(['class_id', 'section_id', 'term', 'session', 'week_start'], 'weekly_data_unique');
            $table->index(['teacher_id', 'submitted_at']);
            $table->index(['class_id', 'section_id', 'term', 'session', 'week_start'], 'weekly_data_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_data_collections');
    }
};

