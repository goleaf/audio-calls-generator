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
        PromptTemplate::query()
            ->whereIn('title', $this->legacyEnglishTemplateTitles())
            ->delete();

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
                'title' => 'Šiltas pasisveikinimas',
                'master_prompt' => 'Kalbėk lietuviškai kaip draugiškas klientų aptarnavimo specialistas. Išlaikyk aiškų, natūralų tempą.',
                'prompt_text' => 'Sveiki, ačiū, kad paskambinote. Peržiūriu jūsų užklausą ir netrukus padėsiu atlikti kitą žingsnį.',
                'language_code' => 'lt-LT',
                'voice' => 'Kore',
            ],
            [
                'title' => 'Sąskaitos priminimas',
                'master_prompt' => 'Kalbėk lietuviškai ramiu sąskaitų aptarnavimo tonu. Žinutė turi būti trumpa ir aiški.',
                'prompt_text' => 'Sveiki, primename, kad jūsų sąskaita jau paruošta peržiūrai. Jei turite klausimų, susisiekite su mūsų komanda.',
                'language_code' => 'lt-LT',
                'voice' => 'Puck',
            ],
            [
                'title' => 'Pristatymo atnaujinimas',
                'master_prompt' => 'Kalbėk lietuviškai tiesiu ir tiksliu operacijų komandos tonu. Žinutė turi skambėti naudingai.',
                'prompt_text' => 'Jūsų pristatymo būsena atnaujinta. Prieš planuodami atsiėmimą, patikrinkite naujausią atvykimo laiką.',
                'language_code' => 'lt-LT',
                'voice' => 'Aoede',
            ],
        ];
    }

    /**
     * Return old shipped template titles that should not remain after reseeding.
     *
     * @return list<string>
     */
    private function legacyEnglishTemplateTitles(): array
    {
        return [
            'Warm support greeting',
            'Lithuanian billing reminder',
            'Delivery update',
        ];
    }
}
