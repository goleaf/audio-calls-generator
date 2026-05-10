<?php

namespace App\Models;

use Database\Factories\AudioGenerationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'prompt_brief',
    'master_prompt',
    'text',
    'status',
    'tts_model',
    'tts_voice',
    'tts_voice_gender',
    'tts_voice_label',
    'tts_language_code',
    'tts_language_name',
    'tts_language_readiness',
    'audio_disk',
    'audio_path',
    'audio_url',
    'audio_file_name',
    'audio_mime_type',
    'audio_size_bytes',
    'audio_sample_rate',
    'audio_channels',
    'audio_sample_width',
    'error_message',
])]
class AudioGeneration extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_WAV_GENERATED = 'wav_generated';

    public const STATUS_FAILED = 'failed';

    /** @use HasFactory<AudioGenerationFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    /**
     * Cast numeric audio metadata and timestamps to native values.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audio_size_bytes' => 'integer',
            'audio_sample_rate' => 'integer',
            'audio_channels' => 'integer',
            'audio_sample_width' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Select the columns required by the recent history UI and order newest first.
     *
     * @param  Builder<AudioGeneration>  $query
     */
    public function scopeRecentHistory(Builder $query): Builder
    {
        return $query
            ->select([
                'id',
                'prompt_brief',
                'master_prompt',
                'text',
                'status',
                'tts_model',
                'tts_voice',
                'tts_voice_gender',
                'tts_voice_label',
                'tts_language_code',
                'tts_language_name',
                'tts_language_readiness',
                'audio_disk',
                'audio_path',
                'audio_url',
                'audio_file_name',
                'audio_mime_type',
                'audio_size_bytes',
                'audio_sample_rate',
                'audio_channels',
                'audio_sample_width',
                'error_message',
                'created_at',
                'updated_at',
            ])
            ->latest('id');
    }
}
