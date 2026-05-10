<?php

namespace App\Services;

class GeminiLanguageService
{
    private const DEFAULT_LANGUAGE_CODE = 'en-US';

    private const LANGUAGES = [
        ['name' => 'Arabic (Egypt)', 'code' => 'ar-EG', 'readiness' => 'GA'],
        ['name' => 'Bangla (Bangladesh)', 'code' => 'bn-BD', 'readiness' => 'GA'],
        ['name' => 'Dutch (Netherlands)', 'code' => 'nl-NL', 'readiness' => 'GA'],
        ['name' => 'English (India)', 'code' => 'en-IN', 'readiness' => 'GA'],
        ['name' => 'English (United States)', 'code' => 'en-US', 'readiness' => 'GA'],
        ['name' => 'French (France)', 'code' => 'fr-FR', 'readiness' => 'GA'],
        ['name' => 'German (Germany)', 'code' => 'de-DE', 'readiness' => 'GA'],
        ['name' => 'Hindi (India)', 'code' => 'hi-IN', 'readiness' => 'GA'],
        ['name' => 'Indonesian (Indonesia)', 'code' => 'id-ID', 'readiness' => 'GA'],
        ['name' => 'Italian (Italy)', 'code' => 'it-IT', 'readiness' => 'GA'],
        ['name' => 'Japanese (Japan)', 'code' => 'ja-JP', 'readiness' => 'GA'],
        ['name' => 'Korean (South Korea)', 'code' => 'ko-KR', 'readiness' => 'GA'],
        ['name' => 'Marathi (India)', 'code' => 'mr-IN', 'readiness' => 'GA'],
        ['name' => 'Polish (Poland)', 'code' => 'pl-PL', 'readiness' => 'GA'],
        ['name' => 'Portuguese (Brazil)', 'code' => 'pt-BR', 'readiness' => 'GA'],
        ['name' => 'Romanian (Romania)', 'code' => 'ro-RO', 'readiness' => 'GA'],
        ['name' => 'Russian (Russia)', 'code' => 'ru-RU', 'readiness' => 'GA'],
        ['name' => 'Spanish (Spain)', 'code' => 'es-ES', 'readiness' => 'GA'],
        ['name' => 'Tamil (India)', 'code' => 'ta-IN', 'readiness' => 'GA'],
        ['name' => 'Telugu (India)', 'code' => 'te-IN', 'readiness' => 'GA'],
        ['name' => 'Thai (Thailand)', 'code' => 'th-TH', 'readiness' => 'GA'],
        ['name' => 'Turkish (Turkey)', 'code' => 'tr-TR', 'readiness' => 'GA'],
        ['name' => 'Ukrainian (Ukraine)', 'code' => 'uk-UA', 'readiness' => 'GA'],
        ['name' => 'Vietnamese (Vietnam)', 'code' => 'vi-VN', 'readiness' => 'GA'],
        ['name' => 'Afrikaans (South Africa)', 'code' => 'af-ZA', 'readiness' => 'Preview'],
        ['name' => 'Albanian (Albania)', 'code' => 'sq-AL', 'readiness' => 'Preview'],
        ['name' => 'Amharic (Ethiopia)', 'code' => 'am-ET', 'readiness' => 'Preview'],
        ['name' => 'Arabic (World)', 'code' => 'ar-001', 'readiness' => 'Preview'],
        ['name' => 'Armenian (Armenia)', 'code' => 'hy-AM', 'readiness' => 'Preview'],
        ['name' => 'Azerbaijani (Azerbaijan)', 'code' => 'az-AZ', 'readiness' => 'Preview'],
        ['name' => 'Basque (Spain)', 'code' => 'eu-ES', 'readiness' => 'Preview'],
        ['name' => 'Belarusian (Belarus)', 'code' => 'be-BY', 'readiness' => 'Preview'],
        ['name' => 'Bulgarian (Bulgaria)', 'code' => 'bg-BG', 'readiness' => 'Preview'],
        ['name' => 'Burmese (Myanmar)', 'code' => 'my-MM', 'readiness' => 'Preview'],
        ['name' => 'Catalan (Spain)', 'code' => 'ca-ES', 'readiness' => 'Preview'],
        ['name' => 'Cebuano (Philippines)', 'code' => 'ceb-PH', 'readiness' => 'Preview'],
        ['name' => 'Chinese, Mandarin (China)', 'code' => 'cmn-CN', 'readiness' => 'Preview'],
        ['name' => 'Chinese, Mandarin (Taiwan)', 'code' => 'cmn-tw', 'readiness' => 'Preview'],
        ['name' => 'Croatian (Croatia)', 'code' => 'hr-HR', 'readiness' => 'Preview'],
        ['name' => 'Czech (Czech Republic)', 'code' => 'cs-CZ', 'readiness' => 'Preview'],
        ['name' => 'Danish (Denmark)', 'code' => 'da-DK', 'readiness' => 'Preview'],
        ['name' => 'English (Australia)', 'code' => 'en-AU', 'readiness' => 'Preview'],
        ['name' => 'English (United Kingdom)', 'code' => 'en-GB', 'readiness' => 'Preview'],
        ['name' => 'Estonian (Estonia)', 'code' => 'et-EE', 'readiness' => 'Preview'],
        ['name' => 'Filipino (Philippines)', 'code' => 'fil-PH', 'readiness' => 'Preview'],
        ['name' => 'Finnish (Finland)', 'code' => 'fi-FI', 'readiness' => 'Preview'],
        ['name' => 'French (Canada)', 'code' => 'fr-CA', 'readiness' => 'Preview'],
        ['name' => 'Galician (Spain)', 'code' => 'gl-ES', 'readiness' => 'Preview'],
        ['name' => 'Georgian (Georgia)', 'code' => 'ka-GE', 'readiness' => 'Preview'],
        ['name' => 'Greek (Greece)', 'code' => 'el-GR', 'readiness' => 'Preview'],
        ['name' => 'Gujarati (India)', 'code' => 'gu-IN', 'readiness' => 'Preview'],
        ['name' => 'Haitian Creole (Haiti)', 'code' => 'ht-HT', 'readiness' => 'Preview'],
        ['name' => 'Hebrew (Israel)', 'code' => 'he-IL', 'readiness' => 'Preview'],
        ['name' => 'Hungarian (Hungary)', 'code' => 'hu-HU', 'readiness' => 'Preview'],
        ['name' => 'Icelandic (Iceland)', 'code' => 'is-IS', 'readiness' => 'Preview'],
        ['name' => 'Javanese (Java)', 'code' => 'jv-JV', 'readiness' => 'Preview'],
        ['name' => 'Kannada (India)', 'code' => 'kn-IN', 'readiness' => 'Preview'],
        ['name' => 'Konkani (India)', 'code' => 'kok-IN', 'readiness' => 'Preview'],
        ['name' => 'Lao (Laos)', 'code' => 'lo-LA', 'readiness' => 'Preview'],
        ['name' => 'Latin (Vatican City)', 'code' => 'la-VA', 'readiness' => 'Preview'],
        ['name' => 'Latvian (Latvia)', 'code' => 'lv-LV', 'readiness' => 'Preview'],
        ['name' => 'Lithuanian (Lithuania)', 'code' => 'lt-LT', 'readiness' => 'Preview'],
        ['name' => 'Luxembourgish (Luxembourg)', 'code' => 'lb-LU', 'readiness' => 'Preview'],
        ['name' => 'Macedonian (North Macedonia)', 'code' => 'mk-MK', 'readiness' => 'Preview'],
        ['name' => 'Maithili (India)', 'code' => 'mai-IN', 'readiness' => 'Preview'],
        ['name' => 'Malagasy (Madagascar)', 'code' => 'mg-MG', 'readiness' => 'Preview'],
        ['name' => 'Malay (Malaysia)', 'code' => 'ms-MY', 'readiness' => 'Preview'],
        ['name' => 'Malayalam (India)', 'code' => 'ml-IN', 'readiness' => 'Preview'],
        ['name' => 'Mongolian (Mongolia)', 'code' => 'mn-MN', 'readiness' => 'Preview'],
        ['name' => 'Nepali (Nepal)', 'code' => 'ne-NP', 'readiness' => 'Preview'],
        ['name' => 'Norwegian, Bokmål (Norway)', 'code' => 'nb-NO', 'readiness' => 'Preview'],
        ['name' => 'Norwegian, Nynorsk (Norway)', 'code' => 'nn-NO', 'readiness' => 'Preview'],
        ['name' => 'Odia (India)', 'code' => 'or-IN', 'readiness' => 'Preview'],
        ['name' => 'Pashto (Afghanistan)', 'code' => 'ps-AF', 'readiness' => 'Preview'],
        ['name' => 'Persian (Iran)', 'code' => 'fa-IR', 'readiness' => 'Preview'],
        ['name' => 'Portuguese (Portugal)', 'code' => 'pt-PT', 'readiness' => 'Preview'],
        ['name' => 'Punjabi (India)', 'code' => 'pa-IN', 'readiness' => 'Preview'],
        ['name' => 'Serbian (Serbia)', 'code' => 'sr-RS', 'readiness' => 'Preview'],
        ['name' => 'Sindhi (India)', 'code' => 'sd-IN', 'readiness' => 'Preview'],
        ['name' => 'Sinhala (Sri Lanka)', 'code' => 'si-LK', 'readiness' => 'Preview'],
        ['name' => 'Slovak (Slovakia)', 'code' => 'sk-SK', 'readiness' => 'Preview'],
        ['name' => 'Slovenian (Slovenia)', 'code' => 'sl-SI', 'readiness' => 'Preview'],
        ['name' => 'Spanish (Latin America)', 'code' => 'es-419', 'readiness' => 'Preview'],
        ['name' => 'Spanish (Mexico)', 'code' => 'es-MX', 'readiness' => 'Preview'],
        ['name' => 'Swahili (Kenya)', 'code' => 'sw-KE', 'readiness' => 'Preview'],
        ['name' => 'Swedish (Sweden)', 'code' => 'sv-SE', 'readiness' => 'Preview'],
        ['name' => 'Urdu (Pakistan)', 'code' => 'ur-PK', 'readiness' => 'Preview'],
    ];

