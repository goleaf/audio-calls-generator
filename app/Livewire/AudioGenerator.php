<?php

namespace App\Livewire;

use App\Exceptions\AudioGenerationException;
use App\Models\AudioGeneration;
use App\Services\AudioGenerationHistoryService;
use App\Services\AudioVoicePreferenceService;
use App\Services\GeminiAudioService;
use App\Services\GeminiVoiceService;
use App\Services\MasterPromptService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Gemini Audio Generator')]
class AudioGenerator extends Component
{
    private const VIEW = 'livewire.audio-generator';

    private const SUCCESS_MASTER_PROMPT_SAVED = 'Master prompt has been saved.';

    private const SUCCESS_WAV_GENERATED = 'WAV audio has been generated.';

    private const SUCCESS_PROMPT_LOADED = 'Prompt has been loaded.';

    private const SUCCESS_PROMPT_REMOVED = 'Prompt has been removed.';

    private const ERROR_PROMPT_NOT_FOUND = 'Prompt was not found.';

    private const ERROR_UNEXPECTED_GENERATION = 'Audio generation failed unexpectedly.';

    public string $masterPrompt = '';

    public string $text = '';

    public string $selectedVoiceGender = '';

    public string $selectedVoice = '';

    public ?string $wavPath = null;

    public ?string $wavUrl = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public ?int $audioGenerationId = null;

    /** @var list<array<string, mixed>> */
    public array $savedGenerations = [];

    /** @var list<string> */
    public array $voiceGenders = [];

    /** @var list<array{name: string, gender: string}> */
    public array $voiceGenerators = [];

    /**
     * Initialize the form with the saved master prompt, default voice, and recent history.
     */
    public function mount(): void
    {
        $voiceService = app(GeminiVoiceService::class);

        $this->masterPrompt = app(MasterPromptService::class)->current();
        $this->voiceGenders = $voiceService->genders();
        $this->setSelectedVoice(app(AudioVoicePreferenceService::class)->current()['name']);
        $this->loadSavedGenerations();
    }

    /**
     * Validate and persist the master prompt used for future audio scripts.
     */
    public function saveMasterPrompt(): void
    {
        $validated = $this->validate($this->masterPromptRules(), $this->validationMessages());
        $masterPrompt = app(MasterPromptService::class)->save($validated['masterPrompt']);

        $this->masterPrompt = $masterPrompt->content;
        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_MASTER_PROMPT_SAVED;
    }

    /**
     * Save the current prompt draft, generate WAV audio through Gemini, and persist the result.
     */
    public function generate(): void
    {
        $validated = $this->validate($this->textRules(), $this->validationMessages());

        $this->resetAudioResults(keepGeneration: true);
        $history = app(AudioGenerationHistoryService::class);
        $masterPrompt = $this->nullableString($this->masterPrompt);
        $generation = $history->saveDraft($this->audioGenerationId, $masterPrompt, $validated['text'], $validated['selectedVoice']);

        $this->syncGenerationState($history, $generation);

        try {
            $audio = app(GeminiAudioService::class)->generateWav($validated['text'], $validated['selectedVoice']);
            $generation = $history->markWavGenerated($generation, $audio);

            $this->applyWavResult($audio);
            $this->syncGenerationState($history, $generation);
            $this->successMessage = self::SUCCESS_WAV_GENERATED;
        } catch (AudioGenerationException $exception) {
            $generation = $history->markWavFailed($generation, $validated['selectedVoice'], $exception->getMessage());

            $this->syncGenerationState($history, $generation);
            $this->errorMessage = $exception->getMessage();

            return;
        } catch (Throwable $exception) {
            report($exception);

            $generation = $history->markWavFailed($generation, $validated['selectedVoice'], self::ERROR_UNEXPECTED_GENERATION);

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
        $this->setSelectedVoice((string) $generation->tts_voice);
        $this->audioGenerationId = $generation->id;
        $this->wavPath = $generation->audio_path;
        $this->wavUrl = $generation->audio_url;
        $this->errorMessage = null;
        $this->successMessage = self::SUCCESS_PROMPT_LOADED;
    }

    /**
     * Select a gender, refresh the available voice names, and save the voice preference.
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
        $this->saveCurrentVoicePreference();
    }

    /**
     * Select and save a voice preference that belongs to the currently selected gender.
     */
    public function selectVoice(string $voiceName): void
    {
        $voiceService = app(GeminiVoiceService::class);
        $voice = $voiceService->find($voiceName);

        if ($voice === null || $voice['gender'] !== $this->selectedVoiceGender) {
            $this->addError('selectedVoice', $this->validationMessages()['selectedVoice.in']);

            return;
        }

        $this->selectedVoice = $voice['name'];
        $this->resetValidation('selectedVoice');
        $this->saveCurrentVoicePreference();
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
     * Validation rules for generating audio from the current form state.
     *
     * @return array<string, list<mixed>>
     */
    private function textRules(): array
    {
        $voiceService = app(GeminiVoiceService::class);

        return [
            'text' => ['required', 'string', 'min:3', 'max:5000'],
            'selectedVoiceGender' => ['required', 'string', Rule::in($voiceService->genders())],
            'selectedVoice' => ['required', 'string', Rule::in($voiceService->namesForGender($this->selectedVoiceGender))],
        ];
    }

    /**
     * Validation rules for saving the single master prompt.
     *
     * @return array<string, list<string>>
     */
    private function masterPromptRules(): array
    {
        return [
            'masterPrompt' => ['required', 'string', 'min:3', 'max:2000'],
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
            'masterPrompt.required' => 'Enter a master prompt first.',
            'masterPrompt.min' => 'The master prompt must contain at least :min characters.',
            'masterPrompt.max' => 'The master prompt must not be longer than :max characters.',
            'text.required' => 'Enter text to synthesize.',
            'text.min' => 'The script must contain at least :min characters.',
            'text.max' => 'The script must not be longer than :max characters.',
            'selectedVoiceGender.required' => 'Choose a voice gender.',
            'selectedVoiceGender.in' => 'Choose an available voice gender.',
            'selectedVoice.required' => 'Choose a voice generator.',
            'selectedVoice.in' => 'Choose a generator from the selected gender.',
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
     * Normalize empty user input to null before saving optional database fields.
     */
    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Persist the currently selected voice without changing previous prompt history.
     */
    private function saveCurrentVoicePreference(): void
    {
        app(AudioVoicePreferenceService::class)->save($this->selectedVoice);
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
