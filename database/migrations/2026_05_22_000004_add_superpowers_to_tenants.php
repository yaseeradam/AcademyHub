<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->json('feature_flags')->nullable()->after('settings');
            $table->unsignedInteger('max_disk_usage_mb')->default(500)->after('feature_flags');
            $table->text('active_broadcast_banner')->nullable()->after('max_disk_usage_mb');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['feature_flags', 'max_disk_usage_mb', 'active_broadcast_banner']);
        });
    }
};
