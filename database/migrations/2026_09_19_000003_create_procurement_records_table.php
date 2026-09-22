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
        if (!Schema::hasTable('procurement_records')) {
            Schema::create('procurement_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->string('item_name');
                $table->string('category')->default('General')->index();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('unit')->default('pcs');
                $table->decimal('unit_price', 12, 2)->nullable();
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->date('purchased_at')->index();
                $table->unsignedBigInteger('purchaser_id')->nullable()->index();
                $table->string('purchaser_name')->nullable();
                $table->string('vendor_name')->nullable();
                $table->string('vendor_phone')->nullable();
                $table->string('payment_method')->default('Cash');
                $table->string('receipt_number')->nullable();
                $table->string('receipt_attachment_path')->nullable();
                $table->string('status')->default('completed'); // completed, pending_approval, reimbursed
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('purchaser_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

                $table->index(['tenant_id', 'purchased_at'], 'procurement_tenant_purchased_idx');
                $table->index(['tenant_id', 'category'], 'procurement_tenant_category_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_records');
    }
};
