<?php

namespace App\Livewire;

use App\Actions\AudioGenerations\GenerateAudioAction;
use App\Actions\AudioGenerations\LoadAudioGeneratorDataAction;
use App\Actions\AudioGenerations\RemovePreviousPromptAction;
use App\Actions\AudioGenerations\Requests\GenerateAudioRequest;
use App\Actions\AudioGenerations\Requests\LoadAudioGeneratorDataRequest;
use App\Actions\AudioGenerations\Requests\RemovePreviousPromptRequest;
use App\Actions\AudioGenerations\Requests\UsePreviousPromptRequest;
use App\Actions\AudioGenerations\Requests\UsePromptTemplateRequest;
use App\Actions\AudioGenerations\UsePreviousPromptAction;
use App\Actions\AudioGenerations\UsePromptTemplateAction;
use App\Rules\AudioGenerations\GenerateAudioRules;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

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

    #[Locked]
    public ?int $audioGenerationId = null;

    /** @var list<array<string, mixed>> */
    public array $savedGenerations = [];

    /** @var list<array{id: int, title: string, master_prompt: string|null, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}> */
    public array $promptTemplates = [];

    /** @var array{title: string, master_prompt: string, prompt_text: string, language_label: string, tts_voice_label: string}|null */
    #[Locked]
    public ?array $selectedTemplate = null;

    protected GenerateAudioAction $generateAudio;

    protected GenerateAudioRules $generateAudioRules;

    protected LoadAudioGeneratorDataAction $loadAudioGeneratorData;

    protected RemovePreviousPromptAction $removePreviousPrompt;

    protected UsePreviousPromptAction $usePreviousPrompt;

    protected UsePromptTemplateAction $usePromptTemplate;

    /**
     * Hydrate non-serializable dependencies for each Livewire request.
     */
    public function boot(
        GenerateAudioAction $generateAudio,
        GenerateAudioRules $generateAudioRules,
        LoadAudioGeneratorDataAction $loadAudioGeneratorData,
        RemovePreviousPromptAction $removePreviousPrompt,
        UsePreviousPromptAction $usePreviousPrompt,
        UsePromptTemplateAction $usePromptTemplate,
    ): void {
        $this->generateAudio = $generateAudio;
        $this->generateAudioRules = $generateAudioRules;
        $this->loadAudioGeneratorData = $loadAudioGeneratorData;
        $this->removePreviousPrompt = $removePreviousPrompt;
        $this->usePreviousPrompt = $usePreviousPrompt;
        $this->usePromptTemplate = $usePromptTemplate;
    }

    /**
     * Initialize the form with reusable templates and recent generation history.
     */
    public function mount(): void
    {
        $this->loadGeneratorData();
    }

    /**
     * Save the current prompt draft, generate WAV audio through Gemini, and persist the result.
     */
    public function generate(): void
    {
        $validated = $this->validate($this->generateAudioRules->rules(), $this->generateAudioRules->messages());
        $this->resetAudioResults(keepGeneration: true);
        $result = $this->generateAudio->handle(new GenerateAudioRequest(
            (int) $validated['selectedPromptTemplateId'],
            $this->audioGenerationId,
        ));

        if ($result['template_state'] === null) {
            $this->successMessage = null;
            $this->errorMessage = $result['error_message'] ?? self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->applyTemplateState($result['template_state']);

        if ($result['generation'] !== null) {
            $this->audioGenerationId = $result['generation']->id;
        }

        if ($result['audio'] !== null) {
            $this->applyWavResult($result['audio']);
            $this->loadGeneratorData();
            $this->successMessage = self::SUCCESS_WAV_GENERATED;

            return;
        }

        $this->loadGeneratorData();
        $this->errorMessage = $result['error_message'] ?? self::ERROR_UNEXPECTED_GENERATION;
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
        $state = $this->usePreviousPrompt->handle(new UsePreviousPromptRequest($generationId));

        if ($state === null) {
            return;
        }

        $this->applyPreviousPromptState($state);
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

        $template = $this->usePromptTemplate->handle(new UsePromptTemplateRequest($id));

        if ($template === null) {
            $this->selectedPromptTemplateId = '';
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_TEMPLATE_NOT_FOUND;

            return;
        }

        $this->selectedPromptTemplateId = (string) $template['template']->id;
        $this->applyTemplateState($template['state']);
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
        if (! $this->removePreviousPrompt->handle(new RemovePreviousPromptRequest($generationId))) {
            $this->successMessage = null;
            $this->errorMessage = self::ERROR_PROMPT_NOT_FOUND;

            return;
        }

        if ($this->audioGenerationId === $generationId) {
            $this->audioGenerationId = null;
            $this->wavPath = null;
            $this->wavUrl = null;
        }

        $this->loadGeneratorData();
        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_PROMPT_REMOVED;
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
     * Refresh the reusable template selector and generation history list.
     */
    private function loadGeneratorData(): void
    {
        $data = $this->loadAudioGeneratorData->handle(new LoadAudioGeneratorDataRequest);

        $this->promptTemplates = $data['prompt_templates'];
        $this->savedGenerations = $data['saved_generations'];
    }

    /**
     * Apply every saved prompt template setting to the audio generation state.
     *
     * @param  array{master_prompt: string, text: string, selected_language_code: string, selected_voice_gender: string, selected_voice: string, selected_template: array{title: string, master_prompt: string, prompt_text: string, language_label: string, tts_voice_label: string}}  $state
     */
    private function applyTemplateState(array $state): void
    {
        $this->masterPrompt = $state['master_prompt'];
        $this->text = $state['text'];
        $this->selectedLanguageCode = $state['selected_language_code'];
        $this->selectedVoiceGender = $state['selected_voice_gender'];
        $this->selectedVoice = $state['selected_voice'];
        $this->selectedTemplate = $state['selected_template'];
        $this->resetValidation('selectedPromptTemplateId');
    }

    /**
     * Apply a previous generation to the current preview state.
     *
     * @param  array{master_prompt: string, text: string, selected_voice: string, selected_voice_gender: string, selected_language_code: string, audio_generation_id: int, wav_path: string|null, wav_url: string|null}  $state
     */
    private function applyPreviousPromptState(array $state): void
    {
        $this->masterPrompt = $state['master_prompt'];
        $this->text = $state['text'];
        $this->selectedVoice = $state['selected_voice'];
        $this->selectedVoiceGender = $state['selected_voice_gender'];
        $this->selectedLanguageCode = $state['selected_language_code'];
        $this->selectedTemplate = null;
        $this->audioGenerationId = $state['audio_generation_id'];
        $this->wavPath = $state['wav_path'];
        $this->wavUrl = $state['wav_url'];
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
}
