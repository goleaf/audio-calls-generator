<?php

namespace App\Livewire;

use App\Livewire\Forms\PromptTemplateForm;
use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;
use App\Services\PromptTemplateService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
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

    public PromptTemplateForm $form;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    #[Locked]
    public ?int $editingTemplateId = null;

    /**
     * Load configured defaults when the page is opened.
     */
    public function mount(): void
    {
        $this->form->resetToDefaults();
    }

    /**
     * Validate and create or update a reusable prompt template.
     */
    public function save(): void
    {
        $validated = $this->form->validate();

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
        $this->form->fillFromTemplate($template);
        $this->resetValidation();
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
    }

    /**
     * Refresh voice names when a gender is selected in the template form.
     */
    public function selectVoiceGender(string $gender): void
    {
        $this->form->selectVoiceGender($gender);
        $this->resetValidation('form.selectedVoice');
    }

    /**
     * Render the class-based Livewire component view.
     */
    public function render(): View
    {
        return view(self::VIEW);
    }

    /**
     * Return supported Gemini-TTS languages grouped by readiness.
     *
     * @return array<string, list<array{name: string, code: string, readiness: string, label: string}>>
     */
    #[Computed]
    public function languageGroups(): array
    {
        return app(GeminiLanguageService::class)->groups();
    }

    /**
     * Return supported voice genders for the template form.
     *
     * @return list<string>
     */
    #[Computed]
    public function voiceGenders(): array
    {
        return app(GeminiVoiceService::class)->genders();
    }

    /**
     * Return voice names for the selected gender.
     *
     * @return list<array{name: string, gender: string}>
     */
    #[Computed]
    public function voiceGenerators(): array
    {
        return $this->form->voiceGenerators();
    }

    /**
     * Return saved templates for the CRUD table.
     *
     * @return list<array{id: int, title: string, master_prompt: string|null, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}>
     */
    #[Computed]
    public function promptTemplates(): array
    {
        return app(PromptTemplateService::class)->recent();
    }

    /**
     * Reset the form to create mode with configured default voice and language.
     */
    private function resetForm(): void
    {
        $this->editingTemplateId = null;
        $this->form->resetToDefaults();
        $this->resetValidation();
    }
}
