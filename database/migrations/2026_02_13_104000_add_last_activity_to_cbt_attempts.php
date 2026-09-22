<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cbt_attempts')) {
            return;
        }

        Schema::table('cbt_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('cbt_attempts', 'last_activity_at')) {
                return;
            }

            $table->timestamp('last_activity_at')->nullable()->after('started_at');
            $table->index(['exam_id', 'last_activity_at'], 'cbt_attempts_exam_last_activity_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cbt_attempts')) {
            return;
        }

        Schema::table('cbt_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('cbt_attempts', 'last_activity_at')) {
                return;
            }

            $table->dropIndex('cbt_attempts_exam_last_activity_index');
            $table->dropColumn('last_activity_at');
        });
    }
};

