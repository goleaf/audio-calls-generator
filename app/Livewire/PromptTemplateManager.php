<?php

namespace App\Livewire;

use App\Services\GeminiLanguageService;
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

    private const SUCCESS_TEMPLATE_REMOVED = 'Prompt template has been removed.';

    private const ERROR_TEMPLATE_NOT_FOUND = 'Prompt template was not found.';

    public string $title = '';

    public string $promptText = '';

    public string $selectedLanguageCode = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    /** @var array<string, list<array{name: string, code: string, readiness: string, label: string}>> */
    public array $languageGroups = [];

    /** @var list<array{id: int, title: string, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null}> */
    public array $promptTemplates = [];

    /**
     * Load supported languages and prompt templates when the page is opened.
     */
    public function mount(): void
    {
        $languageService = app(GeminiLanguageService::class);

        $this->languageGroups = $languageService->groups();
        $this->selectedLanguageCode = $languageService->default()['code'];
        $this->loadPromptTemplates();
    }

    /**
     * Validate and save a reusable prompt template.
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules(), $this->validationMessages());

        app(PromptTemplateService::class)->create(
            $validated['title'],
            $validated['promptText'],
            $validated['selectedLanguageCode'],
        );

        $this->title = '';
        $this->promptText = '';
        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_TEMPLATE_SAVED;
        $this->loadPromptTemplates();
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
        return [
            'title' => ['required', 'string', 'min:2', 'max:120'],
            'selectedLanguageCode' => ['required', 'string', Rule::in(app(GeminiLanguageService::class)->codes())],
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
            'selectedLanguageCode.required' => 'Choose a language.',
            'selectedLanguageCode.in' => 'Choose an available language.',
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
}
