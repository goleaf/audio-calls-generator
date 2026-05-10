<?php

namespace App\Models;

use Database\Factories\PromptTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'prompt_text',
])]
class PromptTemplate extends Model
{
    /** @use HasFactory<PromptTemplateFactory> */
    use HasFactory;

    /**
     * Select the columns required by template lists and order newest first.
     *
     * @param  Builder<PromptTemplate>  $query
     */
    public function scopeRecentList(Builder $query): Builder
    {
        return $query
            ->select([
                'id',
                'title',
                'prompt_text',
                'created_at',
                'updated_at',
            ])
            ->latest('id');
    }
}
