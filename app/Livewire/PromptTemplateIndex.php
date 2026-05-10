<?php

namespace App\Livewire;

use App\Actions\PromptTemplates\ListPromptTemplatesAction;
use App\Actions\PromptTemplates\RemovePromptTemplateAction;
use App\Actions\PromptTemplates\Requests\ListPromptTemplatesRequest;
use App\Actions\PromptTemplates\Requests\RemovePromptTemplateRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Prompt Templates')]
class PromptTemplateIndex extends Component
{
    private const VIEW = 'livewire.prompt-template-index';

    private const SUCCESS_TEMPLATE_REMOVED = 'Prompt template has been removed.';

    private const ERROR_TEMPLATE_NOT_FOUND = 'Prompt template was not found.';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    protected ListPromptTemplatesAction $listPromptTemplates;

    protected RemovePromptTemplateAction $removePromptTemplate;

    /**
     * Hydrate action dependencies for each Livewire request.
     */
    public function boot(
        ListPromptTemplatesAction $listPromptTemplates,
        RemovePromptTemplateAction $removePromptTemplate,
    ): void {
        $this->listPromptTemplates = $listPromptTemplates;
        $this->removePromptTemplate = $removePromptTemplate;
    }

    /**
     * Render the prompt templates index page.
     */
    public function render(): View
    {
        return view(self::VIEW);
    }

    /**
     * Remove a reusable prompt template from the index table.
     */
    public function remove(int $templateId): void
    {
        if (! $this->removePromptTemplate->handle(new RemovePromptTemplateRequest($templateId))) {
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_TEMPLATE_REMOVED;
    }

    /**
     * Return saved templates for the CRUD table.
     *
     * @return list<array{id: int, title: string, master_prompt: string|null, prompt_text: string|null, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}>
     */
    #[Computed]
    public function promptTemplates(): array
    {
        return $this->listPromptTemplates->handle(new ListPromptTemplatesRequest);
    }

    /**
     * Return a flag icon for the template language region code.
     */
    public function languageFlag(?string $languageCode): string
    {
        $parts = preg_split('/[-_]/', trim((string) $languageCode));
        $regionCode = strtoupper($parts[1] ?? '');

        if (! preg_match('/^[A-Z]{2}$/', $regionCode)) {
            return '';
        }

        return $this->regionalIndicator($regionCode[0]) . $this->regionalIndicator($regionCode[1]);
    }

    private function regionalIndicator(string $letter): string
    {
        return mb_chr(0x1F1E6 + (ord($letter) - ord('A')));
    }
}
