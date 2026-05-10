<?php

use App\Exceptions\AudioGenerationException;
use App\Livewire\AudioGenerator;
use App\Models\AudioGeneration;
use App\Models\PromptTemplate;
use App\Services\GeminiAudioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.gemini.model', 'gemini-3.1-flash-tts-preview');
    config()->set('services.gemini.audio.sample_rate', 24000);
    config()->set('services.gemini.audio.channels', 1);
    config()->set('services.gemini.audio.sample_width', 2);
});

test('it saves audio generation information from the selected prompt template', function () {
    $template = PromptTemplate::factory()->create([
        'title' => 'Demo welcome',
        'master_prompt' => 'Write a concise announcement.',
        'prompt_text' => 'Welcome to the demo.',
        'language_code' => 'en-US',
        'language_name' => 'English (United States)',
        'language_readiness' => 'GA',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);

    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Welcome to the demo.', 'Puck', 'en-US')
        ->andReturn([
            'path' => 'audio/demo.wav',
            'url' => '/storage/audio/demo.wav',
            'name' => 'demo.wav',
            'disk' => 'public',
            'mime_type' => 'audio/wav',
            'size' => 512,
            'voice' => 'Puck',
            'voice_gender' => 'Male',
            'voice_label' => 'Male - Puck',
            'language_code' => 'en-US',
            'language_name' => 'English (United States)',
            'language_readiness' => 'GA',
            'language_label' => 'English (United States) - en-US',
        ]);

    Livewire::test(AudioGenerator::class)
        ->call('usePromptTemplate', $template->id)
        ->call('generate')
        ->assertSet('audioGenerationId', 1)
        ->assertSet('savedGenerations.0.audio_path', 'audio/demo.wav')
        ->assertSee('demo.wav');

    expect(AudioGeneration::query()->count())->toBe(1);

    $this->assertDatabaseHas('audio_generations', [
        'id' => 1,
        'prompt_brief' => 'Write a concise announcement.',
        'master_prompt' => 'Write a concise announcement.',
        'text' => 'Welcome to the demo.',
        'status' => AudioGeneration::STATUS_WAV_GENERATED,
        'audio_disk' => 'public',
        'audio_path' => 'audio/demo.wav',
        'audio_url' => '/storage/audio/demo.wav',
        'audio_file_name' => 'demo.wav',
        'audio_mime_type' => 'audio/wav',
        'audio_size_bytes' => 512,
        'tts_model' => 'gemini-3.1-flash-tts-preview',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
        'tts_language_code' => 'en-US',
        'tts_language_name' => 'English (United States)',
        'tts_language_readiness' => 'GA',
        'audio_sample_rate' => 24000,
        'audio_channels' => 1,
        'audio_sample_width' => 2,
    ]);
});

test('it saves the template prompt before calling Gemini to generate audio', function () {
    $template = PromptTemplate::factory()->create([
        'master_prompt' => 'Persist this master prompt immediately.',
        'prompt_text' => 'Save this text before audio exists.',
        'language_code' => 'en-US',
        'language_name' => 'English (United States)',
        'language_readiness' => 'GA',
        'tts_voice' => 'Kore',
        'tts_voice_gender' => 'Female',
        'tts_voice_label' => 'Female - Kore',
    ]);

    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Save this text before audio exists.', 'Kore', 'en-US')
        ->andReturnUsing(function (): array {
            expect(AudioGeneration::query()
                ->where('master_prompt', 'Persist this master prompt immediately.')
                ->where('text', 'Save this text before audio exists.')
                ->where('status', AudioGeneration::STATUS_DRAFT)
                ->exists())->toBeTrue();

            return [
                'path' => 'audio/saved-before-call.wav',
                'url' => '/storage/audio/saved-before-call.wav',
                'name' => 'saved-before-call.wav',
                'disk' => 'public',
                'mime_type' => 'audio/wav',
                'size' => 128,
                'voice' => 'Kore',
                'voice_gender' => 'Female',
                'voice_label' => 'Female - Kore',
                'language_code' => 'en-US',
                'language_name' => 'English (United States)',
                'language_readiness' => 'GA',
                'language_label' => 'English (United States) - en-US',
            ];
        });

    Livewire::test(AudioGenerator::class)
        ->call('usePromptTemplate', $template->id)
        ->call('generate')
        ->assertSet('audioGenerationId', 1)
        ->assertSet('savedGenerations.0.audio_path', 'audio/saved-before-call.wav');

    $this->assertDatabaseHas('audio_generations', [
        'id' => 1,
        'master_prompt' => 'Persist this master prompt immediately.',
        'text' => 'Save this text before audio exists.',
        'status' => AudioGeneration::STATUS_WAV_GENERATED,
        'audio_path' => 'audio/saved-before-call.wav',
        'tts_language_code' => 'en-US',
    ]);
});

test('it normalizes generated audio urls before saving them for playback', function () {
    $template = PromptTemplate::factory()->create([
        'prompt_text' => 'Direct text',
        'tts_voice' => 'Kore',
        'tts_voice_gender' => 'Female',
        'tts_voice_label' => 'Female - Kore',
    ]);

    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Direct text', 'Kore', 'en-US')
        ->andReturn([
            'path' => 'audio/direct.wav',
            'url' => 'http://audio-calls-generator.test/storage/audio/direct.wav',
            'name' => 'direct.wav',
            'disk' => 'public',
            'mime_type' => 'audio/wav',
            'size' => 256,
            'voice' => 'Kore',
            'voice_gender' => 'Female',
            'voice_label' => 'Female - Kore',
            'language_code' => 'en-US',
            'language_name' => 'English (United States)',
            'language_readiness' => 'GA',
            'language_label' => 'English (United States) - en-US',
        ]);

    Livewire::test(AudioGenerator::class)
        ->call('usePromptTemplate', $template->id)
        ->call('generate')
        ->assertSet('wavUrl', '/storage/audio/direct.wav')
        ->assertSet('savedGenerations.0.audio_url', '/storage/audio/direct.wav');

    $this->assertDatabaseHas('audio_generations', [
        'audio_path' => 'audio/direct.wav',
        'audio_url' => '/storage/audio/direct.wav',
    ]);
});

test('it loads a previous prompt into the generator preview state', function () {
    $generation = AudioGeneration::factory()->create([
        'master_prompt' => 'Use a calm radio host style.',
        'prompt_brief' => 'Announce that the doors open at eight.',
        'text' => 'The doors open at eight.',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);

    Livewire::test(AudioGenerator::class)
        ->call('usePrompt', $generation->id)
        ->assertSet('masterPrompt', 'Use a calm radio host style.')
        ->assertSet('text', 'The doors open at eight.')
        ->assertSet('selectedVoiceGender', 'Male')
        ->assertSet('selectedVoice', 'Puck')
        ->assertSet('selectedLanguageCode', 'en-US')
        ->assertSet('audioGenerationId', $generation->id);
});

test('it saves audio generation errors to the database', function () {
    $template = PromptTemplate::factory()->create([
        'prompt_text' => 'Direct text',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);

    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Direct text', 'Puck', 'en-US')
        ->andThrow(new AudioGenerationException('Gemini API key is not configured.'));

    Livewire::test(AudioGenerator::class)
        ->call('usePromptTemplate', $template->id)
        ->call('generate')
        ->assertSet('errorMessage', 'Gemini API key is not configured.')
        ->assertSet('audioGenerationId', 1);

    $this->assertDatabaseHas('audio_generations', [
        'text' => 'Direct text',
        'status' => AudioGeneration::STATUS_FAILED,
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
        'tts_language_code' => 'en-US',
        'error_message' => 'Gemini API key is not configured.',
    ]);
});
