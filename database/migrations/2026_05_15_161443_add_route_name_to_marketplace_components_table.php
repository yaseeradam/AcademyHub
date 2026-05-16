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
        Schema::table('marketplace_components', function (Blueprint $table) {
            $table->string('route_name')->nullable()->after('slug')
                  ->comment('Named Laravel route to navigate to after installation, e.g. cbt.index');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_components', function (Blueprint $table) {
            $table->dropColumn('route_name');
        });
    }
};
