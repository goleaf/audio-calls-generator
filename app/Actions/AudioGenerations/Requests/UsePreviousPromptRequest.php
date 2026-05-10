<?php

namespace App\Actions\AudioGenerations\Requests;

readonly class UsePreviousPromptRequest
{
    /**
     * Create a request for loading a previous audio generation.
     */
    public function __construct(
        public int $generationId,
    ) {}
}
