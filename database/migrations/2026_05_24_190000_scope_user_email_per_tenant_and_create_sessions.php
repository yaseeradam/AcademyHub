<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Scope users.email unique to (tenant_id, email)
        //    so different tenants can have the same email address.
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->unique(['tenant_id', 'email'], 'users_tenant_email_unique');
        });

        // 2. Create sessions table for database-driven session management.
        //    Even if SESSION_DRIVER stays 'file', this prepares for migration
        //    to database sessions and enables session auditing.
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_tenant_email_unique');
            $table->unique('email', 'users_email_unique');
        });
    }
};
