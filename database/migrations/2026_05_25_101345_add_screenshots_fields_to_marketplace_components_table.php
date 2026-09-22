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
        Schema::table('marketplace_components', function (Blueprint $table) {
            $table->unsignedInteger('screenshot_count')->default(3)->after('is_active');
            $table->json('screenshots_metadata')->nullable()->after('screenshot_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_components', function (Blueprint $table) {
            $table->dropColumn(['screenshot_count', 'screenshots_metadata']);
        });
    }
};
