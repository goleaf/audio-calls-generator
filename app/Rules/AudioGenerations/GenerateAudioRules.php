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
            'masterPrompt' => ['required', 'string', 'min:3', 'max:2000'],
            'text' => ['required', 'string', 'min:3', 'max:5000'],
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
            'masterPrompt.required' => 'Enter a master prompt.',
            'masterPrompt.min' => 'The master prompt must contain at least :min characters.',
            'masterPrompt.max' => 'The master prompt must not be longer than :max characters.',
            'text.required' => 'Enter prompt text.',
            'text.min' => 'The prompt text must contain at least :min characters.',
            'text.max' => 'The prompt text must not be longer than :max characters.',
        ];
    }
}
