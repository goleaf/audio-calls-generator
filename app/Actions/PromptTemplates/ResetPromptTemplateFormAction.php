<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\ResetPromptTemplateFormRequest;
use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;

class ResetPromptTemplateFormAction
{
    /**
     * Create the action with language and voice defaults.
     */
    public function __construct(
        private readonly GeminiLanguageService $languages,
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Return clean form state with configured Gemini defaults.
     *
     * @return array{title: string, master_prompt: string, prompt_text: string, selected_language_code: string, selected_voice_gender: string, selected_voice: string}
     */
    public function handle(ResetPromptTemplateFormRequest $request): array
    {
        $voice = $this->voices->default();

        return [
            'title' => '',
            'master_prompt' => '',
            'prompt_text' => '',
            'selected_language_code' => $this->languages->default()['code'],
            'selected_voice_gender' => $voice['gender'],
            'selected_voice' => $voice['name'],
        ];
    }
}
