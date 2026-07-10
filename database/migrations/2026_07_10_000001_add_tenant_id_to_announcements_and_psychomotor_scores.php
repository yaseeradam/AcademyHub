<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('announcements', 'tenant_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
        }

        if (!Schema::hasColumn('psychomotor_scores', 'tenant_id')) {
            Schema::table('psychomotor_scores', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('announcements', 'tenant_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasColumn('psychomotor_scores', 'tenant_id')) {
            Schema::table('psychomotor_scores', function (Blueprint $table) {
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
