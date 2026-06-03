<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds:
     * - fee_structures.installment_plans  — JSON column storing which payment plans the school enables per fee row
     * - transactions.installment_plan     — which plan the parent chose (full|two_installments|monthly)
     * - transactions.installment_number   — which installment number this payment is (1, 2, 3…)
     */
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Example: {"full":true,"two_installments":true,"monthly":false}
            $table->json('installment_plans')->nullable()->after('amount_due');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('installment_plan', 30)->nullable()->after('payment_method');
            $table->unsignedTinyInteger('installment_number')->nullable()->after('installment_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn('installment_plans');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['installment_plan', 'installment_number']);
        });
    }
};
