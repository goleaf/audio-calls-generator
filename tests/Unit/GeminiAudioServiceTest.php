<?php

use App\Exceptions\AudioGenerationException;
use App\Services\GeminiAudioService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('public');

    config()->set('services.gemini.api_key', 'test-key');
    config()->set('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    config()->set('services.gemini.model', 'gemini-3.1-flash-tts-preview');
    config()->set('services.gemini.voice', 'Kore');
    config()->set('services.gemini.language', 'en-US');
    config()->set('services.gemini.timeout', 60);
    config()->set('services.gemini.connect_timeout', 10);
    config()->set('services.gemini.retries', 1);
    config()->set('services.gemini.retry_sleep_milliseconds', 10);
    config()->set('services.gemini.audio.sample_rate', 24000);
    config()->set('services.gemini.audio.channels', 1);
    config()->set('services.gemini.audio.sample_width', 2);
});

test('it stores Gemini PCM output as a public wav file', function () {
    $pcm = str_repeat("\x00\x01", 480);

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'inlineData' => [
                                    'mimeType' => 'audio/pcm',
                                    'data' => base64_encode($pcm),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $audio = app(GeminiAudioService::class)->generateWav('Read this text.');

    expect(str_starts_with($audio['path'], 'audio/'))->toBeTrue();
    expect(str_ends_with($audio['path'], '.wav'))->toBeTrue();
    expect($audio['url'])->toStartWith('/storage/audio/');
    expect($audio['voice'])->toBe('Kore');
    expect($audio['voice_gender'])->toBe('Female');
    expect($audio['voice_label'])->toBe('Female - Kore');
    expect($audio['language_code'])->toBe('en-US');
    expect($audio['language_name'])->toBe('English (United States)');
    expect($audio['language_label'])->toBe('English (United States) - en-US');

    Storage::disk('public')->assertExists($audio['path']);

    $wav = Storage::disk('public')->get($audio['path']);

    expect(substr($wav, 0, 4))->toBe('RIFF');
    expect(substr($wav, 8, 4))->toBe('WAVE');
    expect(substr($wav, 12, 4))->toBe('fmt ');
    expect(substr($wav, 36, 4))->toBe('data');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && Str::contains($request->url(), '/models/gemini-3.1-flash-tts-preview:generateContent')
            && $request->hasHeader('x-goog-api-key', 'test-key')
            && Str::contains(data_get($request->data(), 'contents.0.parts.0.text'), 'English (United States) (en-US)')
            && Str::contains(data_get($request->data(), 'contents.0.parts.0.text'), 'TRANSCRIPT:')
            && Str::contains(data_get($request->data(), 'contents.0.parts.0.text'), 'Read this text.')
            && data_get($request->data(), 'generationConfig.responseModalities.0') === 'AUDIO'
            && data_get($request->data(), 'generationConfig.speechConfig.voiceConfig.prebuiltVoiceConfig.voiceName') === 'Kore';
    });
});

test('it sends the selected voice generator to Gemini', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'inlineData' => [
                                    'mimeType' => 'audio/pcm',
                                    'data' => base64_encode(str_repeat("\x00\x01", 480)),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $audio = app(GeminiAudioService::class)->generateWav('Read this text.', 'Puck', 'lt-LT');

    expect($audio['voice'])->toBe('Puck')
        ->and($audio['voice_gender'])->toBe('Male')
        ->and($audio['voice_label'])->toBe('Male - Puck')
        ->and($audio['language_code'])->toBe('lt-LT')
        ->and($audio['language_name'])->toBe('Lithuanian (Lithuania)')
        ->and($audio['language_readiness'])->toBe('Preview');

    Http::assertSent(fn (Request $request): bool => data_get($request->data(), 'generationConfig.speechConfig.voiceConfig.prebuiltVoiceConfig.voiceName') === 'Puck'
        && Str::contains(data_get($request->data(), 'contents.0.parts.0.text'), 'Lithuanian (Lithuania) (lt-LT)'));
});

test('it stores relative audio urls when the public disk has an absolute host', function () {
    config()->set('filesystems.disks.public.url', 'http://audio-calls-generator.test/storage');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'inlineData' => [
                                    'mimeType' => 'audio/pcm',
                                    'data' => base64_encode(str_repeat("\x00\x01", 480)),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $audio = app(GeminiAudioService::class)->generateWav('Read this text.');

    expect($audio['url'])->toStartWith('/storage/audio/')
        ->and($audio['url'])->not->toStartWith('http://');
});

test('it returns saved wav files from public storage', function () {
    Storage::disk('public')->put('audio/first.wav', 'first');
    Storage::disk('public')->put('audio/ignore.txt', 'ignore');
    Storage::disk('public')->put('audio/second.wav', 'second');

    $files = app(GeminiAudioService::class)->savedWavFiles();

    expect($files)->toHaveCount(2)
        ->and($files[0])->toHaveKeys(['path', 'name', 'url', 'size', 'last_modified'])
        ->and(collect($files)->pluck('path')->all())->toContain('audio/first.wav', 'audio/second.wav')
        ->and(collect($files)->pluck('path')->all())->not->toContain('audio/ignore.txt');
});

test('it fails when the api key is missing', function () {
    config()->set('services.gemini.api_key', null);

    expect(fn () => app(GeminiAudioService::class)->generateWav('Hello'))
        ->toThrow(AudioGenerationException::class, 'Gemini API key is not configured.');
});

test('it fails when Gemini returns an api error', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'error' => [
                'message' => 'API key is invalid.',
            ],
        ], 400),
    ]);

    expect(fn () => app(GeminiAudioService::class)->generateWav('Hello'))
        ->toThrow(AudioGenerationException::class, 'API key is invalid.');
});

test('it fails when Gemini does not return audio data', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [],
                    ],
                ],
            ],
        ]),
    ]);

    expect(fn () => app(GeminiAudioService::class)->generateWav('Hello'))
        ->toThrow(AudioGenerationException::class, 'Gemini did not return audio data.');
});
