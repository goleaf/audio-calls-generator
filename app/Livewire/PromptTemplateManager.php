<?php

namespace App\Livewire;

use App\Actions\PromptTemplates\EditPromptTemplateAction;
use App\Actions\PromptTemplates\ListPromptTemplateLanguagesAction;
use App\Actions\PromptTemplates\ListPromptTemplatesAction;
use App\Actions\PromptTemplates\ListPromptTemplateVoiceGendersAction;
use App\Actions\PromptTemplates\ListPromptTemplateVoiceGeneratorsAction;
use App\Actions\PromptTemplates\RemovePromptTemplateAction;
use App\Actions\PromptTemplates\Requests\EditPromptTemplateRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateLanguagesRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplatesRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateVoiceGendersRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateVoiceGeneratorsRequest;
use App\Actions\PromptTemplates\Requests\RemovePromptTemplateRequest;
use App\Actions\PromptTemplates\Requests\ResetPromptTemplateFormRequest;
use App\Actions\PromptTemplates\Requests\SavePromptTemplateRequest;
use App\Actions\PromptTemplates\Requests\SelectPromptTemplateVoiceGenderRequest;
use App\Actions\PromptTemplates\ResetPromptTemplateFormAction;
use App\Actions\PromptTemplates\SavePromptTemplateAction;
use App\Actions\PromptTemplates\SelectPromptTemplateVoiceGenderAction;
use App\Livewire\Forms\PromptTemplateForm;
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
        $this->form->fillFromState(
            app(ResetPromptTemplateFormAction::class)->handle(new ResetPromptTemplateFormRequest),
        );
    }

    /**
     * Validate and create or update a reusable prompt template.
     */
    public function save(): void
    {
        $validated = $this->form->validate();

        $wasEditing = $this->editingTemplateId !== null;
        $template = app(SavePromptTemplateAction::class)->handle(new SavePromptTemplateRequest(
            $this->editingTemplateId,
            $validated['title'],
            $validated['masterPrompt'],
            $validated['promptText'],
            $validated['selectedLanguageCode'],
            $validated['selectedVoice'],
        ));

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
        $state = app(EditPromptTemplateAction::class)->handle(new EditPromptTemplateRequest($templateId));

        if ($state === null) {
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->editingTemplateId = $state['id'];
        $this->form->fillFromState($state);
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
        if (! app(RemovePromptTemplateAction::class)->handle(new RemovePromptTemplateRequest($templateId))) {
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
        $this->form->fillFromState(
            app(SelectPromptTemplateVoiceGenderAction::class)->handle(new SelectPromptTemplateVoiceGenderRequest($gender)),
        );
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
        return app(ListPromptTemplateLanguagesAction::class)->handle(new ListPromptTemplateLanguagesRequest);
    }

    /**
     * Return supported voice genders for the template form.
     *
     * @return list<string>
     */
    #[Computed]
    public function voiceGenders(): array
    {
        return app(ListPromptTemplateVoiceGendersAction::class)->handle(new ListPromptTemplateVoiceGendersRequest);
    }

    /**
     * Return voice names for the selected gender.
     *
     * @return list<array{name: string, gender: string}>
     */
    #[Computed]
    public function voiceGenerators(): array
    {
        return app(ListPromptTemplateVoiceGeneratorsAction::class)->handle(new ListPromptTemplateVoiceGeneratorsRequest(
            $this->form->selectedVoiceGender,
        ));
    }

    /**
     * Return saved templates for the CRUD table.
     *
     * @return list<array{id: int, title: string, master_prompt: string|null, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}>
     */
    #[Computed]
    public function promptTemplates(): array
    {
        return app(ListPromptTemplatesAction::class)->handle(new ListPromptTemplatesRequest);
    }

    /**
     * Reset the form to create mode with configured default voice and language.
     */
    private function resetForm(): void
    {
        $this->editingTemplateId = null;
        $this->form->fillFromState(
            app(ResetPromptTemplateFormAction::class)->handle(new ResetPromptTemplateFormRequest),
        );
        $this->resetValidation();
    }
}
