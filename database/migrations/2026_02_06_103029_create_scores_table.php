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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->unsignedTinyInteger('term');
            $table->string('session', 9);
            $table->unsignedTinyInteger('ca1')->default(0);
            $table->unsignedTinyInteger('ca2')->default(0);
            $table->unsignedTinyInteger('exam')->default(0);
            $table->unsignedTinyInteger('total')->default(0);
            $table->string('grade', 2)->nullable();
            $table->unsignedSmallInteger('position')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'class_id', 'term', 'session']);
            $table->index(['class_id', 'term', 'session']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
