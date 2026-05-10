<?php

namespace App\Actions\PromptTemplates\Requests;

readonly class RemovePromptTemplateRequest
{
    /**
     * Create a request for deleting a prompt template.
     */
    public function __construct(
        public int $templateId,
    ) {}
}
