<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\SavePromptTemplateRequest;
use App\Models\PromptTemplate;
use App\Services\PromptTemplateService;

class SavePromptTemplateAction
{
    /**
     * Create the action with prompt template persistence.
     */
    public function __construct(
        private readonly PromptTemplateService $templates,
    ) {}

    /**
     * Create or update a prompt template from validated form state.
     */
    public function handle(SavePromptTemplateRequest $request): ?PromptTemplate
    {
        if ($request->templateId === null) {
            return $this->templates->create(
                $request->title,
                $request->masterPrompt,
                $request->promptText,
                $request->languageCode,
                $request->voiceName,
            );
        }

        return $this->templates->update(
            $request->templateId,
            $request->title,
            $request->masterPrompt,
            $request->promptText,
            $request->languageCode,
            $request->voiceName,
        );
    }
}
