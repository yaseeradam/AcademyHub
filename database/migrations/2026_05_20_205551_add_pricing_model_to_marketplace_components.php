<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('marketplace_components', function (Blueprint $table) {
            $table->string('pricing_model')->default('flat')->after('price'); // 'flat' or 'per_student'
            $table->text('short_description')->nullable()->after('description');
            $table->string('category')->nullable()->after('short_description');
            $table->decimal('rating_avg', 3, 2)->default(0)->after('category');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_avg');
            $table->unsignedInteger('installs')->default(0)->after('rating_count');
        });
    }
    public function down(): void {
        Schema::table('marketplace_components', function (Blueprint $table) {
            $table->dropColumn(['pricing_model','short_description','category','rating_avg','rating_count','installs']);
        });
    }
};
