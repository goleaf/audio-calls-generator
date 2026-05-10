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
        if (! app(RemovePromptTemplateAction::class)->handle(new RemovePromptTemplateRequest($templateId))) {
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
        return app(ListPromptTemplatesAction::class)->handle(new ListPromptTemplatesRequest);
    }
}
