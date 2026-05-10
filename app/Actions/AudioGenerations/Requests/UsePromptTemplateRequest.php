<?php

namespace App\Actions\AudioGenerations\Requests;

readonly class UsePromptTemplateRequest
{
    /**
     * Create a request for loading one prompt template.
     */
    public function __construct(
        public int $templateId,
    ) {}
}
