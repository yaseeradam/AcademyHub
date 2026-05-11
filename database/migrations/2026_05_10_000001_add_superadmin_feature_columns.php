<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'password_reset_required')) {
                $table->boolean('password_reset_required')->default(false)->after('last_login_at');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'grace_days')) {
                $table->unsignedTinyInteger('grace_days')->default(7)->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'password_reset_required']);
        });
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('grace_days');
        });
    }
};
