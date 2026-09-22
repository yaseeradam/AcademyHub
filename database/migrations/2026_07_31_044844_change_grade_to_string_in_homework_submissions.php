<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Changes homework_submissions.grade from decimal(5,2) to varchar(20)
     * so that letter grades (A, A+, B, EXCELLENT, etc.) can be stored.
     */
    public function up(): void
    {
        // First set any existing decimal grade values to null to avoid conversion errors
        // (unlikely to have data at this point)
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->string('grade', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->decimal('grade', 5, 2)->nullable()->change();
        });
    }
};
