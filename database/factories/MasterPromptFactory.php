<?php

namespace Database\Factories;

use App\Models\MasterPrompt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterPrompt>
 */
class MasterPromptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => MasterPrompt::CURRENT_KEY,
            'content' => 'Write a short, ready-to-speak audio script. Return only the final script text.',
        ];
    }
}
