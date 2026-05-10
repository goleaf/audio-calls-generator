<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\ListPromptTemplatesRequest;
use App\Services\PromptTemplateService;

class ListPromptTemplatesAction
{
    /**
     * Create the action with prompt template persistence.
     */
    public function __construct(
        private readonly PromptTemplateService $templates,
    ) {}

    /**
     * Return saved templates for the CRUD table.
     *
     * @return list<array{id: int, title: string, master_prompt: string|null, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}>
     */
    public function handle(ListPromptTemplatesRequest $request): array
    {
        return $this->templates->recent();
    }
}
