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
use App\Rules\PromptTemplates\PromptTemplateRules;
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

    protected EditPromptTemplateAction $editPromptTemplate;

    protected ListPromptTemplateLanguagesAction $listPromptTemplateLanguages;

    protected ListPromptTemplateVoiceGendersAction $listPromptTemplateVoiceGenders;

    protected ListPromptTemplateVoiceGeneratorsAction $listPromptTemplateVoiceGenerators;

    protected PromptTemplateRules $promptTemplateRules;

    protected ResetPromptTemplateFormAction $resetPromptTemplateForm;

    protected SavePromptTemplateAction $savePromptTemplate;

    protected SelectPromptTemplateVoiceGenderAction $selectPromptTemplateVoiceGender;

    /**
     * Hydrate action and rule dependencies for each Livewire request.
     */
    public function boot(
        EditPromptTemplateAction $editPromptTemplate,
        ListPromptTemplateLanguagesAction $listPromptTemplateLanguages,
        ListPromptTemplateVoiceGendersAction $listPromptTemplateVoiceGenders,
        ListPromptTemplateVoiceGeneratorsAction $listPromptTemplateVoiceGenerators,
        PromptTemplateRules $promptTemplateRules,
        ResetPromptTemplateFormAction $resetPromptTemplateForm,
        SavePromptTemplateAction $savePromptTemplate,
        SelectPromptTemplateVoiceGenderAction $selectPromptTemplateVoiceGender,
    ): void {
        $this->editPromptTemplate = $editPromptTemplate;
        $this->listPromptTemplateLanguages = $listPromptTemplateLanguages;
        $this->listPromptTemplateVoiceGenders = $listPromptTemplateVoiceGenders;
        $this->listPromptTemplateVoiceGenerators = $listPromptTemplateVoiceGenerators;
        $this->promptTemplateRules = $promptTemplateRules;
        $this->resetPromptTemplateForm = $resetPromptTemplateForm;
        $this->savePromptTemplate = $savePromptTemplate;
        $this->selectPromptTemplateVoiceGender = $selectPromptTemplateVoiceGender;
    }

    /**
     * Load defaults for create mode or saved values for edit mode.
     */
    public function mount(?PromptTemplate $promptTemplate = null): void
    {
        if ($promptTemplate === null) {
            $this->resetForm();

            return;
        }

        $state = $this->editPromptTemplate->handle(new EditPromptTemplateRequest($promptTemplate->id));

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
        $validated = $this->form->validate(
            $this->promptTemplateRules->rules($this->form->selectedVoiceGender),
            $this->promptTemplateRules->messages(),
        );
        $template = $this->savePromptTemplate->handle(new SavePromptTemplateRequest(
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
            $this->selectPromptTemplateVoiceGender->handle(new SelectPromptTemplateVoiceGenderRequest($gender)),
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
        return $this->listPromptTemplateLanguages->handle(new ListPromptTemplateLanguagesRequest);
    }

    /**
     * Return supported voice genders for the template form.
     *
     * @return list<string>
     */
    #[Computed]
    public function voiceGenders(): array
    {
        return $this->listPromptTemplateVoiceGenders->handle(new ListPromptTemplateVoiceGendersRequest);
    }

    /**
     * Return voice names for the selected gender.
     *
     * @return list<array{name: string, gender: string}>
     */
    #[Computed]
    public function voiceGenerators(): array
    {
        return $this->listPromptTemplateVoiceGenerators->handle(new ListPromptTemplateVoiceGeneratorsRequest(
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
            $this->resetPromptTemplateForm->handle(new ResetPromptTemplateFormRequest),
        );
        $this->resetValidation();
    }
}
