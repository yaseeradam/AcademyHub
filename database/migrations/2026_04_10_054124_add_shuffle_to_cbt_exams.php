<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_exams', function (Blueprint $table) {
            if (! Schema::hasColumn('cbt_exams', 'shuffle_questions')) {
                $table->boolean('shuffle_questions')->default(false)->after('show_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cbt_exams', function (Blueprint $table) {
            $table->dropColumn('shuffle_questions');
        });
    }
};
