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
        Schema::table('cbt_attempts', function (Blueprint $table) {
            $table->string('theory_status')->default('pending')->after('submitted_at'); // pending, forwarded, marked, transferred
            $table->unsignedBigInteger('assigned_teacher_id')->nullable()->after('theory_status');
            $table->timestamp('forwarded_at')->nullable()->after('assigned_teacher_id');
            $table->timestamp('marked_at')->nullable()->after('forwarded_at');
            $table->timestamp('transferred_at')->nullable()->after('marked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cbt_attempts', function (Blueprint $table) {
            $table->dropColumn(['theory_status', 'assigned_teacher_id', 'forwarded_at', 'marked_at', 'transferred_at']);
        });
    }
};
