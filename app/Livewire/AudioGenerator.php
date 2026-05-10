<?php

namespace App\Livewire;

use App\Exceptions\AudioGenerationException;
use App\Models\AudioGeneration;
use App\Models\PromptTemplate;
use App\Services\AudioGenerationHistoryService;
use App\Services\GeminiAudioService;
use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;
use App\Services\PromptTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Gemini Audio Generator')]
class AudioGenerator extends Component
{
    private const VIEW = 'livewire.audio-generator';

    private const SUCCESS_WAV_GENERATED = 'WAV audio has been generated.';

    private const SUCCESS_PROMPT_LOADED = 'Prompt has been loaded.';

    private const SUCCESS_PROMPT_REMOVED = 'Prompt has been removed.';

    private const SUCCESS_TEMPLATE_LOADED = 'Prompt template has been loaded.';

    private const ERROR_PROMPT_NOT_FOUND = 'Prompt was not found.';

    private const ERROR_TEMPLATE_NOT_FOUND = 'Prompt template was not found.';

    private const ERROR_UNEXPECTED_GENERATION = 'Audio generation failed unexpectedly.';

    public string $masterPrompt = '';

    public string $text = '';

    public string $selectedPromptTemplateId = '';

    public string $selectedVoiceGender = '';

    public string $selectedVoice = '';

    public string $selectedLanguageCode = '';

    public ?string $wavPath = null;

    public ?string $wavUrl = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public ?int $audioGenerationId = null;

    /** @var list<array<string, mixed>> */
    public array $savedGenerations = [];

    /** @var list<array{id: int, title: string, master_prompt: string|null, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}> */
    public array $promptTemplates = [];

    /** @var array{title: string, master_prompt: string, prompt_text: string, language_label: string, tts_voice_label: string}|null */
    public ?array $selectedTemplate = null;

    /**
     * Initialize the form with reusable templates and recent generation history.
     */
    public function mount(): void
    {
        $this->loadPromptTemplates();
        $this->loadSavedGenerations();
    }

