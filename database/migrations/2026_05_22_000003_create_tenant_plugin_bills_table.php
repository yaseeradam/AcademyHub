<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_plugin_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_component_id')->constrained()->cascadeOnDelete();
            $table->string('bill_type'); // 'setup', 'usage'
            $table->string('term_name')->nullable();
            $table->string('session_name')->nullable();
            $table->unsignedInteger('student_count')->nullable();
            $table->decimal('setup_fee', 10, 2)->default(0.00);
            $table->decimal('usage_fee_per_student', 10, 2)->default(0.00);
            $table->decimal('total_due', 10, 2)->default(0.00);
            $table->string('status')->default('unpaid'); // 'unpaid', 'paid', 'void'
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_plugin_bills');
    }
};
