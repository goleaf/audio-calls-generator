<?php

use App\Exceptions\AudioGenerationException;
use App\Livewire\AudioGenerator;
use App\Models\AudioGeneration;
use App\Services\GeminiAudioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.gemini.model', 'gemini-3.1-flash-tts-preview');
    config()->set('services.gemini.voice', 'Kore');
    config()->set('services.gemini.audio.sample_rate', 24000);
    config()->set('services.gemini.audio.channels', 1);
    config()->set('services.gemini.audio.sample_width', 2);
});

test('it saves direct audio generation information with a selected male voice', function () {
    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Welcome to the demo.', 'Puck')
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
        ]);

    Livewire::test(AudioGenerator::class)
        ->set('masterPrompt', 'Write a concise announcement.')
        ->set('text', 'Welcome to the demo.')
        ->call('selectVoiceGender', 'Male')
        ->set('selectedVoice', 'Puck')
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
        'audio_sample_rate' => 24000,
        'audio_channels' => 1,
        'audio_sample_width' => 2,
    ]);
});

test('it saves the prompt before calling Gemini to generate audio', function () {
    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Save this text before audio exists.', 'Kore')
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
            ];
        });

    Livewire::test(AudioGenerator::class)
        ->set('masterPrompt', 'Persist this master prompt immediately.')
        ->set('text', 'Save this text before audio exists.')
        ->call('generate')
        ->assertSet('audioGenerationId', 1)
        ->assertSet('savedGenerations.0.audio_path', 'audio/saved-before-call.wav');

    $this->assertDatabaseHas('audio_generations', [
        'id' => 1,
        'master_prompt' => 'Persist this master prompt immediately.',
        'text' => 'Save this text before audio exists.',
        'status' => AudioGeneration::STATUS_WAV_GENERATED,
        'audio_path' => 'audio/saved-before-call.wav',
    ]);
});

test('it saves direct audio generation information to the database', function () {
    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Direct text', 'Kore')
        ->andReturn([
            'path' => 'audio/direct.wav',
            'url' => '/storage/audio/direct.wav',
            'name' => 'direct.wav',
            'disk' => 'public',
            'mime_type' => 'audio/wav',
            'size' => 256,
            'voice' => 'Kore',
            'voice_gender' => 'Female',
            'voice_label' => 'Female - Kore',
        ]);

    Livewire::test(AudioGenerator::class)
        ->set('text', 'Direct text')
        ->call('generate')
        ->assertSet('audioGenerationId', 1)
        ->assertSet('savedGenerations.0.audio_path', 'audio/direct.wav')
        ->assertSee('direct.wav');

    $this->assertDatabaseHas('audio_generations', [
        'prompt_brief' => 'Write a short, ready-to-speak audio script. Return only the final script text.',
        'master_prompt' => 'Write a short, ready-to-speak audio script. Return only the final script text.',
        'text' => 'Direct text',
        'status' => AudioGeneration::STATUS_WAV_GENERATED,
        'audio_path' => 'audio/direct.wav',
        'tts_voice' => 'Kore',
        'tts_voice_gender' => 'Female',
        'tts_voice_label' => 'Female - Kore',
    ]);
});

test('it loads a previous prompt into the form', function () {
    $generation = AudioGeneration::factory()->create([
        'master_prompt' => 'Use a calm radio host style.',
        'prompt_brief' => 'Announce that the doors open at eight.',
        'text' => 'The doors open at eight.',
        'tts_voice' => 'Puck',
    ]);

    Livewire::test(AudioGenerator::class)
        ->call('usePrompt', $generation->id)
        ->assertSet('masterPrompt', 'Use a calm radio host style.')
        ->assertSet('text', 'The doors open at eight.')
        ->assertSet('selectedVoiceGender', 'Male')
        ->assertSet('selectedVoice', 'Puck')
        ->assertSet('voiceGenerators.0.name', 'Puck')
        ->assertSet('audioGenerationId', $generation->id);
});

test('it saves audio generation errors to the database', function () {
    $this->mock(GeminiAudioService::class)
        ->shouldReceive('generateWav')
        ->once()
        ->with('Direct text', 'Puck')
        ->andThrow(new AudioGenerationException('Gemini API key is not configured.'));

    Livewire::test(AudioGenerator::class)
        ->set('text', 'Direct text')
        ->call('selectVoiceGender', 'Male')
        ->set('selectedVoice', 'Puck')
        ->call('generate')
        ->assertSet('errorMessage', 'Gemini API key is not configured.')
        ->assertSet('audioGenerationId', 1);

    $this->assertDatabaseHas('audio_generations', [
        'text' => 'Direct text',
        'status' => AudioGeneration::STATUS_FAILED,
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
        'error_message' => 'Gemini API key is not configured.',
    ]);
});
