<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('result_publications', function (Blueprint $table) {
            if (! Schema::hasColumn('result_publications', 'ca1_published_at')) {
                $table->timestamp('ca1_published_at')->nullable()->after('published_at');
            }
            if (! Schema::hasColumn('result_publications', 'ca2_published_at')) {
                $table->timestamp('ca2_published_at')->nullable()->after('ca1_published_at');
            }
            if (! Schema::hasColumn('result_publications', 'exam_published_at')) {
                $table->timestamp('exam_published_at')->nullable()->after('ca2_published_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('result_publications', function (Blueprint $table) {
            if (Schema::hasColumn('result_publications', 'exam_published_at')) {
                $table->dropColumn('exam_published_at');
            }
            if (Schema::hasColumn('result_publications', 'ca2_published_at')) {
                $table->dropColumn('ca2_published_at');
            }
            if (Schema::hasColumn('result_publications', 'ca1_published_at')) {
                $table->dropColumn('ca1_published_at');
            }
        });
    }
};
