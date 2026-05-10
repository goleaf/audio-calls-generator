<?php

namespace App\Actions\AudioGenerations\Requests;

readonly class LoadAudioGeneratorDataRequest
{
    /**
     * Create a request for loading generator page data.
     */
    public function __construct(
        public int $promptTemplateLimit = 50,
        public int $historyLimit = 10,
    ) {}
}
