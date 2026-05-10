<?php

namespace Database\Factories;

use App\Models\PromptTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromptTemplate>
 */
class PromptTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'prompt_text' => fake()->paragraph(),
            'language_code' => 'en-US',
            'language_name' => 'English (United States)',
            'language_readiness' => 'GA',
        ];
    }
}
