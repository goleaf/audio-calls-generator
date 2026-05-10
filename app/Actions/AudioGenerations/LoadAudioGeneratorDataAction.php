<?php

namespace App\Actions\AudioGenerations;

use App\Actions\AudioGenerations\Requests\LoadAudioGeneratorDataRequest;
use App\Services\AudioGenerationHistoryService;
use App\Services\PromptTemplateService;

class LoadAudioGeneratorDataAction
{
    /**
     * Create the action with template and history services.
     */
    public function __construct(
        private readonly PromptTemplateService $templates,
        private readonly AudioGenerationHistoryService $history,
    ) {}

    /**
     * Load generator page lists.
     *
     * @return array{prompt_templates: list<array<string, mixed>>, saved_generations: list<array<string, mixed>>}
     */
    public function handle(LoadAudioGeneratorDataRequest $request): array
    {
        return [
            'prompt_templates' => $this->templates->options($request->promptTemplateLimit),
            'saved_generations' => $this->history->recent($request->historyLimit),
        ];
    }
}
