<?php

namespace App\Models;

use Database\Factories\MasterPromptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'content',
])]
class MasterPrompt extends Model
{
    public const CURRENT_KEY = 'current';

    /** @use HasFactory<MasterPromptFactory> */
    use HasFactory;
}
