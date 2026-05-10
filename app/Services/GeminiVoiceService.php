<?php

namespace App\Services;

class GeminiVoiceService
{
    private const DEFAULT_VOICE = 'Kore';

    private const GENERATORS = [
        ['name' => 'Kore', 'gender' => 'Female'],
        ['name' => 'Puck', 'gender' => 'Male'],
        ['name' => 'Aoede', 'gender' => 'Female'],
        ['name' => 'Charon', 'gender' => 'Male'],
        ['name' => 'Leda', 'gender' => 'Female'],
        ['name' => 'Orus', 'gender' => 'Male'],
        ['name' => 'Zephyr', 'gender' => 'Female'],
        ['name' => 'Fenrir', 'gender' => 'Male'],
        ['name' => 'Despina', 'gender' => 'Female'],
        ['name' => 'Enceladus', 'gender' => 'Male'],
        ['name' => 'Callirrhoe', 'gender' => 'Female'],
        ['name' => 'Iapetus', 'gender' => 'Male'],
        ['name' => 'Autonoe', 'gender' => 'Female'],
        ['name' => 'Umbriel', 'gender' => 'Male'],
        ['name' => 'Erinome', 'gender' => 'Female'],
        ['name' => 'Algieba', 'gender' => 'Male'],
        ['name' => 'Achernar', 'gender' => 'Female'],
        ['name' => 'Algenib', 'gender' => 'Male'],
        ['name' => 'Gacrux', 'gender' => 'Female'],
        ['name' => 'Rasalgethi', 'gender' => 'Male'],
        ['name' => 'Pulcherrima', 'gender' => 'Female'],
        ['name' => 'Alnilam', 'gender' => 'Male'],
        ['name' => 'Vindemiatrix', 'gender' => 'Female'],
        ['name' => 'Schedar', 'gender' => 'Male'],
        ['name' => 'Sadachbia', 'gender' => 'Male'],
        ['name' => 'Achird', 'gender' => 'Male'],
        ['name' => 'Sulafat', 'gender' => 'Female'],
        ['name' => 'Zubenelgenubi', 'gender' => 'Male'],
        ['name' => 'Laomedeia', 'gender' => 'Female'],
        ['name' => 'Sadaltager', 'gender' => 'Male'],
    ];

    /** @var list<array{name: string, gender: string, label: string}>|null */
    private ?array $generators = null;

    /** @var array<string, array{name: string, gender: string, label: string}>|null */
    private ?array $generatorsByName = null;

    /**
     * Return every supported Gemini prebuilt voice with display labels.
     *
     * @return list<array{name: string, gender: string, label: string}>
     */
    public function generators(): array
    {
        return $this->generators ??= collect(self::GENERATORS)
            ->map(fn (array $generator): array => [
                'name' => $generator['name'],
                'gender' => $generator['gender'],
                'label' => $this->label($generator['gender'], $generator['name']),
            ])
            ->all();
    }

    /**
     * Return the unique gender names used to group voice generators.
     *
     * @return list<string>
     */
    public function genders(): array
    {
        return collect(self::GENERATORS)
            ->pluck('gender')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Return voice generators that belong to one selected gender.
     *
     * @return list<array{name: string, gender: string, label: string}>
     */
    public function generatorsForGender(string $gender): array
    {
        return collect($this->generators())
            ->filter(fn (array $generator): bool => $generator['gender'] === $gender)
            ->values()
            ->all();
    }

    /**
     * Return all supported prebuilt voice names.
     *
     * @return list<string>
     */
    public function names(): array
    {
        return collect(self::GENERATORS)
            ->pluck('name')
            ->all();
    }

    /**
     * Return voice names that belong to one selected gender.
     *
     * @return list<string>
     */
    public function namesForGender(string $gender): array
    {
        return collect($this->generatorsForGender($gender))
            ->pluck('name')
            ->all();
    }

    /**
     * Return the configured default voice, falling back to Kore.
     *
     * @return array{name: string, gender: string, label: string}
     */
    public function default(): array
    {
        return $this->find((string) config('services.gemini.voice', self::DEFAULT_VOICE))
            ?? $this->find(self::DEFAULT_VOICE)
            ?? [
                'name' => self::DEFAULT_VOICE,
                'gender' => 'Female',
                'label' => $this->label('Female', self::DEFAULT_VOICE),
            ];
    }

    /**
     * Find one prebuilt voice by its Gemini voice name.
     *
     * @return array{name: string, gender: string, label: string}|null
     */
    public function find(string $name): ?array
    {
        $this->generatorsByName ??= collect($this->generators())
            ->keyBy('name')
            ->all();

        return $this->generatorsByName[$name] ?? null;
    }

    /**
     * Build the label displayed in saved prompt history.
     */
    private function label(string $gender, string $name): string
    {
        return "{$gender} - {$name}";
    }
}
