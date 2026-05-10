<?php

use App\Actions\AudioGenerations\GenerateAudioAction;
use App\Actions\AudioGenerations\LoadAudioGeneratorDataAction;
use App\Actions\AudioGenerations\RemovePreviousPromptAction;
use App\Actions\AudioGenerations\Requests\GenerateAudioRequest;
use App\Actions\AudioGenerations\Requests\LoadAudioGeneratorDataRequest;
use App\Actions\AudioGenerations\Requests\RemovePreviousPromptRequest;
use App\Actions\AudioGenerations\Requests\UsePreviousPromptRequest;
use App\Actions\AudioGenerations\Requests\UsePromptTemplateRequest;
use App\Actions\AudioGenerations\UsePreviousPromptAction;
use App\Actions\AudioGenerations\UsePromptTemplateAction;
use App\Actions\PromptTemplates\EditPromptTemplateAction;
use App\Actions\PromptTemplates\ListPromptTemplateLanguagesAction;
use App\Actions\PromptTemplates\ListPromptTemplatesAction;
use App\Actions\PromptTemplates\ListPromptTemplateVoiceGendersAction;
use App\Actions\PromptTemplates\ListPromptTemplateVoiceGeneratorsAction;
use App\Actions\PromptTemplates\RemovePromptTemplateAction;
use App\Actions\PromptTemplates\Requests\EditPromptTemplateRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateLanguagesRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplatesRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateVoiceGendersRequest;
use App\Actions\PromptTemplates\Requests\ListPromptTemplateVoiceGeneratorsRequest;
use App\Actions\PromptTemplates\Requests\RemovePromptTemplateRequest;
use App\Actions\PromptTemplates\Requests\ResetPromptTemplateFormRequest;
use App\Actions\PromptTemplates\Requests\SavePromptTemplateRequest;
use App\Actions\PromptTemplates\Requests\SelectPromptTemplateVoiceGenderRequest;
use App\Actions\PromptTemplates\ResetPromptTemplateFormAction;
use App\Actions\PromptTemplates\SavePromptTemplateAction;
use App\Actions\PromptTemplates\SelectPromptTemplateVoiceGenderAction;

test('domain actions are grouped with matching request objects', function () {
    $actions = [
        GenerateAudioAction::class => GenerateAudioRequest::class,
        LoadAudioGeneratorDataAction::class => LoadAudioGeneratorDataRequest::class,
        RemovePreviousPromptAction::class => RemovePreviousPromptRequest::class,
        UsePreviousPromptAction::class => UsePreviousPromptRequest::class,
        UsePromptTemplateAction::class => UsePromptTemplateRequest::class,
        EditPromptTemplateAction::class => EditPromptTemplateRequest::class,
        ListPromptTemplateLanguagesAction::class => ListPromptTemplateLanguagesRequest::class,
        ListPromptTemplatesAction::class => ListPromptTemplatesRequest::class,
        ListPromptTemplateVoiceGendersAction::class => ListPromptTemplateVoiceGendersRequest::class,
        ListPromptTemplateVoiceGeneratorsAction::class => ListPromptTemplateVoiceGeneratorsRequest::class,
        RemovePromptTemplateAction::class => RemovePromptTemplateRequest::class,
        ResetPromptTemplateFormAction::class => ResetPromptTemplateFormRequest::class,
        SavePromptTemplateAction::class => SavePromptTemplateRequest::class,
        SelectPromptTemplateVoiceGenderAction::class => SelectPromptTemplateVoiceGenderRequest::class,
    ];

    foreach ($actions as $actionClass => $requestClass) {
        expect(class_exists($actionClass))->toBeTrue("Missing action {$actionClass}");
        expect(class_exists($requestClass))->toBeTrue("Missing request {$requestClass}");
        expect((new ReflectionMethod($actionClass, 'handle'))->getParameters()[0]->getType()?->getName())->toBe($requestClass);
    }
});
