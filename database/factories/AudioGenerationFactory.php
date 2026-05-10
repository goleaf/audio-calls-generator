<?php

namespace Database\Factories;

use App\Models\AudioGeneration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AudioGeneration>
 */
class AudioGenerationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prompt_brief' => fake()->sentence(),
            'master_prompt' => 'Write a clear short audio script.',
            'text' => fake()->paragraph(),
            'status' => AudioGeneration::STATUS_WAV_GENERATED,
            'tts_model' => 'gemini-3.1-flash-tts-preview',
            'tts_voice' => 'Kore',
            'tts_voice_gender' => 'Female',
            'tts_voice_label' => 'Female - Kore',
            'audio_disk' => 'public',
            'audio_path' => 'audio/'.fake()->uuid().'.wav',
            'audio_url' => '/storage/audio/'.fake()->uuid().'.wav',
            'audio_file_name' => fake()->uuid().'.wav',
            'audio_mime_type' => 'audio/wav',
            'audio_size_bytes' => fake()->numberBetween(1024, 204800),
            'audio_sample_rate' => 24000,
            'audio_channels' => 1,
            'audio_sample_width' => 2,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AudioGeneration::STATUS_FAILED,
            'error_message' => 'Generation failed.',
        ]);
    }
}
