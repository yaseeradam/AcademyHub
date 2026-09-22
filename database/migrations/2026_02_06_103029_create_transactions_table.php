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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->enum('type', ['Income', 'Expense']);
            $table->string('category');
            $table->unsignedTinyInteger('term')->nullable();
            $table->string('session', 9)->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_method', ['Cash', 'Transfer', 'POS'])->nullable();
            $table->string('receipt_number')->nullable()->unique();
            $table->date('date');
            $table->timestamps();

            $table->index(['type', 'date']);
            $table->index(['student_id', 'category', 'date']);
            $table->index(['student_id', 'category', 'term', 'session']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
