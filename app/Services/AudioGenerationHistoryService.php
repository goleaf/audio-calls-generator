<?php

namespace App\Services;

use App\Models\AudioGeneration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioGenerationHistoryService
{
    private const DEFAULT_MODEL = 'gemini-3.1-flash-tts-preview';

    private const DEFAULT_SAMPLE_RATE = 24000;

    private const DEFAULT_CHANNELS = 1;

    private const DEFAULT_SAMPLE_WIDTH = 2;

    private const DEFAULT_RECENT_LIMIT = 10;

    private const PUBLIC_DISK = 'public';

    private const WAV_MIME_TYPE = 'audio/wav';

    /**
     * Create the history service with access to the supported Gemini voices.
     */
    public function __construct(
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Create a draft record before the Gemini request has produced audio.
     */
    public function createDraft(?string $masterPrompt, ?string $text, string $voiceName): AudioGeneration
    {
        return AudioGeneration::query()->create($this->draftAttributes($masterPrompt, $text, $voiceName));
    }

    /**
     * Update the active draft or create a new one when the current record is no longer editable.
     */
    public function saveDraft(?int $id, ?string $masterPrompt, ?string $text, string $voiceName): AudioGeneration
    {
        $generation = $id === null ? null : $this->find($id);

        if ($generation === null || $generation->status !== AudioGeneration::STATUS_DRAFT) {
            return $this->createDraft($masterPrompt, $text, $voiceName);
        }

        $generation->fill($this->draftAttributes($masterPrompt, $text, $voiceName));
        $generation->save();

        return $generation->refresh();
    }

    /**
     * Create and immediately mark a generation as WAV-generated.
     *
     * @param  array{path: string, url: string, name?: string, disk?: string, mime_type?: string, size?: int, voice?: string, voice_gender?: string, voice_label?: string}  $audio
     */
    public function createWavGenerated(?string $masterPrompt, string $text, array $audio): AudioGeneration
    {
        $voice = $this->voices->find($audio['voice'] ?? '') ?? $this->voices->default();
        $generation = $this->createDraft($masterPrompt, $text, $voice['name']);

        return $this->markWavGenerated($generation, $audio);
    }

    /**
     * Persist WAV metadata onto an existing draft generation.
     *
     * @param  array{path: string, url: string, name?: string, disk?: string, mime_type?: string, size?: int, voice?: string, voice_gender?: string, voice_label?: string}  $audio
     */
    public function markWavGenerated(AudioGeneration $generation, array $audio): AudioGeneration
    {
        $generation->fill($this->wavAttributes($audio));
        $generation->save();

        return $generation->refresh();
    }

    /**
     * Create a failed generation record for callers that do not already have a draft.
     */
    public function recordWavFailure(?string $masterPrompt, string $text, string $voiceName, string $errorMessage): AudioGeneration
    {
        $generation = $this->createDraft($masterPrompt, $text, $voiceName);

        return $this->markWavFailed($generation, $voiceName, $errorMessage);
    }

    /**
     * Persist failure metadata onto an existing generation.
     */
    public function markWavFailed(AudioGeneration $generation, string $voiceName, string $errorMessage): AudioGeneration
    {
        $generation->fill([
            'status' => AudioGeneration::STATUS_FAILED,
            'tts_model' => $this->modelName(),
            ...$this->voiceAttributes($voiceName),
            ...$this->audioConfigAttributes(),
            'error_message' => $errorMessage,
        ]);
        $generation->save();

        return $generation->refresh();
    }

    /**
     * Find one history record with the same selected columns used by the list view.
     */
    public function find(int $id): ?AudioGeneration
    {
        $generation = AudioGeneration::query()
            ->recentHistory()
            ->whereKey($id)
            ->first();

        if ($generation === null) {
            return null;
        }

        return $this->withNormalizedAudioUrl($generation);
    }

    /**
     * Delete a history record and its stored WAV file when one exists.
     */
    public function delete(int $id): bool
    {
        $generation = $this->find($id);

        if ($generation === null) {
            return false;
        }

        $this->deleteAudioFile($generation);

        return (bool) $generation->delete();
    }

    /**
     * Return recent generations in the array shape consumed by the Livewire view.
     *
     * @return list<array<string, mixed>>
     */
    public function recent(int $limit = self::DEFAULT_RECENT_LIMIT): array
    {
        return AudioGeneration::query()
            ->recentHistory()
            ->limit($limit)
            ->get()
            ->map(fn (AudioGeneration $generation): array => [
                'id' => $generation->id,
                'prompt_brief' => $generation->prompt_brief,
                'master_prompt' => $generation->master_prompt,
                'text' => $generation->text,
                'status' => $generation->status,
                'tts_model' => $generation->tts_model,
                'tts_voice' => $generation->tts_voice,
                'tts_voice_gender' => $generation->tts_voice_gender,
                'tts_voice_label' => $generation->tts_voice_label,
                'audio_disk' => $generation->audio_disk,
                'audio_path' => $generation->audio_path,
                'audio_url' => $this->publicAudioUrl($generation->audio_path, $generation->audio_url),
                'audio_file_name' => $generation->audio_file_name,
                'audio_mime_type' => $generation->audio_mime_type,
                'audio_size_bytes' => $generation->audio_size_bytes,
                'audio_sample_rate' => $generation->audio_sample_rate,
                'audio_channels' => $generation->audio_channels,
                'audio_sample_width' => $generation->audio_sample_width,
                'error_message' => $generation->error_message,
                'created_at' => $generation->created_at?->toISOString(),
            ])
            ->all();
    }

    /**
     * Build the database attributes for a draft record.
     *
     * @return array<string, mixed>
     */
    private function draftAttributes(?string $masterPrompt, ?string $text, string $voiceName): array
    {
        return [
            'prompt_brief' => $masterPrompt,
            'master_prompt' => $masterPrompt,
            'text' => $text,
            'status' => AudioGeneration::STATUS_DRAFT,
            'tts_model' => $this->modelName(),
            ...$this->voiceAttributes($voiceName),
            ...$this->audioConfigAttributes(),
            'error_message' => null,
        ];
    }

    /**
     * Build the database attributes for a successful WAV result.
     *
     * @param  array{path: string, url: string, name?: string, disk?: string, mime_type?: string, size?: int, voice?: string, voice_gender?: string, voice_label?: string}  $audio
     * @return array<string, mixed>
     */
    private function wavAttributes(array $audio): array
    {
        $voice = $this->voices->find($audio['voice'] ?? '') ?? $this->voices->default();

        return [
            'status' => AudioGeneration::STATUS_WAV_GENERATED,
            'tts_model' => $this->modelName(),
            'tts_voice' => $voice['name'],
            'tts_voice_gender' => $audio['voice_gender'] ?? $voice['gender'],
            'tts_voice_label' => $audio['voice_label'] ?? $voice['label'],
            'audio_disk' => $audio['disk'] ?? self::PUBLIC_DISK,
            'audio_path' => $audio['path'],
            'audio_url' => $this->publicAudioUrl($audio['path'], $audio['url'] ?? null),
            'audio_file_name' => $audio['name'] ?? basename($audio['path']),
            'audio_mime_type' => $audio['mime_type'] ?? self::WAV_MIME_TYPE,
            'audio_size_bytes' => $audio['size'] ?? null,
            ...$this->audioConfigAttributes(),
            'error_message' => null,
        ];
    }

    /**
     * Delete the WAV file for a generation when a path was saved.
     */
    private function deleteAudioFile(AudioGeneration $generation): void
    {
        if ($generation->audio_path === null || $generation->audio_path === '') {
            return;
        }

        Storage::disk($generation->audio_disk ?: self::PUBLIC_DISK)->delete($generation->audio_path);
    }

    /**
     * Apply the same-origin playback URL to a loaded generation without saving it.
     */
    private function withNormalizedAudioUrl(AudioGeneration $generation): AudioGeneration
    {
        $generation->audio_url = $this->publicAudioUrl($generation->audio_path, $generation->audio_url);

        return $generation;
    }

    /**
     * Build a browser-safe same-origin URL for generated WAV playback.
     */
    private function publicAudioUrl(?string $path, ?string $fallback = null): ?string
    {
        if ($path === null || $path === '') {
            return $fallback;
        }

        $fileName = basename($path);

        if (! Str::endsWith(Str::lower($fileName), '.wav')) {
            return $fallback;
        }

        return route('audio.files.show', ['fileName' => $fileName], false);
    }

    /**
     * Build normalized voice attributes from a Gemini voice name.
     *
     * @return array{tts_voice: string, tts_voice_gender: string, tts_voice_label: string}
     */
    private function voiceAttributes(string $voiceName): array
    {
        $voice = $this->voices->find($voiceName) ?? $this->voices->default();

        return [
            'tts_voice' => $voice['name'],
            'tts_voice_gender' => $voice['gender'],
            'tts_voice_label' => $voice['label'],
        ];
    }

    /**
     * Return the configured Gemini TTS model name.
     */
    private function modelName(): string
    {
        return (string) config('services.gemini.model', self::DEFAULT_MODEL);
    }

    /**
     * Build shared WAV audio setting attributes from configuration.
     *
     * @return array{audio_sample_rate: int, audio_channels: int, audio_sample_width: int}
     */
    private function audioConfigAttributes(): array
    {
        return [
            'audio_sample_rate' => (int) config('services.gemini.audio.sample_rate', self::DEFAULT_SAMPLE_RATE),
            'audio_channels' => (int) config('services.gemini.audio.channels', self::DEFAULT_CHANNELS),
            'audio_sample_width' => (int) config('services.gemini.audio.sample_width', self::DEFAULT_SAMPLE_WIDTH),
        ];
    }
}
