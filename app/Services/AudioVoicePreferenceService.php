<?php

namespace App\Services;

use App\Models\AudioVoicePreference;

class AudioVoicePreferenceService
{
    /**
     * Create the preference service with access to the supported Gemini voices.
     */
    public function __construct(
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Return the saved voice preference or the configured default voice.
     *
     * @return array{name: string, gender: string, label: string}
     */
    public function current(): array
    {
        $preference = AudioVoicePreference::query()
            ->select(['id', 'key', 'tts_voice', 'tts_voice_gender', 'tts_voice_label'])
            ->where('key', AudioVoicePreference::CURRENT_KEY)
            ->first();

        if ($preference === null) {
            return $this->voices->default();
        }

        return $this->voices->find((string) $preference->tts_voice) ?? $this->voices->default();
    }

    /**
     * Store the selected voice without creating an audio prompt history row.
     */
    public function save(string $voiceName): AudioVoicePreference
    {
        $voice = $this->voices->find($voiceName) ?? $this->voices->default();

        return AudioVoicePreference::query()->updateOrCreate(
            ['key' => AudioVoicePreference::CURRENT_KEY],
            [
                'tts_voice' => $voice['name'],
                'tts_voice_gender' => $voice['gender'],
                'tts_voice_label' => $voice['label'],
            ],
        );
    }
}
