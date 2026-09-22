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
            if (Schema::hasColumn('cbt_attempts', 'ip_address')) {
                return;
            }

            $table->string('ip_address', 45)->nullable()->after('student_id');
            $table->string('allowed_ip', 45)->nullable()->after('ip_address');

            $table->timestamp('terminated_at')->nullable()->after('submitted_at');
            $table->foreignId('terminated_by')->nullable()->after('terminated_at')->constrained('users')->nullOnDelete();
            $table->text('termination_reason')->nullable()->after('terminated_by');

            $table->index(['exam_id', 'started_at'], 'cbt_attempts_exam_started_at_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cbt_attempts')) {
            return;
        }

        Schema::table('cbt_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('cbt_attempts', 'ip_address')) {
                return;
            }

            $table->dropIndex('cbt_attempts_exam_started_at_index');

            $table->dropColumn('termination_reason');
            $table->dropConstrainedForeignId('terminated_by');
            $table->dropColumn('terminated_at');
            $table->dropColumn('allowed_ip');
            $table->dropColumn('ip_address');
        });
    }
};

