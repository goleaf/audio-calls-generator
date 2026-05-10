<?php

namespace App\Actions\PromptTemplates\Requests;

readonly class SavePromptTemplateRequest
{
    /**
     * Create a request for creating or updating a prompt template.
     */
    public function __construct(
        public ?int $templateId,
        public string $title,
        public string $masterPrompt,
        public string $promptText,
        public string $languageCode,
        public string $voiceName,
    ) {}
}
