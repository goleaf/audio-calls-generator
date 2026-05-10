<?php

namespace App\Livewire\Forms;

use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;
use Illuminate\Validation\Rule;
use Livewire\Form;

class PromptTemplateForm extends Form
{
    public string $title = '';

    public string $masterPrompt = '';

    public string $promptText = '';

    public string $selectedLanguageCode = '';

    public string $selectedVoiceGender = '';

    public string $selectedVoice = '';

    /**
     * Validate all fields required to save a complete prompt template.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $voiceService = app(GeminiVoiceService::class);

        return [
            'title' => ['required', 'string', 'min:2', 'max:120'],
            'masterPrompt' => ['required', 'string', 'min:3', 'max:2000'],
            'selectedLanguageCode' => ['required', 'string', Rule::in(app(GeminiLanguageService::class)->codes())],
            'selectedVoiceGender' => ['required', 'string', Rule::in($voiceService->genders())],
            'selectedVoice' => ['required', 'string', Rule::in($voiceService->namesForGender($this->selectedVoiceGender))],
            'promptText' => ['required', 'string', 'min:3', 'max:5000'],
        ];
    }

    /**
     * Human-readable validation messages for the prompt template form.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Enter a template title.',
            'title.min' => 'The template title must contain at least :min characters.',
            'title.max' => 'The template title must not be longer than :max characters.',
            'masterPrompt.required' => 'Enter a master prompt.',
            'masterPrompt.min' => 'The master prompt must contain at least :min characters.',
            'masterPrompt.max' => 'The master prompt must not be longer than :max characters.',
            'selectedLanguageCode.required' => 'Choose a language.',
            'selectedLanguageCode.in' => 'Choose an available language.',
            'selectedVoiceGender.required' => 'Choose a voice gender.',
            'selectedVoiceGender.in' => 'Choose an available voice gender.',
            'selectedVoice.required' => 'Choose a voice generator.',
            'selectedVoice.in' => 'Choose a generator from the selected gender.',
            'promptText.required' => 'Enter prompt text.',
            'promptText.min' => 'The prompt text must contain at least :min characters.',
            'promptText.max' => 'The prompt text must not be longer than :max characters.',
        ];
    }

    /**
     * Apply action-built form state without touching fields the state did not include.
     *
     * @param  array<string, mixed>  $state
     */
    public function fillFromState(array $state): void
    {
        if (array_key_exists('title', $state)) {
            $this->title = (string) $state['title'];
        }

        if (array_key_exists('master_prompt', $state)) {
            $this->masterPrompt = (string) $state['master_prompt'];
        }

        if (array_key_exists('prompt_text', $state)) {
            $this->promptText = (string) $state['prompt_text'];
        }

        if (array_key_exists('selected_language_code', $state)) {
            $this->selectedLanguageCode = (string) $state['selected_language_code'];
        }

        if (array_key_exists('selected_voice_gender', $state)) {
            $this->selectedVoiceGender = (string) $state['selected_voice_gender'];
        }

        if (array_key_exists('selected_voice', $state)) {
            $this->selectedVoice = (string) $state['selected_voice'];
        }
    }
}
