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
        // 1. Meetings
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('room_code', 32)->unique();
            $table->string('jitsi_room_name', 64)->unique();
            $table->string('password_hash')->nullable();
            $table->dateTime('scheduled_at')->index();
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('status', 24)->default('scheduled')->index(); // scheduled, ongoing, ended, cancelled
            $table->boolean('lobby_enabled')->default(false);
            $table->boolean('allow_guests')->default(true);
            $table->boolean('start_muted')->default(false);
            $table->string('recurrence_rule')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
        });

        // 2. Meeting Invitations
        Schema::create('meeting_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('status', 24)->default('pending'); // pending, accepted, declined
            $table->string('token', 64)->unique();
            $table->timestamps();
        });

        // 3. Attendance Sessions
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('session_token', 64)->index();
            $table->dateTime('joined_at');
            $table->dateTime('left_at')->nullable();
            $table->dateTime('last_heartbeat_at')->nullable();
            $table->string('platform', 24)->default('web'); // web, android, ios
            $table->text('device_info')->nullable();
            $table->timestamps();

            $table->index(['meeting_id', 'user_id']);
        });

        // 4. Attendance Summaries
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_identifier')->nullable();
            $table->unsignedInteger('total_seconds')->default(0);
            $table->dateTime('first_join_at')->nullable();
            $table->string('status', 24)->default('present'); // present, late, absent
            $table->timestamps();

            $table->index(['meeting_id', 'user_id']);
        });

        // 5. Meeting Notes
        Schema::create('meeting_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->unique()->constrained('meetings')->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        // 6. Meeting Note Versions
        Schema::create('meeting_note_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('meeting_notes')->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. Action Items
        Schema::create('action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });

        // 8. Meeting Files
        Schema::create('meeting_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime', 128);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index('meeting_id');
        });

        // 9. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });

        // 10. Device Tokens
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 255)->unique();
            $table->string('platform', 24); // android, ios, web
            $table->timestamps();
        });

        // 11. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
        });

        // 12. Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('meeting_files');
        Schema::dropIfExists('action_items');
        Schema::dropIfExists('meeting_note_versions');
        Schema::dropIfExists('meeting_notes');
        Schema::dropIfExists('attendance_summaries');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('meeting_invitations');
        Schema::dropIfExists('meetings');
    }
};

