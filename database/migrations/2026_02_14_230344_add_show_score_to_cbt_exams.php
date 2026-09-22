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
            if (Schema::hasColumn('cbt_exams', 'show_score')) {
                return;
            }

            $table->boolean('show_score')->default(false)->after('allowed_cidrs');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cbt_exams')) {
            return;
        }

        Schema::table('cbt_exams', function (Blueprint $table) {
            if (! Schema::hasColumn('cbt_exams', 'show_score')) {
                return;
            }

            $table->dropColumn('show_score');
        });
    }
};
