<?php

namespace App\Actions\PromptTemplates\Requests;

class ListPromptTemplateVoiceGeneratorsRequest
{
    /**
     * Create a request for voice generators that match a selected gender.
     */
    public function __construct(
        public readonly string $gender,
    ) {}
}
