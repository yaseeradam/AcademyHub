<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            if (Schema::hasTable('teacher_attendance_marks')) {
                Schema::table('teacher_attendance_marks', function (Blueprint $table) {
                    $table->dropForeign(['teacher_id']);
                    $table->foreign('teacher_id')
                        ->references('id')
                        ->on('users')
                        ->cascadeOnDelete();
                });
            }

            if (Schema::hasTable('weekly_data_collections')) {
                Schema::table('weekly_data_collections', function (Blueprint $table) {
                    $table->dropForeign(['teacher_id']);
                    $table->foreign('teacher_id')
                        ->references('id')
                        ->on('users')
                        ->cascadeOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            if (Schema::hasTable('teacher_attendance_marks')) {
                Schema::table('teacher_attendance_marks', function (Blueprint $table) {
                    $table->dropForeign(['teacher_id']);
                    $table->foreign('teacher_id')
                        ->references('id')
                        ->on('users')
                        ->restrictOnDelete();
                });
            }

            if (Schema::hasTable('weekly_data_collections')) {
                Schema::table('weekly_data_collections', function (Blueprint $table) {
                    $table->dropForeign(['teacher_id']);
                    $table->foreign('teacher_id')
                        ->references('id')
                        ->on('users')
                        ->restrictOnDelete();
                });
            }
        }
    }
};
