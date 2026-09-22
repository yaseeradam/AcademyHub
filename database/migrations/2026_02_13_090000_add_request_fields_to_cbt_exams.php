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
            if (Schema::hasColumn('cbt_exams', 'assigned_teacher_id')) {
                return;
            }

            $table->foreignId('assigned_teacher_id')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('requested_by')
                ->nullable()
                ->after('assigned_teacher_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('requested_at')->nullable()->after('requested_by');
            $table->text('request_note')->nullable()->after('requested_at');

            $table->index(['assigned_teacher_id', 'status'], 'cbt_exams_assigned_teacher_status_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cbt_exams')) {
            return;
        }

        Schema::table('cbt_exams', function (Blueprint $table) {
            if (! Schema::hasColumn('cbt_exams', 'assigned_teacher_id')) {
                return;
            }

            $table->dropIndex('cbt_exams_assigned_teacher_status_index');

            $table->dropConstrainedForeignId('requested_by');
            $table->dropColumn('requested_at');
            $table->dropColumn('request_note');
            $table->dropConstrainedForeignId('assigned_teacher_id');
        });
    }
};

