<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\ListPromptTemplateVoiceGeneratorsRequest;
use App\Services\GeminiVoiceService;

class ListPromptTemplateVoiceGeneratorsAction
{
    /**
     * Create the action with Gemini voice metadata.
     */
    public function __construct(
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Return voice names that belong to the selected gender.
     *
     * @return list<array{name: string, gender: string}>
     */
    public function handle(ListPromptTemplateVoiceGeneratorsRequest $request): array
    {
        return collect($this->voices->generatorsForGender($request->gender))
            ->map(fn (array $generator): array => [
                'name' => $generator['name'],
                'gender' => $generator['gender'],
            ])
            ->all();
    }
}
