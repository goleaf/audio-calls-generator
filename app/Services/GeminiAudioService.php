<?php

namespace App\Services;

use App\Exceptions\AudioGenerationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GeminiAudioService
{
    private const DEFAULT_MODEL = 'gemini-3.1-flash-tts-preview';

    private const AUDIO_DIRECTORY = 'audio';

    private const PUBLIC_DISK = 'public';

    private const WAV_MIME_TYPE = 'audio/wav';

    private const DEFAULT_TIMEOUT = 60;

    private const DEFAULT_CONNECT_TIMEOUT = 10;

    private const DEFAULT_RETRIES = 2;

    private const DEFAULT_RETRY_SLEEP_MILLISECONDS = 300;

    private const DEFAULT_SAMPLE_RATE = 24000;

    private const DEFAULT_CHANNELS = 1;

    private const DEFAULT_SAMPLE_WIDTH = 2;

    /**
     * Create the Gemini audio service with HTTP and voice dependencies.
     */
    public function __construct(
        private readonly HttpFactory $http,
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Generate a WAV file from text and return its storage metadata.
     *
     * @return array{path: string, url: string, name: string, disk: string, mime_type: string, size: int, voice: string, voice_gender: string, voice_label: string}
     */
    public function generateWav(string $text, ?string $voiceName = null): array
    {
        $apiKey = trim((string) config('services.gemini.api_key'));

        if ($apiKey === '') {
            throw new AudioGenerationException('Gemini API key is not configured.');
        }

        $model = (string) config('services.gemini.model', self::DEFAULT_MODEL);
        $voice = $this->voices->find($voiceName ?? '') ?? $this->voices->default();
        $response = $this->requestAudio($apiKey, $model, $text, $voice['name']);

        if ($response->failed()) {
            $this->logApiFailure($response);

            throw new AudioGenerationException($this->apiErrorMessage($response));
        }

        $pcm = $this->extractPcmAudio($response);

        return [
            ...$this->storeWav($pcm),
            'voice' => $voice['name'],
            'voice_gender' => $voice['gender'],
            'voice_label' => $voice['label'],
        ];
    }

    /**
     * Return recently saved WAV files from public storage.
     *
     * @return list<array{path: string, name: string, url: string, size: int, last_modified: int}>
     */
    public function savedWavFiles(): array
    {
        $disk = Storage::disk(self::PUBLIC_DISK);

        return collect($disk->files(self::AUDIO_DIRECTORY))
            ->filter(fn (string $path): bool => Str::endsWith(Str::lower($path), '.wav'))
            ->map(fn (string $path): array => [
                'path' => $path,
                'name' => basename($path),
                'url' => $this->publicAudioUrl($path),
                'size' => $disk->size($path),
                'last_modified' => $disk->lastModified($path),
            ])
            ->sort(fn (array $first, array $second): int => [$second['last_modified'], $second['path']] <=> [$first['last_modified'], $first['path']])
            ->take(10)
            ->values()
            ->all();
    }

    /**
     * Send the TTS generation request to Gemini and wrap transport failures.
     */
    private function requestAudio(string $apiKey, string $model, string $text, string $voiceName): Response
    {
        $baseUrl = rtrim((string) config('services.gemini.base_url'), '/');

        try {
            return $this->http
                ->baseUrl($baseUrl)
                ->acceptJson()
                ->asJson()
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout((int) config('services.gemini.timeout', self::DEFAULT_TIMEOUT))
                ->connectTimeout((int) config('services.gemini.connect_timeout', self::DEFAULT_CONNECT_TIMEOUT))
                ->retry(
                    (int) config('services.gemini.retries', self::DEFAULT_RETRIES),
                    (int) config('services.gemini.retry_sleep_milliseconds', self::DEFAULT_RETRY_SLEEP_MILLISECONDS),
                    throw: false,
                )
                ->post("/models/{$model}:generateContent", $this->payload($model, $text, $voiceName));
        } catch (ConnectionException $exception) {
            throw new AudioGenerationException('Could not connect to the Gemini API.', previous: $exception);
        } catch (Throwable $exception) {
            throw new AudioGenerationException('Gemini audio generation failed before a response was received.', previous: $exception);
        }
    }

    /**
     * Build the Gemini request payload for a single-speaker prebuilt voice.
     *
     * @return array<string, mixed>
     */
    private function payload(string $model, string $text, string $voiceName): array
    {
        return [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $this->speechPrompt($text),
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['AUDIO'],
                'speechConfig' => [
                    'voiceConfig' => [
                        'prebuiltVoiceConfig' => [
                            'voiceName' => $voiceName,
                        ],
                    ],
                ],
            ],
            'model' => $model,
        ];
    }

    /**
     * Wrap user text in a minimal instruction that prevents Gemini from speaking metadata.
     */
    private function speechPrompt(string $text): string
    {
        return "Synthesize speech from the transcript below. Speak only the transcript text, not these instructions.\n\nTRANSCRIPT:\n{$text}";
    }

    /**
     * Extract and decode the PCM audio payload from a Gemini response.
     */
    private function extractPcmAudio(Response $response): string
    {
        $payload = $response->json();
        $base64Audio = data_get($payload, 'candidates.0.content.parts.0.inlineData.data')
            ?? data_get($payload, 'candidates.0.content.parts.0.inline_data.data');

        if (! is_string($base64Audio) || trim($base64Audio) === '') {
            throw new AudioGenerationException('Gemini did not return audio data.');
        }

        $pcm = base64_decode($base64Audio, true);

        if ($pcm === false || $pcm === '') {
            throw new AudioGenerationException('Gemini returned invalid audio data.');
        }

        return $pcm;
    }

    /**
     * Store PCM audio as a public WAV file.
     *
     * @return array{path: string, url: string, name: string, disk: string, mime_type: string, size: int}
     */
    private function storeWav(string $pcm): array
    {
        $path = self::AUDIO_DIRECTORY.'/'.now()->format('YmdHis').'-'.Str::uuid().'.wav';
        $disk = Storage::disk(self::PUBLIC_DISK);
        $wav = $this->pcmToWav($pcm);

        $disk->makeDirectory(self::AUDIO_DIRECTORY);
        $disk->put($path, $wav, 'public');

        return [
            'path' => $path,
            'url' => $this->publicAudioUrl($path),
            'name' => basename($path),
            'disk' => self::PUBLIC_DISK,
            'mime_type' => self::WAV_MIME_TYPE,
            'size' => strlen($wav),
        ];
    }

    /**
     * Build a same-origin playback URL for the generated WAV route.
     */
    private function publicAudioUrl(string $path): string
    {
        return route('audio.files.show', ['fileName' => basename($path)], false);
    }

    /**
     * Wrap raw little-endian PCM bytes in a WAV container header.
     */
    private function pcmToWav(string $pcm): string
    {
        $sampleRate = (int) config('services.gemini.audio.sample_rate', self::DEFAULT_SAMPLE_RATE);
        $channels = (int) config('services.gemini.audio.channels', self::DEFAULT_CHANNELS);
        $sampleWidth = (int) config('services.gemini.audio.sample_width', self::DEFAULT_SAMPLE_WIDTH);
        $dataSize = strlen($pcm);
        $byteRate = $sampleRate * $channels * $sampleWidth;
        $blockAlign = $channels * $sampleWidth;

        return 'RIFF'
            .pack('V', 36 + $dataSize)
            .'WAVE'
            .'fmt '
            .pack('VvvVVvv', 16, 1, $channels, $sampleRate, $byteRate, $blockAlign, $sampleWidth * 8)
            .'data'
            .pack('V', $dataSize)
            .$pcm;
    }

    /**
     * Resolve the best user-facing message from a failed Gemini response.
     */
    private function apiErrorMessage(Response $response): string
    {
        $message = data_get($response->json(), 'error.message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        return "Gemini API request failed with status {$response->status()}.";
    }

    /**
     * Record safe Gemini failure details without exposing the API key.
     */
    private function logApiFailure(Response $response): void
    {
        Log::warning('Gemini audio generation failed.', [
            'status' => $response->status(),
            'body' => Str::limit($response->body(), 1000),
        ]);
    }
}
