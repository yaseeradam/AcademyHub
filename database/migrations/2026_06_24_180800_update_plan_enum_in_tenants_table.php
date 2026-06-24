<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter enum column to include 'basic'
        DB::statement("ALTER TABLE tenants MODIFY COLUMN plan ENUM('free', 'basic', 'pro', 'enterprise') NOT NULL DEFAULT 'basic'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY COLUMN plan ENUM('free', 'pro', 'enterprise') NOT NULL DEFAULT 'free'");
    }
};
