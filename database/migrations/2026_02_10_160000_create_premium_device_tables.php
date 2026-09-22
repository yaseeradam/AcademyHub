<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('label')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('premium_device_removals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('premium_device_id')->constrained('premium_devices')->cascadeOnDelete();
            $table->foreignId('removed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_device_removals');
        Schema::dropIfExists('premium_devices');
    }
};

