<?php

namespace Database\Seeders;

use App\Models\PromptTemplate;
use App\Services\GeminiLanguageService;
use App\Services\GeminiVoiceService;
use Illuminate\Database\Seeder;

class PromptTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(GeminiLanguageService $languages, GeminiVoiceService $voices): void
    {
        foreach ($this->templates() as $template) {
            $language = $languages->find($template['language_code']) ?? $languages->default();
            $voice = $voices->find($template['voice']) ?? $voices->default();

            PromptTemplate::query()->updateOrCreate(
                ['title' => $template['title']],
                [
                    'master_prompt' => $template['master_prompt'],
                    'prompt_text' => $template['prompt_text'],
                    'language_code' => $language['code'],
                    'language_name' => $language['name'],
                    'language_readiness' => $language['readiness'],
                    'tts_voice' => $voice['name'],
                    'tts_voice_gender' => $voice['gender'],
                    'tts_voice_label' => $voice['label'],
                ],
            );
        }
    }

    /**
     * Return the default prompt templates shipped with the app.
     *
     * @return list<array{title: string, master_prompt: string, prompt_text: string, language_code: string, voice: string}>
     */
    private function templates(): array
    {
        return [
            [
                'title' => 'Warm support greeting',
                'master_prompt' => 'Speak as a friendly support representative. Keep the pacing clear and natural.',
                'prompt_text' => 'Hello, thank you for calling. I am checking your request now and will guide you through the next step.',
                'language_code' => 'en-US',
                'voice' => 'Kore',
            ],
            [
                'title' => 'Lithuanian billing reminder',
                'master_prompt' => 'Speak in Lithuanian with a calm billing support tone. Keep the message concise.',
                'prompt_text' => 'Sveiki, primename, kad jūsų sąskaita jau paruošta peržiūrai. Jei turite klausimų, susisiekite su mūsų komanda.',
                'language_code' => 'lt-LT',
                'voice' => 'Puck',
            ],
            [
                'title' => 'Delivery update',
                'master_prompt' => 'Speak with a direct operations tone. The message should sound helpful and precise.',
                'prompt_text' => 'Your delivery status has been updated. Please check the latest arrival time before planning pickup.',
                'language_code' => 'en-GB',
                'voice' => 'Aoede',
            ],
        ];
    }
}
