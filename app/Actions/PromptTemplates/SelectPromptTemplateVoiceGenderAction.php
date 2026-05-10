<?php

namespace App\Actions\PromptTemplates;

use App\Actions\PromptTemplates\Requests\SelectPromptTemplateVoiceGenderRequest;
use App\Services\GeminiVoiceService;

class SelectPromptTemplateVoiceGenderAction
{
    /**
     * Create the action with supported Gemini voices.
     */
    public function __construct(
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Return the selected gender with the first matching voice.
     *
     * @return array{selected_voice_gender: string, selected_voice: string}
     */
    public function handle(SelectPromptTemplateVoiceGenderRequest $request): array
    {
        $generators = $this->voices->generatorsForGender($request->gender);

        if ($generators === []) {
            $voice = $this->voices->default();

            return [
                'selected_voice_gender' => $voice['gender'],
                'selected_voice' => $voice['name'],
            ];
        }

        return [
            'selected_voice_gender' => $request->gender,
            'selected_voice' => $generators[0]['name'],
        ];
    }
}
