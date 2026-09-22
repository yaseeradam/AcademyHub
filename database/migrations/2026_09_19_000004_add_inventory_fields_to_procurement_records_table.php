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
        if (Schema::hasTable('procurement_records')) {
            Schema::table('procurement_records', function (Blueprint $table) {
                if (!Schema::hasColumn('procurement_records', 'is_inventory_item')) {
                    $table->boolean('is_inventory_item')->default(false)->after('receipt_attachment_path')->index();
                }
                if (!Schema::hasColumn('procurement_records', 'inventory_location')) {
                    $table->string('inventory_location')->nullable()->after('is_inventory_item')->index();
                }
                if (!Schema::hasColumn('procurement_records', 'inventory_status')) {
                    $table->string('inventory_status')->default('in_use')->after('inventory_location')->index();
                }
                if (!Schema::hasColumn('procurement_records', 'assigned_to')) {
                    $table->string('assigned_to')->nullable()->after('inventory_status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('procurement_records')) {
            Schema::table('procurement_records', function (Blueprint $table) {
                $columns = ['is_inventory_item', 'inventory_location', 'inventory_status', 'assigned_to'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('procurement_records', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
