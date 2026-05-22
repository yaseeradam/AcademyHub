<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_components', function (Blueprint $table) {
            $table->decimal('setup_fee', 10, 2)->default(0.00)->after('pricing_model');
            $table->decimal('usage_fee_per_student', 10, 2)->default(0.00)->after('setup_fee');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_components', function (Blueprint $table) {
            $table->dropColumn(['setup_fee', 'usage_fee_per_student']);
        });
    }
};
