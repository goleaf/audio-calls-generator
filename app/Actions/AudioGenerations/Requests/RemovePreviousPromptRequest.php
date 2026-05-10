<?php

namespace App\Actions\AudioGenerations\Requests;

readonly class RemovePreviousPromptRequest
{
    /**
     * Create a request for deleting a previous audio generation.
     */
    public function __construct(
        public int $generationId,
    ) {}
}
