<?php

namespace App\Actions\AudioGenerations;

use App\Actions\AudioGenerations\Requests\UsePreviousPromptRequest;
use App\Services\AudioGenerationHistoryService;
use App\Services\GeminiLanguageService;

class UsePreviousPromptAction
{
    /**
     * Create the action with generation history and language services.
     */
    public function __construct(
        private readonly AudioGenerationHistoryService $history,
        private readonly GeminiLanguageService $languages,
    ) {}

    /**
     * Load a previous generation into generator state.
     *
     * @return array{master_prompt: string, text: string, selected_voice: string, selected_voice_gender: string, selected_language_code: string, audio_generation_id: int, wav_path: string|null, wav_url: string|null}|null
     */
    public function handle(UsePreviousPromptRequest $request): ?array
    {
        $generation = $this->history->find($request->generationId);

        if ($generation === null) {
            return null;
        }

        return [
            'master_prompt' => (string) ($generation->master_prompt ?? $generation->prompt_brief),
            'text' => (string) $generation->text,
            'selected_voice' => (string) $generation->tts_voice,
            'selected_voice_gender' => (string) $generation->tts_voice_gender,
            'selected_language_code' => (string) ($generation->tts_language_code ?: $this->languages->default()['code']),
            'audio_generation_id' => $generation->id,
            'wav_path' => $generation->audio_path,
            'wav_url' => $generation->audio_url,
        ];
    }
}
