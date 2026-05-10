<?php

namespace App\Livewire\Forms;

use App\Models\PromptTemplate;
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
     * Reset the form to empty text fields and configured Gemini defaults.
     */
    public function resetToDefaults(): void
    {
        $this->title = '';
        $this->masterPrompt = '';
        $this->promptText = '';
        $this->setSelectedLanguage(app(GeminiLanguageService::class)->default()['code']);
        $this->setSelectedVoice(app(GeminiVoiceService::class)->default()['name']);
    }

    /**
     * Fill the form from an existing prompt template for editing.
     */
    public function fillFromTemplate(PromptTemplate $template): void
    {
        $this->title = $template->title;
        $this->masterPrompt = (string) $template->master_prompt;
        $this->promptText = $template->prompt_text;
        $this->setSelectedLanguage((string) $template->language_code);
        $this->setSelectedVoice((string) $template->tts_voice);
    }

    /**
     * Select a voice gender and default to the first matching generator.
     */
    public function selectVoiceGender(string $gender): void
    {
        $voiceService = app(GeminiVoiceService::class);
        $generators = $this->voiceOptionsForGender($voiceService, $gender);

        if ($generators === []) {
            $this->setSelectedVoice($voiceService->default()['name']);

            return;
        }

        $this->selectedVoiceGender = $gender;
        $this->selectedVoice = $generators[0]['name'];
    }

    /**
     * Return the voice options that belong to the selected gender.
     *
     * @return list<array{name: string, gender: string}>
     */
    public function voiceGenerators(): array
    {
        return $this->voiceOptionsForGender(app(GeminiVoiceService::class), $this->selectedVoiceGender);
    }

    /**
     * Set the selected language to a supported Gemini-TTS language code.
     */
    private function setSelectedLanguage(string $languageCode): void
    {
        $languageService = app(GeminiLanguageService::class);
        $language = $languageService->find($languageCode) ?? $languageService->default();

        $this->selectedLanguageCode = $language['code'];
    }

    /**
     * Set the selected voice and align the dependent gender field.
     */
    private function setSelectedVoice(string $voiceName): void
    {
        $voiceService = app(GeminiVoiceService::class);
        $voice = $voiceService->find($voiceName) ?? $voiceService->default();

        $this->selectedVoiceGender = $voice['gender'];
        $this->selectedVoice = $voice['name'];
    }

    /**
     * Build the slim voice option shape required by the Blade select.
     *
     * @return list<array{name: string, gender: string}>
     */
    private function voiceOptionsForGender(GeminiVoiceService $voiceService, string $gender): array
    {
        return collect($voiceService->generatorsForGender($gender))
            ->map(fn (array $generator): array => [
                'name' => $generator['name'],
                'gender' => $generator['gender'],
            ])
            ->all();
    }
}
