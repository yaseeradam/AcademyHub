<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cbt_answers', 'awarded_marks')) {
            Schema::table('cbt_answers', function (Blueprint $table) {
                $table->unsignedSmallInteger('awarded_marks')->nullable()->after('text_answer');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cbt_answers', 'awarded_marks')) {
            Schema::table('cbt_answers', function (Blueprint $table) {
                $table->dropColumn('awarded_marks');
            });
        }
    }
};
