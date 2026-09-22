<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates a lightweight superadmin_notifications table.
     * This stores platform-wide alerts (e.g. bank submission, rating, feedback).
     */
    public function up(): void
    {
        Schema::create('superadmin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60);           // 'payout_request' | 'app_rating' | 'support_ticket' etc.
            $table->string('title', 255);
            $table->text('message');
            $table->string('action_url', 500)->nullable(); // deep-link to relevant admin page
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['read_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('superadmin_notifications');
    }
};
