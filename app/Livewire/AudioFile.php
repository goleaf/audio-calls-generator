<?php

namespace App\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AudioFile
{
    /**
     * Serve generated WAV files from the public storage disk for browser playback.
     */
    public function __invoke(Request $request, string $fileName): BinaryFileResponse
    {
        if ($fileName !== basename($fileName) || ! Str::endsWith(Str::lower($fileName), '.wav')) {
            abort(404);
        }

        $path = "audio/{$fileName}";
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response()->file($disk->path($path), [
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => "{$disposition}; filename=\"{$fileName}\"",
            'Content-Type' => 'audio/wav',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
