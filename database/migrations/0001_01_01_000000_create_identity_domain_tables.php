<?php

declare(strict_types=1);

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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('pending_email_verification'); // pending_email_verification, active, suspended, banned
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sanctum default tables will be created by Sanctum's own migration
        // Spatie RBAC default tables will be created by Spatie's own migration

        // Refresh Tokens with Token Family Tracking
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token_hash')->unique();
            $table->uuid('token_family_id')->index();
            $table->uuid('parent_token_id')->nullable()->index();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        // User Session Fingerprinting
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_name')->nullable();
            $table->string('device_fingerprint')->index()->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        // Device Registration for FCM and Trust
        Schema::create('user_devices', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_id')->unique();
            $table->string('fcm_token')->nullable();
            $table->string('platform')->nullable(); // iOS, Android, Web
            $table->string('status')->default('active');
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });

        // Extended Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->uuid('entity_id')->nullable();
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Security Events
        Schema::create('security_events', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type');
            $table->string('severity')->default('LOW'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->jsonb('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Failed Login Attempts
        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('email_attempted')->index();
            $table->timestamp('created_at')->useCurrent();
        });

        // Notification Preferences
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUIDv7
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('email_promotions')->default(false);
            $table->boolean('push_messages')->default(true);
            $table->boolean('sms_alerts')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('failed_login_attempts');
        Schema::dropIfExists('security_events');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
