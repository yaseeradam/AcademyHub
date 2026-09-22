<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychomotor_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('term');
            $table->string('session');
            $table->json('traits');
            $table->timestamps();

            $table->unique(['student_id', 'class_id', 'term', 'session'], 'psychomotor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psychomotor_scores');
    }
};
