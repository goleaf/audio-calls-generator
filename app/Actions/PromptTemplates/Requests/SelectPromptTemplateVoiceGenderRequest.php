<?php

namespace App\Actions\PromptTemplates\Requests;

readonly class SelectPromptTemplateVoiceGenderRequest
{
    /**
     * Create a request for selecting a prompt-template voice gender.
     */
    public function __construct(
        public string $gender,
    ) {}
}