    /** @var list<array{name: string, code: string, readiness: string, label: string}>|null */
    private ?array $languages = null;

    /** @var array<string, array{name: string, code: string, readiness: string, label: string}>|null */
    private ?array $languagesByCode = null;

    /**
     * Return every supported Gemini-TTS language with a display label.
     *
     * @return list<array{name: string, code: string, readiness: string, label: string}>
     */
    public function languages(): array
    {
        return $this->languages ??= collect(self::LANGUAGES)
            ->map(fn (array $language): array => [
                'name' => $language['name'],
                'code' => $language['code'],
                'readiness' => $language['readiness'],
                'label' => $this->label($language['name'], $language['code']),
            ])
            ->all();
    }

    /**
     * Return languages grouped for Blade optgroups.
     *
     * @return array<string, list<array{name: string, code: string, readiness: string, label: string}>>
     */
    public function groups(): array
    {
        return collect($this->languages())
            ->groupBy('readiness')
            ->map(fn ($languages): array => $languages->values()->all())
            ->all();
    }

    /**
     * Return all supported BCP-47 language codes.
     *
     * @return list<string>
     */
    public function codes(): array
    {
        return collect(self::LANGUAGES)
            ->pluck('code')
            ->all();
    }

    /**
     * Return the configured default language, falling back to English (United States).
     *
     * @return array{name: string, code: string, readiness: string, label: string}
     */
    public function default(): array
    {
        return $this->find((string) config('services.gemini.language', self::DEFAULT_LANGUAGE_CODE))
            ?? $this->find(self::DEFAULT_LANGUAGE_CODE)
            ?? [
                'name' => 'English (United States)',
                'code' => self::DEFAULT_LANGUAGE_CODE,
                'readiness' => 'GA',
                'label' => $this->label('English (United States)', self::DEFAULT_LANGUAGE_CODE),
            ];
    }

    /**
     * Find one supported language by its BCP-47 code.
     *
     * @return array{name: string, code: string, readiness: string, label: string}|null
     */
    public function find(string $code): ?array
    {
        $this->languagesByCode ??= collect($this->languages())
            ->keyBy('code')
            ->all();

        return $this->languagesByCode[$code] ?? null;
    }

    /**
     * Build the label displayed in forms and history.
     */
    private function label(string $name, string $code): string
    {
        return $this->plainLanguageName($name);
    }

    private function plainLanguageName(string $name): string
    {
        return trim(preg_replace('/\\s*\\([^)]*\\)\\s*$/', '', $name));
    }
}
