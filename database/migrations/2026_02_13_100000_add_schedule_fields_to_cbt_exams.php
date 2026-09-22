<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cbt_exams')) {
            return;
        }

        Schema::table('cbt_exams', function (Blueprint $table) {
            if (Schema::hasColumn('cbt_exams', 'starts_at')) {
                return;
            }

            $table->timestamp('starts_at')->nullable()->after('published_at');
            $table->timestamp('ends_at')->nullable()->after('starts_at');

            $table->index(['starts_at', 'ends_at'], 'cbt_exams_schedule_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cbt_exams')) {
            return;
        }

        Schema::table('cbt_exams', function (Blueprint $table) {
            if (! Schema::hasColumn('cbt_exams', 'starts_at')) {
                return;
            }

            $table->dropIndex('cbt_exams_schedule_index');
            $table->dropColumn('ends_at');
            $table->dropColumn('starts_at');
        });
    }
};

