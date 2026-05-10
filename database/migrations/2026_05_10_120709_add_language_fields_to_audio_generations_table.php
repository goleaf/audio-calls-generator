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
        Schema::table('audio_generations', function (Blueprint $table) {
            $table->string('tts_language_code')->nullable()->after('tts_voice_label');
            $table->string('tts_language_name')->nullable()->after('tts_language_code');
            $table->string('tts_language_readiness')->nullable()->after('tts_language_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audio_generations', function (Blueprint $table) {
            $table->dropColumn([
                'tts_language_code',
                'tts_language_name',
                'tts_language_readiness',
            ]);
        });
    }
};
