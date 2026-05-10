<?php

namespace App\Livewire;

use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;
use App\Services\PromptTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Prompt Templates')]
class PromptTemplateManager extends Component
{
    private const VIEW = 'livewire.prompt-template-manager';

    private const SUCCESS_TEMPLATE_SAVED = 'Prompt template has been saved.';

    private const SUCCESS_TEMPLATE_UPDATED = 'Prompt template has been updated.';

    private const SUCCESS_TEMPLATE_REMOVED = 'Prompt template has been removed.';

    private const ERROR_TEMPLATE_NOT_FOUND = 'Prompt template was not found.';

    public string $title = '';

    public string $masterPrompt = '';

    public string $promptText = '';

    public string $selectedLanguageCode = '';

    public string $selectedVoiceGender = '';

    public string $selectedVoice = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public ?int $editingTemplateId = null;

    /** @var array<string, list<array{name: string, code: string, readiness: string, label: string}>> */
    public array $languageGroups = [];

    /** @var list<string> */
    public array $voiceGenders = [];

    /** @var list<array{name: string, gender: string}> */
    public array $voiceGenerators = [];

    /** @var list<array{id: int, title: string, master_prompt: string|null, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}> */
    public array $promptTemplates = [];

    /**
     * Load supported languages, voices, and prompt templates when the page is opened.
     */
    public function mount(): void
    {
        $languageService = app(GeminiLanguageService::class);
        $voiceService = app(GeminiVoiceService::class);

        $this->languageGroups = $languageService->groups();
        $this->selectedLanguageCode = $languageService->default()['code'];
        $this->voiceGenders = $voiceService->genders();
        $this->setSelectedVoice($voiceService->default()['name']);
        $this->loadPromptTemplates();
    }

    /**
     * Validate and create or update a reusable prompt template.
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules(), $this->validationMessages());

        $service = app(PromptTemplateService::class);
        $wasEditing = $this->editingTemplateId !== null;
        $template = $this->editingTemplateId === null
            ? $service->create(
                $validated['title'],
                $validated['masterPrompt'],
                $validated['promptText'],
                $validated['selectedLanguageCode'],
                $validated['selectedVoice'],
            )
            : $service->update(
                $this->editingTemplateId,
                $validated['title'],
                $validated['masterPrompt'],
                $validated['promptText'],
                $validated['selectedLanguageCode'],
                $validated['selectedVoice'],
            );

        if ($template === null) {
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->resetForm();
        $this->errorMessage = null;
        $this->successMessage = $wasEditing ? self::SUCCESS_TEMPLATE_UPDATED : self::SUCCESS_TEMPLATE_SAVED;
        $this->loadPromptTemplates();
    }

    /**
     * Load an existing template into the form for editing.
     */
    public function edit(int $templateId): void
    {
        $template = app(PromptTemplateService::class)->find($templateId);

        if ($template === null) {
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->editingTemplateId = $template->id;
        $this->title = $template->title;
        $this->masterPrompt = (string) $template->master_prompt;
        $this->promptText = $template->prompt_text;
        $this->setSelectedLanguage((string) $template->language_code);
        $this->setSelectedVoice((string) $template->tts_voice);
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    /**
     * Return the form to create mode without changing saved templates.
     */
    public function cancelEdit(): void
    {
        $this->resetForm();
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    /**
     * Remove a reusable prompt template from the library.
     */
    public function remove(int $templateId): void
    {
        if (! app(PromptTemplateService::class)->delete($templateId)) {
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_TEMPLATE_REMOVED;
        $this->loadPromptTemplates();
    }

    /**
     * Refresh voice names when a gender is selected in the template form.
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
        $this->voiceGenerators = $generators;
        $this->selectedVoice = $generators[0]['name'];
        $this->resetValidation('selectedVoice');
    }

    /**
     * Render the class-based Livewire component view.
     */
    public function render(): View
    {
        return view(self::VIEW);
    }

    /**
     * Validation rules for prompt template creation.
     *
     * @return array<string, list<mixed>>
     */
    private function rules(): array
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
     * Human-readable validation messages shown in the template manager.
     *
     * @return array<string, string>
     */
    private function validationMessages(): array
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
     * Refresh the saved template list from the persistence service.
     */
    private function loadPromptTemplates(): void
    {
        $this->promptTemplates = app(PromptTemplateService::class)->recent();
    }

    /**
     * Reset the form to create mode with configured default voice and language.
     */
    private function resetForm(): void
    {
        $this->editingTemplateId = null;
        $this->title = '';
        $this->masterPrompt = '';
        $this->promptText = '';
        $this->setSelectedLanguage(app(GeminiLanguageService::class)->default()['code']);
        $this->setSelectedVoice(app(GeminiVoiceService::class)->default()['name']);
        $this->resetValidation();
    }

    /**
     * Set the selected language to a supported Gemini-TTS language code.
     */
    private function setSelectedLanguage(string $languageCode): void
    {
        $languageService = app(GeminiLanguageService::class);
        $language = $languageService->find($languageCode) ?? $languageService->default();

        $this->selectedLanguageCode = $language['code'];
        $this->resetValidation('selectedLanguageCode');
    }

    /**
     * Set the selected voice and rebuild the dependent voice options list.
     */
    private function setSelectedVoice(string $voiceName): void
    {
        $voiceService = app(GeminiVoiceService::class);
        $voice = $voiceService->find($voiceName) ?? $voiceService->default();

        $this->selectedVoiceGender = $voice['gender'];
        $this->voiceGenerators = $this->voiceOptionsForGender($voiceService, $voice['gender']);
        $this->selectedVoice = $voice['name'];
        $this->resetValidation('selectedVoice');
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
