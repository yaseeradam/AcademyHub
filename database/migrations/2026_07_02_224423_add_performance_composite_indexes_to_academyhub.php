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
        if (Schema::hasTable('scores')) {
            Schema::table('scores', function (Blueprint $table) {
                $table->index(['tenant_id', 'class_id', 'term', 'session'], 'scores_tenant_class_term_session_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('scores')) {
            Schema::table('scores', function (Blueprint $table) {
                $table->dropIndex('scores_tenant_class_term_session_index');
            });
        }
    }
};
