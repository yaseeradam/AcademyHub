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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // field key
            $table->string('label'); // display label
            $table->enum('type', ['text', 'number', 'date', 'select', 'textarea', 'checkbox']);
            $table->boolean('required')->default(false);
            $table->json('options')->nullable(); // for select fields
            $table->string('placeholder')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};