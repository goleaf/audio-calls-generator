<?php

use App\Exceptions\AudioGenerationException;
use App\Livewire\AudioGenerator;
use App\Models\AudioGeneration;
use App\Models\PromptTemplate;
use App\Services\GeminiAudioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('audio generator page renders as a template-only generator', function () {
    $this->get(route('audio.generator'))
        ->assertSuccessful()
        ->assertSee('Prompt templates')
        ->assertSee('Audio generator')
        ->assertSee('Prompt template')
        ->assertSee('Generate audio')
        ->assertSee('Create templates on the')
        ->assertSeeHtml('data-icon="notebook-pen"')
        ->assertSeeHtml('data-icon="audio-lines"')
        ->assertSeeHtml('data-icon="history"')
        ->assertDontSee('Master prompt')
        ->assertDontSee('Save master prompt')
        ->assertDontSeeHtml('<span>Language</span>')
        ->assertDontSee('English (United States) - en-US')
        ->assertDontSee('Voice gender')
        ->assertDontSee('Voice generator')
        ->assertDontSee('Female')
        ->assertDontSee('Male')
        ->assertDontSee('Additional prompt')
        ->assertDontSee('Download WAV');
});

test('audio generator loads every saved prompt template setting', function () {
    $template = PromptTemplate::factory()->create([
        'title' => 'Follow up call',
        'master_prompt' => 'Speak as a careful support specialist.',
        'prompt_text' => 'Ask the caller if they need more help with their order.',
        'language_code' => 'lt-LT',
        'language_name' => 'Lithuanian (Lithuania)',
        'language_readiness' => 'Preview',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);

    Livewire::test(AudioGenerator::class)
        ->assertSet('promptTemplates.0.title', 'Follow up call')
        ->assertSee('Follow up call')
        ->call('usePromptTemplate', $template->id)
        ->assertSet('selectedPromptTemplateId', (string) $template->id)
        ->assertSet('masterPrompt', 'Speak as a careful support specialist.')
        ->assertSet('selectedLanguageCode', 'lt-LT')
        ->assertSet('selectedVoiceGender', 'Male')
        ->assertSet('selectedVoice', 'Puck')
        ->assertSet('text', 'Ask the caller if they need more help with their order.')
        ->assertSet('selectedTemplate.title', 'Follow up call')
        ->assertSee('Male - Puck')
        ->assertSee('Lithuanian (Lithuania) - lt-LT')
        ->assertSet('successMessage', 'Prompt template has been loaded.');
});

test('template is required before generating audio', function () {
    Livewire::test(AudioGenerator::class)
        ->call('generate')
        ->assertHasErrors(['selectedPromptTemplateId' => ['required']])
        ->assertSee('Choose a prompt template first.');
});

test('selected template must exist before generating audio', function () {
    Livewire::test(AudioGenerator::class)
        ->set('selectedPromptTemplateId', '999')
        ->call('generate')
        ->assertHasErrors(['selectedPromptTemplateId' => ['exists']])
        ->assertSee('Choose an available prompt template.');
});

test('it generates wav audio using the selected prompt template settings', function () {
    $template = PromptTemplate::factory()->create([
        'title' => 'Lithuanian callback',
        'master_prompt' => 'Speak in a calm billing support style.',
        'prompt_text' => 'Your invoice is ready for review.',
        'language_code' => 'lt-LT',
        'language_name' => 'Lithuanian (Lithuania)',
        'language_readiness' => 'Preview',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);

    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Your invoice is ready for review.', 'Puck', 'lt-LT')
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
            'language_code' => 'lt-LT',
            'language_name' => 'Lithuanian (Lithuania)',
            'language_readiness' => 'Preview',
            'language_label' => 'Lithuanian (Lithuania) - lt-LT',
        ]);

    Livewire::test(AudioGenerator::class)
        ->call('usePromptTemplate', $template->id)
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
        'prompt_brief' => 'Speak in a calm billing support style.',
        'master_prompt' => 'Speak in a calm billing support style.',
        'text' => 'Your invoice is ready for review.',
        'status' => AudioGeneration::STATUS_WAV_GENERATED,
        'audio_path' => 'audio/demo.wav',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
        'tts_language_code' => 'lt-LT',
        'tts_language_name' => 'Lithuanian (Lithuania)',
        'tts_language_readiness' => 'Preview',
    ]);
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

test('it shows generation errors to the user', function () {
    $template = PromptTemplate::factory()->create([
        'prompt_text' => 'Narration script',
        'tts_voice' => 'Kore',
        'tts_voice_gender' => 'Female',
        'tts_voice_label' => 'Female - Kore',
        'language_code' => 'en-US',
        'language_name' => 'English (United States)',
        'language_readiness' => 'GA',
    ]);

    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Narration script', 'Kore', 'en-US')
        ->andThrow(new AudioGenerationException('Gemini API key is not configured.'));

    Livewire::test(AudioGenerator::class)
        ->call('usePromptTemplate', $template->id)
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
