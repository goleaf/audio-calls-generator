<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AudioFileController extends Controller
{
    /**
     * Serve generated WAV files from the public storage disk for browser playback.
     */
    public function __invoke(string $fileName): BinaryFileResponse
    {
        if ($fileName !== basename($fileName) || ! Str::endsWith(Str::lower($fileName), '.wav')) {
            abort(404);
        }

        $path = "audio/{$fileName}";
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        return response()->file($disk->path($path), [
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
            'Content-Type' => 'audio/wav',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
