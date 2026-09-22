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
            if (Schema::hasColumn('cbt_exams', 'pin')) {
                return;
            }

            $table->string('pin', 20)->nullable()->after('ends_at');
            $table->unsignedSmallInteger('grace_minutes')->default(0)->after('pin');
            $table->text('allowed_cidrs')->nullable()->after('grace_minutes');

            $table->index(['pin'], 'cbt_exams_pin_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cbt_exams')) {
            return;
        }

        Schema::table('cbt_exams', function (Blueprint $table) {
            if (! Schema::hasColumn('cbt_exams', 'pin')) {
                return;
            }

            $table->dropIndex('cbt_exams_pin_index');
            $table->dropColumn('allowed_cidrs');
            $table->dropColumn('grace_minutes');
            $table->dropColumn('pin');
        });
    }
};

