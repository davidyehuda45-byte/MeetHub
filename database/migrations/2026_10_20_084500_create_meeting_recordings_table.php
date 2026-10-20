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
        Schema::create('meeting_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('file_name');
            $table->string('storage_disk')->default('local');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('mime_type')->default('video/mp4');
            $table->string('status')->default('ready'); // processing, ready, failed
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['meeting_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_recordings');
    }
};

