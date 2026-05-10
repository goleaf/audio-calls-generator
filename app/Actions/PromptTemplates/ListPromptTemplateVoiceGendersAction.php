<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\ListPromptTemplateVoiceGendersRequest;
use App\Services\GeminiVoiceService;

class ListPromptTemplateVoiceGendersAction
{
    /**
     * Create the action with Gemini voice metadata.
     */
    public function __construct(
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Return supported voice genders for prompt templates.
     *
     * @return list<string>
     */
    public function handle(ListPromptTemplateVoiceGendersRequest $request): array
    {
        return $this->voices->genders();
    }
}
