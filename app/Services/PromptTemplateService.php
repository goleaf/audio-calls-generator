<?php

namespace App\Services;

use App\Models\PromptTemplate;

class PromptTemplateService
{
    private const DEFAULT_LIMIT = 50;

    /**
     * Create the prompt template service with Gemini language and voice metadata.
     */
    public function __construct(
        private readonly GeminiLanguageService $languages,
        private readonly GeminiVoiceService $voices,
    ) {}

    /**
     * Return prompt templates in the shape consumed by Livewire select lists.
     *
     * @return list<array{id: int, title: string, master_prompt: string|null, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}>
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
                'master_prompt' => $template->master_prompt,
                'prompt_text' => $template->prompt_text,
                'language_code' => $template->language_code,
                'language_name' => $template->language_name,
                'language_readiness' => $template->language_readiness,
                'language_label' => $this->languageLabel($template->language_code, $template->language_name),
                'tts_voice' => $template->tts_voice,
                'tts_voice_gender' => $template->tts_voice_gender,
                'tts_voice_label' => $template->tts_voice_label,
            ])
            ->all();
    }

    /**
     * Return lightweight template options for the audio generator selector.
     *
     * @return list<array{id: int, title: string, master_prompt: string|null, prompt_text: string, language_code: string|null, language_name: string|null, language_readiness: string|null, language_label: string|null, tts_voice: string|null, tts_voice_gender: string|null, tts_voice_label: string|null}>
     */
    public function options(int $limit = self::DEFAULT_LIMIT): array
    {
        return PromptTemplate::query()
            ->select([
                'id',
                'title',
                'master_prompt',
                'prompt_text',
                'language_code',
                'language_name',
                'language_readiness',
                'tts_voice',
                'tts_voice_gender',
                'tts_voice_label',
            ])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (PromptTemplate $template): array => [
                'id' => $template->id,
                'title' => $template->title,
                'master_prompt' => $template->master_prompt,
                'prompt_text' => $template->prompt_text,
                'language_code' => $template->language_code,
                'language_name' => $template->language_name,
                'language_readiness' => $template->language_readiness,
                'language_label' => $this->languageLabel($template->language_code, $template->language_name),
                'tts_voice' => $template->tts_voice,
                'tts_voice_gender' => $template->tts_voice_gender,
                'tts_voice_label' => $template->tts_voice_label,
            ])
            ->all();
    }

    /**
     * Persist a reusable prompt template.
     */
    public function create(string $title, string $masterPrompt, string $promptText, string $languageCode, string $voiceName): PromptTemplate
    {
        return PromptTemplate::query()->create($this->attributes($title, $masterPrompt, $promptText, $languageCode, $voiceName));
    }

    /**
     * Update a reusable prompt template if it exists.
     */
    public function update(int $id, string $title, string $masterPrompt, string $promptText, string $languageCode, string $voiceName): ?PromptTemplate
    {
        $template = $this->find($id);

        if ($template === null) {
            return null;
        }

        $template->fill($this->attributes($title, $masterPrompt, $promptText, $languageCode, $voiceName));
        $template->save();

        return $template->refresh();
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

    /**
     * Build normalized prompt template attributes for create and update.
     *
     * @return array{title: string, master_prompt: string, prompt_text: string, language_code: string, language_name: string, language_readiness: string, tts_voice: string, tts_voice_gender: string, tts_voice_label: string}
     */
    private function attributes(string $title, string $masterPrompt, string $promptText, string $languageCode, string $voiceName): array
    {
        $language = $this->languages->find($languageCode) ?? $this->languages->default();
        $voice = $this->voices->find($voiceName) ?? $this->voices->default();

        return [
            'title' => $title,
            'master_prompt' => $masterPrompt,
            'prompt_text' => $promptText,
            'language_code' => $language['code'],
            'language_name' => $language['name'],
            'language_readiness' => $language['readiness'],
            'tts_voice' => $voice['name'],
            'tts_voice_gender' => $voice['gender'],
            'tts_voice_label' => $voice['label'],
        ];
    }
}
