<?php

namespace App\Services;

use App\Models\MasterPrompt;

class MasterPromptService
{
    public const DEFAULT_CONTENT = 'Write a short, ready-to-speak audio script. Return only the final script text.';

    /**
     * Return the saved master prompt or the default instruction text.
     */
    public function current(): string
    {
        return (string) (MasterPrompt::query()
            ->select(['id', 'key', 'content'])
            ->where('key', MasterPrompt::CURRENT_KEY)
            ->first()
            ?->content ?? self::DEFAULT_CONTENT);
    }

    /**
     * Store the single reusable master prompt for future generations.
     */
    public function save(string $content): MasterPrompt
    {
        return MasterPrompt::query()->updateOrCreate(
            ['key' => MasterPrompt::CURRENT_KEY],
            ['content' => trim($content)],
        );
    }
}
