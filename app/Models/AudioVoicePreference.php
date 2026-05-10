<?php

namespace App\Models;

use Database\Factories\AudioVoicePreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'tts_voice',
    'tts_voice_gender',
    'tts_voice_label',
])]
class AudioVoicePreference extends Model
{
    public const CURRENT_KEY = 'current';

    /** @use HasFactory<AudioVoicePreferenceFactory> */
    use HasFactory;
}
