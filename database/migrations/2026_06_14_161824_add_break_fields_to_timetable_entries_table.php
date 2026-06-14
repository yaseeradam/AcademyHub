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
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->change();
            $table->boolean('is_break')->default(false);
            $table->string('break_text')->nullable();
            $table->string('color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable(false)->change();
            $table->dropColumn(['is_break', 'break_text', 'color']);
        });
    }
};
