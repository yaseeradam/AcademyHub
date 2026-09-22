<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('from_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('to_class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('to_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('promoted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('promoted_at');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'promoted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};

