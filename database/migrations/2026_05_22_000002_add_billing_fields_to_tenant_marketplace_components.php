<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_marketplace_components', function (Blueprint $table) {
            $table->decimal('setup_fee', 10, 2)->nullable()->after('price_paid');
            $table->decimal('usage_fee_per_student', 10, 2)->nullable()->after('setup_fee');
            $table->json('allowed_class_ids')->nullable()->after('usage_fee_per_student');
            $table->string('status')->default('active')->after('allowed_class_ids'); // 'active', 'suspended', etc.
        });
    }

    public function down(): void
    {
        Schema::table('tenant_marketplace_components', function (Blueprint $table) {
            $table->dropColumn(['setup_fee', 'usage_fee_per_student', 'allowed_class_ids', 'status']);
        });
    }
};
