<?php

use App\Livewire\PromptTemplateManager;
use App\Models\PromptTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('prompt templates page renders from the top menu', function () {
    $this->get(route('audio.generator'))
        ->assertSuccessful()
        ->assertSee('Prompt templates');

    $this->get(route('audio.prompt-templates'))
        ->assertSuccessful()
        ->assertSee('Prompt templates')
        ->assertSee('Audio generator')
        ->assertSee('Template title')
        ->assertSee('Language')
        ->assertSee('Lithuanian (Lithuania) - lt-LT')
        ->assertSee('Prompt text');
});

test('it creates a prompt template', function () {
    Livewire::test(PromptTemplateManager::class)
        ->set('title', 'Warm welcome')
        ->set('selectedLanguageCode', 'lt-LT')
        ->set('promptText', 'Welcome the caller with a calm and clear greeting.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('title', '')
        ->assertSet('promptText', '')
        ->assertSet('successMessage', 'Prompt template has been saved.')
        ->assertSee('Warm welcome');

    $this->assertDatabaseHas('prompt_templates', [
        'title' => 'Warm welcome',
        'prompt_text' => 'Welcome the caller with a calm and clear greeting.',
        'language_code' => 'lt-LT',
        'language_name' => 'Lithuanian (Lithuania)',
        'language_readiness' => 'Preview',
    ]);
});

test('it validates prompt template fields', function () {
    Livewire::test(PromptTemplateManager::class)
        ->set('title', '')
        ->set('selectedLanguageCode', 'missing')
        ->set('promptText', '')
        ->call('save')
        ->assertHasErrors([
            'title' => ['required'],
            'selectedLanguageCode' => ['in'],
            'promptText' => ['required'],
        ])
        ->assertSee('Enter a template title.')
        ->assertSee('Choose an available language.')
        ->assertSee('Enter prompt text.');
});

test('it removes a prompt template', function () {
    $template = PromptTemplate::factory()->create([
        'title' => 'Short reminder',
        'prompt_text' => 'Remind the caller about the appointment.',
        'language_code' => 'en-US',
        'language_name' => 'English (United States)',
        'language_readiness' => 'GA',
    ]);

    Livewire::test(PromptTemplateManager::class)
        ->assertSee('Short reminder')
        ->call('remove', $template->id)
        ->assertSet('successMessage', 'Prompt template has been removed.')
        ->assertDontSee('Short reminder');

    $this->assertDatabaseMissing('prompt_templates', [
        'id' => $template->id,
    ]);
});
