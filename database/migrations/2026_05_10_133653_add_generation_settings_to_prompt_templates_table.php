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
        Schema::table('prompt_templates', function (Blueprint $table) {
            $table->text('master_prompt')->nullable()->after('title');
            $table->string('tts_voice')->nullable()->after('language_readiness');
            $table->string('tts_voice_gender')->nullable()->after('tts_voice');
            $table->string('tts_voice_label')->nullable()->after('tts_voice_gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prompt_templates', function (Blueprint $table) {
            $table->dropColumn([
                'master_prompt',
                'tts_voice',
                'tts_voice_gender',
                'tts_voice_label',
            ]);
        });
    }
};
