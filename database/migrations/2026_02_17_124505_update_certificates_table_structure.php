<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // No-op.
        //
        // The certificates table schema is defined in `2026_02_07_210070_create_certificates_table.php`.
        // This migration previously attempted a destructive schema change (including dropping columns)
        // and used database-specific functions not supported by SQLite during tests.
        //
        // Kept as a no-op to avoid breaking existing installations.
        if (! Schema::hasTable('certificates')) {
            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op.
    }
};
