<?php

use App\Exceptions\AudioGenerationException;
use App\Livewire\AudioGenerator;
use App\Models\AudioGeneration;
use App\Models\AudioVoicePreference;
use App\Models\MasterPrompt;
use App\Services\GeminiAudioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('audio generator page renders', function () {
    $this->get(route('audio.generator'))
        ->assertSuccessful()
        ->assertSee('Master prompt')
        ->assertSee('Voice gender')
        ->assertSee('Voice generator')
        ->assertSee('Female')
        ->assertSee('Male')
        ->assertSee('Kore')
        ->assertDontSee('Female - Kore')
        ->assertDontSee('Male - Puck')
        ->assertSee('Save master prompt')
        ->assertDontSee('Generate prompt')
        ->assertSee('Text')
        ->assertSee('0 / 5000')
        ->assertSee('Generate audio')
        ->assertSeeHtml('data-icon="notebook-pen"')
        ->assertSeeHtml('data-icon="save"')
        ->assertSeeHtml('data-icon="users"')
        ->assertSeeHtml('data-icon="mic"')
        ->assertSeeHtml('data-icon="file-text"')
        ->assertSeeHtml('data-icon="audio-lines"')
        ->assertSeeHtml('data-icon="history"')
        ->assertDontSee('Voice production desk')
        ->assertDontSee('Signal chain')
        ->assertDontSee('Tone cues')
        ->assertDontSee('Playback console')
        ->assertDontSee('Additional prompt')
        ->assertDontSee('Download WAV')
        ->assertDontSee('Creating script');
});

test('voice generator names are filtered by selected gender', function () {
    Livewire::test(AudioGenerator::class)
        ->assertSet('selectedVoiceGender', 'Female')
        ->assertSet('selectedVoice', 'Kore')
        ->assertSet('voiceGenerators.0.name', 'Kore')
        ->assertSee('Kore')
        ->assertDontSee('Puck')
        ->call('selectVoiceGender', 'Male')
        ->assertSet('selectedVoiceGender', 'Male')
        ->assertSet('selectedVoice', 'Puck')
        ->assertSet('voiceGenerators.0.name', 'Puck')
        ->assertSee('Puck')
        ->assertDontSee('Kore');
});

test('it saves the selected voice preference without creating a previous prompt', function () {
    Livewire::test(AudioGenerator::class)
        ->set('masterPrompt', 'Persist selected voice with this prompt.')
        ->set('text', 'Persist selected voice with this text.')
        ->call('selectVoiceGender', 'Male')
        ->assertSet('audioGenerationId', null)
        ->assertSet('selectedVoiceGender', 'Male')
        ->assertSet('selectedVoice', 'Puck')
        ->assertSet('savedGenerations', [])
        ->call('selectVoice', 'Charon')
        ->assertSet('audioGenerationId', null)
        ->assertSet('selectedVoiceGender', 'Male')
        ->assertSet('selectedVoice', 'Charon')
        ->assertSet('savedGenerations', []);

    expect(AudioGeneration::query()->count())->toBe(0);

    $this->assertDatabaseHas('audio_voice_preferences', [
        'key' => AudioVoicePreference::CURRENT_KEY,
        'tts_voice' => 'Charon',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Charon',
    ]);
});

