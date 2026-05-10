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
            $table->string('tts_voice_gender')->nullable()->after('tts_voice');
            $table->string('tts_voice_label')->nullable()->after('tts_voice_gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audio_generations', function (Blueprint $table) {
            $table->dropColumn(['tts_voice_gender', 'tts_voice_label']);
        });
    }
};
