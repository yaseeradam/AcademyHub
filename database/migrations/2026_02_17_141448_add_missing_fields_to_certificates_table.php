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
        Schema::table('certificates', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('session', 20)->nullable()->after('description');
            $table->tinyInteger('term')->nullable()->after('session');
            $table->date('issue_date')->nullable()->after('term');
            $table->string('template', 50)->nullable()->after('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['description', 'session', 'term', 'issue_date', 'template']);
        });
    }
};
