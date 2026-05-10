<?php

namespace App\Livewire;

use App\Actions\PromptTemplates\EditPromptTemplateAction;
use App\Actions\PromptTemplates\ListPromptTemplateLanguagesAction;
use App\Actions\PromptTemplates\ListPromptTemplateVoiceGendersAction;
use App\Actions\PromptTemplates\ListPromptTemplateVoiceGeneratorsAction;
use App\Actions\PromptTemplates\Requests\EditPromptTemplateRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateLanguagesRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateVoiceGendersRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateVoiceGeneratorsRequest;
use App\Actions\PromptTemplates\Requests\ResetPromptTemplateFormRequest;
use App\Actions\PromptTemplates\Requests\SavePromptTemplateRequest;
use App\Actions\PromptTemplates\Requests\SelectPromptTemplateVoiceGenderRequest;
use App\Actions\PromptTemplates\ResetPromptTemplateFormAction;
use App\Actions\PromptTemplates\SavePromptTemplateAction;
use App\Actions\PromptTemplates\SelectPromptTemplateVoiceGenderAction;
use App\Livewire\Forms\PromptTemplateForm;
use App\Models\PromptTemplate;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Prompt Template')]
class PromptTemplateFormPage extends Component
{
    private const VIEW = 'livewire.prompt-template-form-page';

    private const INDEX_ROUTE = 'audio.prompt-templates';

    private const ERROR_TEMPLATE_NOT_FOUND = 'Prompt template was not found.';

    public PromptTemplateForm $form;

    public ?string $errorMessage = null;

    #[Locked]
    public ?int $editingTemplateId = null;

    /**
     * Load defaults for create mode or saved values for edit mode.
     */
    public function mount(?PromptTemplate $promptTemplate = null): void
    {
        if ($promptTemplate === null) {
            $this->resetForm();

            return;
        }

        $state = app(EditPromptTemplateAction::class)->handle(new EditPromptTemplateRequest($promptTemplate->id));

        if ($state === null) {
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;
            $this->resetForm();

            return;
        }

        $this->editingTemplateId = $state['id'];
        $this->form->fillFromState($state);
    }

    /**
     * Validate and create or update a reusable prompt template.
     */
    public function save(): void
    {
        $validated = $this->form->validate();
        $template = app(SavePromptTemplateAction::class)->handle(new SavePromptTemplateRequest(
            $this->editingTemplateId,
            $validated['title'],
            $validated['masterPrompt'],
            $validated['promptText'],
            $validated['selectedLanguageCode'],
            $validated['selectedVoice'],
        ));

        if ($template === null) {
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->redirectRoute(self::INDEX_ROUTE);
    }

    /**
     * Return to the index page without changing saved templates.
     */
    public function cancelEdit(): void
    {
        $this->redirectRoute(self::INDEX_ROUTE);
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
     * Render the prompt template form page.
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
