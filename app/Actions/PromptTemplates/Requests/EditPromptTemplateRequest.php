<?php

namespace App\Actions\PromptTemplates\Requests;

readonly class EditPromptTemplateRequest
{
    /**
     * Create a request for loading a template into the edit form.
     */
    public function __construct(
        public int $templateId,
    ) {}
}
