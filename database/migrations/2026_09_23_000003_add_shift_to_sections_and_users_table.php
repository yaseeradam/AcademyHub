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
        if (Schema::hasTable('sections') && !Schema::hasColumn('sections', 'shift')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->string('shift', 20)->default('Western')->after('name');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'shift')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('shift', 20)->nullable()->default('Western')->after('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sections') && Schema::hasColumn('sections', 'shift')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropColumn('shift');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'shift')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('shift');
            });
        }
    }
};
