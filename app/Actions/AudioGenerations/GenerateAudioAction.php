<?php

namespace App\Actions\AudioGenerations;

use App\Actions\AudioGenerations\Requests\GenerateAudioRequest;
use App\Actions\AudioGenerations\Requests\UsePromptTemplateRequest;
use App\Exceptions\AudioGenerationException;
use App\Models\AudioGeneration;
use App\Services\AudioGenerationHistoryService;
use App\Services\GeminiAudioService;
use Throwable;

class GenerateAudioAction
{
    private const ERROR_UNEXPECTED_GENERATION = 'Audio generation failed unexpectedly.';

    /**
     * Create the action with audio, history, and template dependencies.
     */
    public function __construct(
        private readonly UsePromptTemplateAction $templates,
        private readonly AudioGenerationHistoryService $history,
        private readonly GeminiAudioService $audio,
    ) {}

    /**
     * Generate WAV audio from the selected template and persist all generation state.
     *
     * @return array{template_state: array<string, mixed>|null, generation: AudioGeneration|null, audio: array{path: string, url: string}|null, error_message: string|null}
     */
    public function handle(GenerateAudioRequest $request): array
    {
        $template = $this->templates->handle(new UsePromptTemplateRequest($request->promptTemplateId));

        if ($template === null) {
            return $this->result(null, null, null, 'Prompt template was not found.');
        }

        $state = $template['state'];
        $generation = $this->history->saveDraft(
            $request->audioGenerationId,
            $state['master_prompt'],
            $state['text'],
            $state['selected_voice'],
            $state['selected_language_code'],
        );

        try {
            $audio = $this->audio->generateWav(
                $state['text'],
                $state['selected_voice'],
                $state['selected_language_code'],
            );
            $generation = $this->history->markWavGenerated($generation, $audio);

            return $this->result($state, $generation, [
                'path' => (string) $generation->audio_path,
                'url' => (string) $generation->audio_url,
            ]);
        } catch (AudioGenerationException $exception) {
            $generation = $this->markFailure($generation, $state, $exception->getMessage());

            return $this->result($state, $generation, null, $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            $generation = $this->markFailure($generation, $state, self::ERROR_UNEXPECTED_GENERATION);

            return $this->result($state, $generation, null, self::ERROR_UNEXPECTED_GENERATION);
        }
    }

    /**
     * Mark an existing draft as failed using the selected template state.
     *
     * @param  array<string, mixed>  $state
     */
    private function markFailure(AudioGeneration $generation, array $state, string $message): AudioGeneration
    {
        return $this->history->markWavFailed(
            $generation,
            $state['selected_voice'],
            $state['selected_language_code'],
            $message,
        );
    }

    /**
     * Build a stable result shape for the Livewire component.
     *
     * @param  array<string, mixed>|null  $templateState
     * @param  array{path: string, url: string}|null  $audio
     * @return array{template_state: array<string, mixed>|null, generation: AudioGeneration|null, audio: array{path: string, url: string}|null, error_message: string|null}
     */
    private function result(?array $templateState, ?AudioGeneration $generation, ?array $audio, ?string $errorMessage = null): array
    {
        return [
            'template_state' => $templateState,
            'generation' => $generation,
            'audio' => $audio,
            'error_message' => $errorMessage,
        ];
    }
}
