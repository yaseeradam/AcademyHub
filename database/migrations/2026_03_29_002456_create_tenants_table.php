<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->enum('plan', ['free', 'pro', 'enterprise'])->default('free');
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->json('settings')->nullable(); // per-tenant overrides
            $table->string('logo')->nullable();
            $table->string('primary_color')->default('#f59e0b');
            $table->unsignedBigInteger('max_students')->default(500);
            $table->unsignedBigInteger('max_teachers')->default(50);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
