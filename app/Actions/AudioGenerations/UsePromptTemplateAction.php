<?php

namespace App\Actions\AudioGenerations;

use App\Actions\AudioGenerations\Requests\UsePromptTemplateRequest;
use App\Models\PromptTemplate;
use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;
use App\Services\PromptTemplateService;

class UsePromptTemplateAction
{
    /**
     * Create the action with prompt template, language, and voice services.
     */
    public function __construct(
        private readonly PromptTemplateService $templates,
        private readonly GeminiLanguageService $languages,
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Load a prompt template and convert it into audio generator state.
     *
     * @return array{template: PromptTemplate, state: array{master_prompt: string, text: string, selected_language_code: string, selected_voice_gender: string, selected_voice: string, selected_template: array{title: string, master_prompt: string, prompt_text: string, language_label: string, tts_voice_label: string}}}|null
     */
    public function handle(UsePromptTemplateRequest $request): ?array
    {
        if ($request->templateId < 1) {
            return null;
        }

        $template = $this->templates->find($request->templateId);

        if ($template === null) {
            return null;
        }

        return [
            'template' => $template,
            'state' => $this->stateFromTemplate($template),
        ];
    }

    /**
     * Convert a saved prompt template into Livewire generator state.
     *
     * @return array{master_prompt: string, text: string, selected_language_code: string, selected_voice_gender: string, selected_voice: string, selected_template: array{title: string, master_prompt: string, prompt_text: string, language_label: string, tts_voice_label: string}}
     */
    private function stateFromTemplate(PromptTemplate $template): array
    {
        $language = $this->languages->find((string) $template->language_code) ?? $this->languages->default();
        $voice = $this->voices->find((string) $template->tts_voice) ?? $this->voices->default();

        return [
            'master_prompt' => (string) $template->master_prompt,
            'text' => $template->prompt_text,
            'selected_language_code' => $language['code'],
            'selected_voice_gender' => $voice['gender'],
            'selected_voice' => $voice['name'],
            'selected_template' => [
                'title' => $template->title,
                'master_prompt' => (string) $template->master_prompt,
                'prompt_text' => $template->prompt_text,
                'language_label' => $language['label'],
                'tts_voice_label' => $voice['label'],
            ],
        ];
    }
}
