<?php

namespace App\Rules\PromptTemplates;

use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;
use Illuminate\Validation\Rule;

class PromptTemplateRules
{
    /**
     * Create the rule set with supported Gemini language and voice metadata.
     */
    public function __construct(
        private readonly GeminiLanguageService $languages,
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Return validation rules required to save a complete prompt template.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(string $selectedVoiceGender): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:120'],
            'masterPrompt' => ['required', 'string', 'min:3', 'max:2000'],
            'selectedLanguageCode' => ['required', 'string', Rule::in($this->languages->codes())],
            'selectedVoiceGender' => ['required', 'string', Rule::in($this->voices->genders())],
            'selectedVoice' => ['required', 'string', Rule::in($this->voices->namesForGender($selectedVoiceGender))],
            'promptText' => ['required', 'string', 'min:3', 'max:5000'],
        ];
    }

    /**
     * Return human-readable validation messages for prompt template forms.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Enter a template title.',
            'title.min' => 'The template title must contain at least :min characters.',
            'title.max' => 'The template title must not be longer than :max characters.',
            'masterPrompt.required' => 'Enter a master prompt.',
            'masterPrompt.min' => 'The master prompt must contain at least :min characters.',
            'masterPrompt.max' => 'The master prompt must not be longer than :max characters.',
            'selectedLanguageCode.required' => 'Choose a language.',
            'selectedLanguageCode.in' => 'Choose an available language.',
            'selectedVoiceGender.required' => 'Choose a voice gender.',
            'selectedVoiceGender.in' => 'Choose an available voice gender.',
            'selectedVoice.required' => 'Choose a voice generator.',
            'selectedVoice.in' => 'Choose a generator from the selected gender.',
            'promptText.required' => 'Enter prompt text.',
            'promptText.min' => 'The prompt text must contain at least :min characters.',
            'promptText.max' => 'The prompt text must not be longer than :max characters.',
        ];
    }
}
