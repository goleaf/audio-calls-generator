<?php

namespace App\Rules\AudioGenerations;

use Illuminate\Validation\Rule;

class GenerateAudioRules
{
    /**
     * Return validation rules for generating audio from a saved prompt template.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'selectedPromptTemplateId' => [
                'required',
                'integer',
                Rule::exists('prompt_templates', 'id'),
            ],
        ];
    }

    /**
     * Return human-readable validation messages for audio generation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'selectedPromptTemplateId.required' => 'Choose a prompt template first.',
            'selectedPromptTemplateId.integer' => 'Choose an available prompt template.',
            'selectedPromptTemplateId.exists' => 'Choose an available prompt template.',
        ];
    }
}