    /**
     * Save the current prompt draft, generate WAV audio through Gemini, and persist the result.
     */
    public function generate(): void
    {
        $validated = $this->validate($this->templateRules(), $this->validationMessages());
        $template = app(PromptTemplateService::class)->find((int) $validated['selectedPromptTemplateId']);

        if ($template === null) {
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->applyTemplate($template);

        $this->resetAudioResults(keepGeneration: true);
        $history = app(AudioGenerationHistoryService::class);
        $generation = $history->saveDraft(
            $this->audioGenerationId,
            $this->masterPrompt,
            $this->text,
            $this->selectedVoice,
            $this->selectedLanguageCode,
        );

        $this->syncGenerationState($history, $generation);

        try {
            $audio = app(GeminiAudioService::class)->generateWav(
                $this->text,
                $this->selectedVoice,
                $this->selectedLanguageCode,
            );
            $generation = $history->markWavGenerated($generation, $audio);

            $this->applyWavResult([
                'path' => (string) $generation->audio_path,
                'url' => (string) $generation->audio_url,
            ]);
            $this->syncGenerationState($history, $generation);
            $this->successMessage = self::SUCCESS_WAV_GENERATED;
        } catch (AudioGenerationException $exception) {
            $generation = $history->markWavFailed(
                $generation,
                $this->selectedVoice,
                $this->selectedLanguageCode,
                $exception->getMessage(),
            );

            $this->syncGenerationState($history, $generation);
            $this->errorMessage = $exception->getMessage();

            return;
        } catch (Throwable $exception) {
            report($exception);

            $generation = $history->markWavFailed(
                $generation,
                $this->selectedVoice,
                $this->selectedLanguageCode,
                self::ERROR_UNEXPECTED_GENERATION,
            );

            $this->syncGenerationState($history, $generation);
            $this->errorMessage = self::ERROR_UNEXPECTED_GENERATION;

            return;
        }
    }

    /**
     * Render the class-based Livewire component view.
     */
    public function render(): View
    {
        return view(self::VIEW);
    }

    /**
     * Load a previous generation into the form so it can be reused.
     */
    public function usePrompt(int $generationId): void
    {
        $generation = app(AudioGenerationHistoryService::class)->find($generationId);

        if ($generation === null) {
            return;
        }

        $this->masterPrompt = (string) ($generation->master_prompt ?? $generation->prompt_brief);
        $this->text = (string) $generation->text;
        $this->selectedVoice = (string) $generation->tts_voice;
        $this->selectedVoiceGender = (string) $generation->tts_voice_gender;
        $this->selectedLanguageCode = (string) ($generation->tts_language_code ?: app(GeminiLanguageService::class)->default()['code']);
        $this->selectedTemplate = null;
        $this->audioGenerationId = $generation->id;
        $this->wavPath = $generation->audio_path;
        $this->wavUrl = $generation->audio_url;
        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_PROMPT_LOADED;
    }

    /**
     * Load a reusable prompt template into the audio text field.
     */
    public function usePromptTemplate(int|string $templateId): void
    {
        $id = (int) $templateId;

        if ($id < 1) {
            $this->selectedPromptTemplateId = '';
            $this->selectedTemplate = null;
            $this->resetAudioResults();

            return;
        }

        $template = app(PromptTemplateService::class)->find($id);

        if ($template === null) {
            $this->selectedPromptTemplateId = '';
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->selectedPromptTemplateId = (string) $template->id;
        $this->applyTemplate($template);
        $this->wavPath = null;
        $this->wavUrl = null;
        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_TEMPLATE_LOADED;
    }

    /**
     * Remove a saved prompt and its stored WAV file from the history.
     */
    public function removePrompt(int $generationId): void
    {
        $history = app(AudioGenerationHistoryService::class);

        if (! $history->delete($generationId)) {
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_PROMPT_NOT_FOUND;

            return;
        }

        if ($this->audioGenerationId === $generationId) {
            $this->audioGenerationId = null;
            $this->wavPath = null;
            $this->wavUrl = null;
        }

        $this->loadSavedGenerations($history);
        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_PROMPT_REMOVED;
    }

    /**
     * Validation rules for generating audio from a saved prompt template.
     *
     * @return array<string, list<mixed>>
     */
    private function templateRules(): array
    {
        return [
            'selectedPromptTemplateId' => [
                'required',
                'integer',
                Rule::exists('prompt_templates', 'id'),
            ],
        ];
    }

    /**
     * Human-readable validation messages shown in the Livewire view.
     *
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'selectedPromptTemplateId.required' => 'Choose a prompt template first.',
            'selectedPromptTemplateId.integer' => 'Choose an available prompt template.',
            'selectedPromptTemplateId.exists' => 'Choose an available prompt template.',
        ];
    }

    /**
     * Clear audio output state while optionally preserving the active generation id.
     */
    private function resetAudioResults(bool $keepGeneration = false): void
    {
        $this->wavPath = null;
        $this->wavUrl = null;
        $this->errorMessage = null;
        $this->successMessage = null;

        if (! $keepGeneration) {
            $this->audioGenerationId = null;
        }
    }

    /**
     * Refresh the right-side history list from the persistence service.
     */
    private function loadSavedGenerations(?AudioGenerationHistoryService $history = null): void
    {
        $this->savedGenerations = ($history ?? app(AudioGenerationHistoryService::class))->recent();
    }

    /**
     * Refresh the reusable prompt template selector.
     */
    private function loadPromptTemplates(?PromptTemplateService $templates = null): void
    {
        $this->promptTemplates = ($templates ?? app(PromptTemplateService::class))->options();
    }

    /**
     * Apply every saved prompt template setting to the audio generation state.
     */
    private function applyTemplate(PromptTemplate $template): void
    {
        $languageService = app(GeminiLanguageService::class);
        $voiceService = app(GeminiVoiceService::class);
        $language = $languageService->find((string) $template->language_code) ?? $languageService->default();
        $voice = $voiceService->find((string) $template->tts_voice) ?? $voiceService->default();

        $this->masterPrompt = (string) $template->master_prompt;
        $this->text = $template->prompt_text;
        $this->selectedLanguageCode = $language['code'];
        $this->selectedVoiceGender = $voice['gender'];
        $this->selectedVoice = $voice['name'];
        $this->selectedTemplate = [
            'title' => $template->title,
            'master_prompt' => (string) $template->master_prompt,
            'prompt_text' => $template->prompt_text,
            'language_label' => $language['label'],
            'tts_voice_label' => $voice['label'],
        ];
        $this->resetValidation('selectedPromptTemplateId');
    }

    /**
     * Apply generated WAV file paths to the Livewire state.
     *
     * @param  array{path: string, url: string}  $audio
     */
    private function applyWavResult(array $audio): void
    {
        $this->wavPath = $audio['path'];
        $this->wavUrl = $audio['url'];
    }

    /**
     * Keep the active generation id and saved history list synchronized.
     */
    private function syncGenerationState(AudioGenerationHistoryService $history, AudioGeneration $generation): void
    {
        $this->audioGenerationId = $generation->id;
        $this->loadSavedGenerations($history);
    }
}
