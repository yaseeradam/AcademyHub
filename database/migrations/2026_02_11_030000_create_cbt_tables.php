<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cbt_exams')) {
            Schema::create('cbt_exams', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();

                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();

                $table->unsignedTinyInteger('term')->nullable();
                $table->string('session', 9)->nullable();
                $table->unsignedSmallInteger('duration_minutes')->default(30);

                $table->string('status', 20)->default('draft'); // draft|submitted|approved|rejected
                $table->string('access_code', 32)->nullable()->unique();

                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('submitted_at')->nullable();

                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('note')->nullable();
                $table->timestamp('published_at')->nullable();

                $table->timestamps();

                $table->index(['status', 'submitted_at']);
                $table->index(['class_id', 'subject_id']);
                $table->index(['created_by', 'status']);
            });
        }

        if (! Schema::hasTable('cbt_questions')) {
            Schema::create('cbt_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_id')->constrained('cbt_exams')->cascadeOnDelete();
                $table->string('type', 20)->default('mcq'); // mcq
                $table->text('prompt');
                $table->unsignedSmallInteger('marks')->default(1);
                $table->unsignedSmallInteger('position')->default(1);
                $table->timestamps();

                $table->index(['exam_id', 'position']);
            });
        }

        if (! Schema::hasTable('cbt_options')) {
            Schema::create('cbt_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('cbt_questions')->cascadeOnDelete();
                $table->text('label');
                $table->boolean('is_correct')->default(false);
                $table->unsignedTinyInteger('position')->default(1);
                $table->timestamps();

                $table->index(['question_id', 'position']);
            });
        }

        if (! Schema::hasTable('cbt_attempts')) {
            Schema::create('cbt_attempts', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('exam_id')->constrained('cbt_exams')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->unsignedInteger('score')->default(0);
                $table->unsignedInteger('max_score')->default(0);
                $table->decimal('percent', 5, 2)->default(0);

                $table->timestamps();

                $table->unique(['exam_id', 'student_id'], 'cbt_attempts_exam_student_unique');
                $table->index(['exam_id', 'submitted_at']);
            });
        }

        if (! Schema::hasTable('cbt_answers')) {
            Schema::create('cbt_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attempt_id')->constrained('cbt_attempts')->cascadeOnDelete();
                $table->foreignId('question_id')->constrained('cbt_questions')->cascadeOnDelete();
                $table->foreignId('option_id')->nullable()->constrained('cbt_options')->nullOnDelete();
                $table->boolean('is_correct')->nullable();
                $table->timestamps();

                $table->unique(['attempt_id', 'question_id'], 'cbt_answers_attempt_question_unique');
                $table->index(['attempt_id', 'is_correct']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_answers');
        Schema::dropIfExists('cbt_attempts');
        Schema::dropIfExists('cbt_options');
        Schema::dropIfExists('cbt_questions');
        Schema::dropIfExists('cbt_exams');
    }
};