test('it loads the saved voice preference on the page', function () {
    AudioVoicePreference::factory()->create([
        'key' => AudioVoicePreference::CURRENT_KEY,
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);

    Livewire::test(AudioGenerator::class)
        ->assertSet('selectedVoiceGender', 'Male')
        ->assertSet('selectedVoice', 'Puck')
        ->assertSet('voiceGenerators.0.name', 'Puck');
});

test('it updates the saved voice draft when generating audio', function () {
    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Narration script', 'Puck')
        ->andReturn([
            'path' => 'audio/demo.wav',
            'url' => '/storage/audio/demo.wav',
            'name' => 'demo.wav',
            'disk' => 'public',
            'mime_type' => 'audio/wav',
            'size' => 3,
            'voice' => 'Puck',
            'voice_gender' => 'Male',
            'voice_label' => 'Male - Puck',
        ]);

    Livewire::test(AudioGenerator::class)
        ->set('text', 'Narration script')
        ->call('selectVoiceGender', 'Male')
        ->call('generate')
        ->assertSet('audioGenerationId', 1)
        ->assertSet('savedGenerations.0.status', AudioGeneration::STATUS_WAV_GENERATED);

    expect(AudioGeneration::query()->count())->toBe(1);

    $this->assertDatabaseHas('audio_generations', [
        'id' => 1,
        'status' => AudioGeneration::STATUS_WAV_GENERATED,
        'audio_path' => 'audio/demo.wav',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);
});

test('selected voice generator must match selected gender before generating audio', function () {
    Livewire::test(AudioGenerator::class)
        ->set('text', 'Narration script')
        ->call('selectVoiceGender', 'Female')
        ->set('selectedVoice', 'Puck')
        ->call('generate')
        ->assertHasErrors(['selectedVoice' => ['in']])
        ->assertSee('Choose a generator from the selected gender.');
});

test('audio generator page shows saved database generations', function () {
    AudioGeneration::factory()->create([
        'master_prompt' => 'Use an upbeat studio voice.',
        'prompt_brief' => 'Welcome the first caller.',
        'audio_path' => 'audio/saved-demo.wav',
        'audio_url' => '/storage/audio/saved-demo.wav',
        'audio_file_name' => 'saved-demo.wav',
    ]);

    $this->get(route('audio.generator'))
        ->assertSuccessful()
        ->assertSee('Previous prompts')
        ->assertSee('Use')
        ->assertSee('Remove')
        ->assertSee('Use an upbeat studio voice.')
        ->assertSee('saved-demo.wav')
        ->assertSee('Download');
});

test('it removes a saved prompt from previous prompts', function () {
    Storage::fake('public');
    Storage::disk('public')->put('audio/saved-demo.wav', 'wav');

    $generation = AudioGeneration::factory()->create([
        'audio_disk' => 'public',
        'audio_path' => 'audio/saved-demo.wav',
        'audio_url' => '/storage/audio/saved-demo.wav',
        'audio_file_name' => 'saved-demo.wav',
    ]);

    Livewire::test(AudioGenerator::class)
        ->call('usePrompt', $generation->id)
        ->assertSet('audioGenerationId', $generation->id)
        ->call('removePrompt', $generation->id)
        ->assertSet('audioGenerationId', null)
        ->assertSet('wavPath', null)
        ->assertSet('wavUrl', null)
        ->assertSet('savedGenerations', [])
        ->assertSet('successMessage', 'Prompt has been removed.');

    $this->assertDatabaseMissing('audio_generations', [
        'id' => $generation->id,
    ]);

    Storage::disk('public')->assertMissing('audio/saved-demo.wav');
});

test('it saves the master prompt independently', function () {
    Livewire::test(AudioGenerator::class)
        ->set('masterPrompt', 'Write a calm audio script for one speaker.')
        ->call('saveMasterPrompt')
        ->assertHasNoErrors()
        ->assertSet('successMessage', 'Master prompt has been saved.');

    $this->assertDatabaseHas('master_prompts', [
        'key' => MasterPrompt::CURRENT_KEY,
        'content' => 'Write a calm audio script for one speaker.',
    ]);
});

test('it loads the saved master prompt on the page', function () {
    MasterPrompt::factory()->create([
        'key' => MasterPrompt::CURRENT_KEY,
        'content' => 'Keep the narration quiet and precise.',
    ]);

    Livewire::test(AudioGenerator::class)
        ->assertSet('masterPrompt', 'Keep the narration quiet and precise.');
});

test('master prompt is required before saving', function () {
    Livewire::test(AudioGenerator::class)
        ->set('masterPrompt', '')
        ->call('saveMasterPrompt')
        ->assertHasErrors(['masterPrompt' => ['required']])
        ->assertSee('Enter a master prompt first.');
});

test('text is required before generating audio', function () {
    Livewire::test(AudioGenerator::class)
        ->set('text', '')
        ->call('generate')
        ->assertHasErrors(['text' => ['required']])
        ->assertSee('Enter text to synthesize.');
});

test('text length is limited before generating audio', function () {
    Livewire::test(AudioGenerator::class)
        ->set('text', str_repeat('a', 5001))
        ->call('generate')
        ->assertHasErrors(['text' => ['max']]);
});

test('selected voice generator must exist before generating audio', function () {
    Livewire::test(AudioGenerator::class)
        ->set('text', 'Narration script')
        ->set('selectedVoice', 'MissingVoice')
        ->call('generate')
        ->assertHasErrors(['selectedVoice' => ['in']])
        ->assertSee('Choose a generator from the selected gender.');
});

test('it generates only a wav file through the Gemini service', function () {
    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Narration script', 'Puck')
        ->andReturn([
            'path' => 'audio/demo.wav',
            'url' => '/storage/audio/demo.wav',
            'name' => 'demo.wav',
            'disk' => 'public',
            'mime_type' => 'audio/wav',
            'size' => 3,
            'voice' => 'Puck',
            'voice_gender' => 'Male',
            'voice_label' => 'Male - Puck',
        ]);

    Livewire::test(AudioGenerator::class)
        ->set('text', 'Narration script')
        ->call('selectVoiceGender', 'Male')
        ->set('selectedVoice', 'Puck')
        ->call('generate')
        ->assertHasNoErrors()
        ->assertSet('wavPath', 'audio/demo.wav')
        ->assertSet('wavUrl', '/storage/audio/demo.wav')
        ->assertSet('audioGenerationId', 1)
        ->assertSet('savedGenerations.0.audio_path', 'audio/demo.wav')
        ->assertSet('successMessage', 'WAV audio has been generated.')
        ->assertSee('Download WAV')
        ->assertDontSee('Download MP3')
        ->assertDontSee('MP3');

    $this->assertDatabaseHas('audio_generations', [
        'text' => 'Narration script',
        'status' => AudioGeneration::STATUS_WAV_GENERATED,
        'audio_path' => 'audio/demo.wav',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);
});

test('it shows generation errors to the user', function () {
    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Narration script', 'Kore')
        ->andThrow(new AudioGenerationException('Gemini API key is not configured.'));

    Livewire::test(AudioGenerator::class)
        ->set('text', 'Narration script')
        ->call('generate')
        ->assertSet('audioGenerationId', 1)
        ->assertSet('errorMessage', 'Gemini API key is not configured.')
        ->assertSee('Gemini API key is not configured.');

    $this->assertDatabaseHas('audio_generations', [
        'text' => 'Narration script',
        'status' => AudioGeneration::STATUS_FAILED,
        'error_message' => 'Gemini API key is not configured.',
    ]);
});
