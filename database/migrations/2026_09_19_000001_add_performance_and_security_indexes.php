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
        if (Schema::hasTable('event_rsvps') && Schema::hasColumn('event_rsvps', 'tenant_id')) {
            Schema::table('event_rsvps', function (Blueprint $table) {
                $table->index('tenant_id', 'event_rsvps_tenant_id_index');
            });
        }

        if (Schema::hasTable('cbt_attempts') && Schema::hasColumn('cbt_attempts', 'assigned_teacher_id')) {
            Schema::table('cbt_attempts', function (Blueprint $table) {
                $table->index('assigned_teacher_id', 'cbt_attempts_assigned_teacher_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('event_rsvps')) {
            Schema::table('event_rsvps', function (Blueprint $table) {
                $table->dropIndex('event_rsvps_tenant_id_index');
            });
        }

        if (Schema::hasTable('cbt_attempts')) {
            Schema::table('cbt_attempts', function (Blueprint $table) {
                $table->dropIndex('cbt_attempts_assigned_teacher_id_index');
            });
        }
    }
};
