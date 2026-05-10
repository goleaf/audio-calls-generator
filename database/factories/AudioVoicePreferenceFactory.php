<?php

namespace Database\Factories;

use App\Models\AudioVoicePreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AudioVoicePreference>
 */
class AudioVoicePreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => AudioVoicePreference::CURRENT_KEY,
            'tts_voice' => 'Kore',
            'tts_voice_gender' => 'Female',
            'tts_voice_label' => 'Female - Kore',
        ];
    }
}
