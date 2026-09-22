<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cbt_answers', 'text_answer')) {
            Schema::table('cbt_answers', function (Blueprint $table) {
                $table->text('text_answer')->nullable()->after('option_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cbt_answers', 'text_answer')) {
            Schema::table('cbt_answers', function (Blueprint $table) {
                $table->dropColumn('text_answer');
            });
        }
    }
};
