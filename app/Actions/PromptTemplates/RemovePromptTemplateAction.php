<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\RemovePromptTemplateRequest;
use App\Services\PromptTemplateService;

class RemovePromptTemplateAction
{
    /**
     * Create the action with prompt template persistence.
     */
    public function __construct(
        private readonly PromptTemplateService $templates,
    ) {}

    /**
     * Delete one prompt template.
     */
    public function handle(RemovePromptTemplateRequest $request): bool
    {
        return $this->templates->delete($request->templateId);
    }
}
