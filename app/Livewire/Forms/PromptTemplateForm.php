<?php

namespace App\Livewire\Forms;

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
