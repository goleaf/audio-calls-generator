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
        Schema::create('audio_generations', function (Blueprint $table) {
            $table->id();
            $table->text('prompt_brief')->nullable();
            $table->longText('text')->nullable();
            $table->string('status')->default('draft')->index();
            $table->string('prompt_model')->nullable();
            $table->string('tts_model')->nullable();
            $table->string('tts_voice')->nullable();
            $table->string('audio_disk')->nullable();
            $table->string('audio_path')->nullable()->index();
            $table->text('audio_url')->nullable();
            $table->string('audio_file_name')->nullable();
            $table->string('audio_mime_type')->nullable();
            $table->unsignedBigInteger('audio_size_bytes')->nullable();
            $table->unsignedInteger('audio_sample_rate')->nullable();
            $table->unsignedSmallInteger('audio_channels')->nullable();
            $table->unsignedSmallInteger('audio_sample_width')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_generations');
    }
};
