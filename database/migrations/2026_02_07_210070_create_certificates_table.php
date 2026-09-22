<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('type', 50)->default('General');
            $table->string('title');
            $table->text('body');
            $table->date('issued_on');
            $table->string('serial_number', 50)->unique();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'issued_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

