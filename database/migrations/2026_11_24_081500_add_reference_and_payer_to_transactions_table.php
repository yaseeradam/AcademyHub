<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('transactions', 'reference')) {
                    $table->string('reference', 255)->nullable()->after('receipt_number');
                }
                if (! Schema::hasColumn('transactions', 'payer_name')) {
                    $table->string('payer_name', 255)->nullable()->after('reference');
                }
                if (! Schema::hasColumn('transactions', 'notes')) {
                    $table->text('notes')->nullable()->after('payer_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'notes')) {
                    $table->dropColumn('notes');
                }
                if (Schema::hasColumn('transactions', 'payer_name')) {
                    $table->dropColumn('payer_name');
                }
                if (Schema::hasColumn('transactions', 'reference')) {
                    $table->dropColumn('reference');
                }
            });
        }
    }
};
