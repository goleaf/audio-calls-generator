<?php

use App\Livewire\AudioGenerator;
use App\Livewire\Forms\PromptTemplateForm;
use App\Rules\AudioGenerations\GenerateAudioRules;
use App\Rules\PromptTemplates\PromptTemplateRules;

test('validation rules are stored in grouped rule classes', function () {
    expect(class_exists(GenerateAudioRules::class))->toBeTrue()
        ->and(class_exists(PromptTemplateRules::class))->toBeTrue()
        ->and(method_exists(GenerateAudioRules::class, 'rules'))->toBeTrue()
        ->and(method_exists(GenerateAudioRules::class, 'messages'))->toBeTrue()
        ->and(method_exists(PromptTemplateRules::class, 'rules'))->toBeTrue()
        ->and(method_exists(PromptTemplateRules::class, 'messages'))->toBeTrue();
});

test('livewire components and forms do not own validation messages', function () {
    expect((new ReflectionClass(PromptTemplateForm::class))->hasMethod('messages'))->toBeFalse()
        ->and((new ReflectionClass(PromptTemplateForm::class))->hasMethod('rules'))->toBeFalse()
        ->and((new ReflectionClass(AudioGenerator::class))->hasMethod('validationMessages'))->toBeFalse()
        ->and((new ReflectionClass(AudioGenerator::class))->hasMethod('templateRules'))->toBeFalse();
});
