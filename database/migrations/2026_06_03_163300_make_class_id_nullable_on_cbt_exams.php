<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cbt_exams') && Schema::hasColumn('cbt_exams', 'class_id')) {
            Schema::table('cbt_exams', function (Blueprint $table) {
                // Drop foreign key constraint first
                $table->dropForeign(['class_id']);
            });

            Schema::table('cbt_exams', function (Blueprint $table) {
                // Change column to nullable
                $table->foreignId('class_id')->nullable()->change();
            });

            Schema::table('cbt_exams', function (Blueprint $table) {
                // Re-add foreign key constraint
                $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cbt_exams') && Schema::hasColumn('cbt_exams', 'class_id')) {
            Schema::table('cbt_exams', function (Blueprint $table) {
                $table->dropForeign(['class_id']);
            });

            Schema::table('cbt_exams', function (Blueprint $table) {
                $table->foreignId('class_id')->nullable(false)->change();
            });

            Schema::table('cbt_exams', function (Blueprint $table) {
                $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            });
        }
    }
};
