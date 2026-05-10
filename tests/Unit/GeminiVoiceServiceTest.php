<?php

use App\Services\GeminiVoiceService;

test('it exposes the official Gemini TTS voices grouped by gender', function () {
    $voices = app(GeminiVoiceService::class);

    expect($voices->names())->toHaveCount(30)
        ->and($voices->namesForGender('Female'))->toEqualCanonicalizing([
            'Achernar',
            'Aoede',
            'Autonoe',
            'Callirrhoe',
            'Despina',
            'Erinome',
            'Gacrux',
            'Kore',
            'Laomedeia',
            'Leda',
            'Pulcherrima',
            'Sulafat',
            'Vindemiatrix',
            'Zephyr',
        ])
        ->and($voices->namesForGender('Male'))->toEqualCanonicalizing([
            'Achird',
            'Algenib',
            'Algieba',
            'Alnilam',
            'Charon',
            'Enceladus',
            'Fenrir',
            'Iapetus',
            'Orus',
            'Puck',
            'Rasalgethi',
            'Sadachbia',
            'Sadaltager',
            'Schedar',
            'Umbriel',
            'Zubenelgenubi',
        ])
        ->and($voices->find('Sadachbia'))->toMatchArray([
            'name' => 'Sadachbia',
            'gender' => 'Male',
            'label' => 'Male - Sadachbia',
        ]);
});
