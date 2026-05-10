<?php

namespace App\Actions\AudioGenerations;

use App\Actions\AudioGenerations\Requests\RemovePreviousPromptRequest;
use App\Services\AudioGenerationHistoryService;

class RemovePreviousPromptAction
{
    /**
     * Create the action with generation history access.
     */
    public function __construct(
        private readonly AudioGenerationHistoryService $history,
    ) {}

    /**
     * Delete a previous generation and its saved WAV file.
     */
    public function handle(RemovePreviousPromptRequest $request): bool
    {
        return $this->history->delete($request->generationId);
    }
}
