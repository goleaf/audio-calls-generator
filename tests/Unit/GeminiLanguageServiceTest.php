<?php

use App\Services\GeminiLanguageService;

test('it exposes the official Gemini TTS languages grouped by readiness', function () {
    $languages = app(GeminiLanguageService::class);

    expect($languages->codes())->toHaveCount(87)
        ->and($languages->codes())->toContain('en-US', 'lt-LT', 'es-419', 'cmn-tw')
        ->and($languages->groups())->toHaveKeys(['GA', 'Preview'])
        ->and($languages->groups()['GA'])->toHaveCount(24)
        ->and($languages->groups()['Preview'])->toHaveCount(63)
        ->and($languages->find('lt-LT'))->toMatchArray([
            'code' => 'lt-LT',
            'name' => 'Lithuanian (Lithuania)',
            'readiness' => 'Preview',
            'label' => 'Lithuanian (Lithuania) - lt-LT',
        ])
        ->and($languages->find('nb-NO'))->toMatchArray([
            'code' => 'nb-NO',
            'name' => 'Norwegian, Bokmål (Norway)',
            'readiness' => 'Preview',
            'label' => 'Norwegian, Bokmål (Norway) - nb-NO',
        ])
        ->and($languages->default())->toMatchArray([
            'code' => 'en-US',
            'name' => 'English (United States)',
            'readiness' => 'GA',
            'label' => 'English (United States) - en-US',
        ]);
});
