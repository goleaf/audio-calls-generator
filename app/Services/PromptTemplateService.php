<?php

namespace App\Services;

use App\Models\PromptTemplate;

class PromptTemplateService
{
    private const DEFAULT_LIMIT = 50;

    /**
     * Create the prompt template service with Gemini language metadata.
     */
    public function __construct(
        private readonly GeminiLanguageService $languages,
    ) {}

    /**
     * Return prompt templates in the shape consumed by Livewire select lists.
     *
     * @return list<array{id: int, title: string, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null}>
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
                'language_code' => $template->language_code,
                'language_name' => $template->language_name,
                'language_readiness' => $template->language_readiness,
                'language_label' => $this->languageLabel($template->language_code, $template->language_name),
            ])
            ->all();
    }

    /**
     * Return lightweight template options for the audio generator selector.
     *
     * @return list<array{id: int, title: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null}>
     */
    public function options(int $limit = self::DEFAULT_LIMIT): array
    {
        return PromptTemplate::query()
            ->select([
                'id',
                'title',
                'language_code',
                'language_name',
                'language_readiness',
            ])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (PromptTemplate $template): array => [
                'id' => $template->id,
                'title' => $template->title,
                'language_code' => $template->language_code,
                'language_name' => $template->language_name,
                'language_readiness' => $template->language_readiness,
                'language_label' => $this->languageLabel($template->language_code, $template->language_name),
            ])
            ->all();
    }

    /**
     * Persist a reusable prompt template.
     */
    public function create(string $title, string $promptText, string $languageCode): PromptTemplate
    {
        $language = $this->languages->find($languageCode) ?? $this->languages->default();

        return PromptTemplate::query()->create([
            'title' => $title,
            'prompt_text' => $promptText,
            'language_code' => $language['code'],
            'language_name' => $language['name'],
            'language_readiness' => $language['readiness'],
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

    /**
     * Build a nullable language label for lists.
     */
    private function languageLabel(?string $code, ?string $name): ?string
    {
        if ($code === null || $code === '' || $name === null || $name === '') {
            return null;
        }

        return "{$name} - {$code}";
    }
}
