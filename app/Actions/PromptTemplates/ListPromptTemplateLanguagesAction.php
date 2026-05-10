<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\ListPromptTemplateLanguagesRequest;
use App\Services\GeminiLanguageService;

class ListPromptTemplateLanguagesAction
{
    /**
     * Create the action with Gemini language metadata.
     */
    public function __construct(
        private readonly GeminiLanguageService $languages,
    ) {}

    /**
     * Return supported Gemini-TTS languages grouped by readiness.
     *
     * @return array<string, list<array{name: string, code: string, readiness: string, label: string}>>
     */
    public function handle(ListPromptTemplateLanguagesRequest $request): array
    {
        return $this->languages->groups();
    }
}
