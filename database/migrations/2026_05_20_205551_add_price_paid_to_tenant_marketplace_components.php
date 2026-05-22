<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tenant_marketplace_components', function (Blueprint $table) {
            $table->decimal('price_paid', 10, 2)->nullable()->after('installed_at');
            $table->unsignedInteger('student_count_at_install')->nullable()->after('price_paid');
            $table->timestamp('uninstalled_at')->nullable()->after('student_count_at_install');
        });
    }
    public function down(): void {
        Schema::table('tenant_marketplace_components', function (Blueprint $table) {
            $table->dropColumn(['price_paid','student_count_at_install','uninstalled_at']);
        });
    }
};
