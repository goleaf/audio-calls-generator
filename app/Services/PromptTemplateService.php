<?php

namespace App\Services;

use App\Models\PromptTemplate;

class PromptTemplateService
{
    private const DEFAULT_LIMIT = 50;

    /**
     * Return prompt templates in the shape consumed by Livewire select lists.
     *
     * @return list<array{id: int, title: string, prompt_text: string}>
     */
    public function recent(int $limit = self::DEFAULT_LIMIT): array
    {
        return PromptTemplate::query()
            ->recentList()
            ->limit($limit)
            ->get()
            ->map(fn (PromptTemplate $template): array => [
                'id' => $template->id,
                'title' => $template->title,
                'prompt_text' => $template->prompt_text,
            ])
            ->all();
    }

    /**
     * Return lightweight template options for the audio generator selector.
     *
     * @return list<array{id: int, title: string}>
     */
    public function options(int $limit = self::DEFAULT_LIMIT): array
    {
        return PromptTemplate::query()
            ->select([
                'id',
                'title',
            ])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (PromptTemplate $template): array => [
                'id' => $template->id,
                'title' => $template->title,
            ])
            ->all();
    }

    /**
     * Persist a reusable prompt template.
     */
    public function create(string $title, string $promptText): PromptTemplate
    {
        return PromptTemplate::query()->create([
            'title' => $title,
            'prompt_text' => $promptText,
        ]);
    }

    /**
     * Find one prompt template by primary key.
     */
    public function find(int $id): ?PromptTemplate
    {
        return PromptTemplate::query()
            ->recentList()
            ->whereKey($id)
            ->first();
    }

    /**
     * Delete a prompt template if it exists.
     */
    public function delete(int $id): bool
    {
        $template = $this->find($id);

        if ($template === null) {
            return false;
        }

        return (bool) $template->delete();
    }
}
