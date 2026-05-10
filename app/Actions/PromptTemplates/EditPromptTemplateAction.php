<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\EditPromptTemplateRequest;
use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;
use App\Services\PromptTemplateService;

class EditPromptTemplateAction
{
    /**
     * Create the action with template, language, and voice services.
     */
    public function __construct(
        private readonly PromptTemplateService $templates,
        private readonly GeminiLanguageService $languages,
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Load a prompt template into the editable form state.
     *
     * @return array{id: int, title: string, master_prompt: string, prompt_text: string, selected_language_code: string, selected_voice_gender: string, selected_voice: string}|null
     */
    public function handle(EditPromptTemplateRequest $request): ?array
    {
        $template = $this->templates->find($request->templateId);

        if ($template === null) {
            return null;
        }

        $language = $this->languages->find((string) $template->language_code) ?? $this->languages->default();
        $voice = $this->voices->find((string) $template->tts_voice) ?? $this->voices->default();

        return [
            'id' => $template->id,
            'title' => $template->title,
            'master_prompt' => (string) $template->master_prompt,
            'prompt_text' => $template->prompt_text,
            'selected_language_code' => $language['code'],
            'selected_voice_gender' => $voice['gender'],
            'selected_voice' => $voice['name'],
        ];
    }
}
