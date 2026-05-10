<?php

namespace App\Actions\AudioGenerations\Requests;

readonly class GenerateAudioRequest
{
    /**
     * Create a request for generating WAV audio from a prompt template.
     */
    public function __construct(
        public int $promptTemplateId,
        public ?int $audioGenerationId,
    ) {}
}
