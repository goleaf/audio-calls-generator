<?php

use App\Livewire\PromptTemplateFormPage;
use App\Livewire\PromptTemplateIndex;
use App\Models\PromptTemplate;
use Database\Seeders\PromptTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('prompt templates index page renders only the list from the top menu', function () {
    $template = PromptTemplate::factory()->create([
        'title' => 'Reusable reminder',
        'master_prompt' => 'Speak clearly.',
        'prompt_text' => 'Remind the caller about the appointment.',
    ]);

    $this->get(route('audio.generator'))
        ->assertSuccessful()
        ->assertSee('Prompt templates');

    $this->get(route('audio.prompt-templates'))
        ->assertSuccessful()
        ->assertSee('Prompt templates')
        ->assertSee('Audio generator')
        ->assertSee('Create template')
        ->assertSee('Actions')
        ->assertSee($template->title)
        ->assertDontSee('Template title')
        ->assertDontSee('Save template');
});

test('prompt template create page renders only the form', function () {
    $template = PromptTemplate::factory()->create(['title' => 'Existing table item']);

    $this->get(route('audio.prompt-templates.create'))
        ->assertSuccessful()
        ->assertSee('Create prompt template')
        ->assertSee('Template title')
        ->assertSee('Master prompt')
        ->assertSee('Prompt text')
        ->assertSee('Language')
        ->assertSee('Lithuanian (Lithuania) - lt-LT')
        ->assertSee('Voice gender')
        ->assertSee('Voice generator')
        ->assertSee('Save template')
        ->assertDontSee('Actions')
        ->assertDontSee($template->title);
});

test('prompt template edit page renders only the form with saved values', function () {
    $template = PromptTemplate::factory()->create([
        'title' => 'Editable template',
        'master_prompt' => 'Speak like a calm assistant.',
        'prompt_text' => 'Remind the caller about the appointment.',
        'language_code' => 'en-US',
        'language_name' => 'English (United States)',
        'language_readiness' => 'GA',
        'tts_voice' => 'Kore',
        'tts_voice_gender' => 'Female',
        'tts_voice_label' => 'Female - Kore',
    ]);

    $this->get(route('audio.prompt-templates.edit', $template))
        ->assertSuccessful()
        ->assertSee('Edit prompt template')
        ->assertSee('Editable template')
        ->assertSee('Update template')
        ->assertSee('English (United States) - en-US')
        ->assertDontSee('Create template')
        ->assertDontSee('Actions');
});

test('it creates a prompt template with all generation settings', function () {
    Livewire::test(PromptTemplateFormPage::class)
        ->set('form.title', 'Warm welcome')
        ->set('form.masterPrompt', 'Speak with a warm support tone.')
        ->set('form.selectedLanguageCode', 'lt-LT')
        ->call('selectVoiceGender', 'Male')
        ->set('form.selectedVoice', 'Puck')
        ->set('form.promptText', 'Welcome the caller with a calm and clear greeting.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('audio.prompt-templates');

    $this->assertDatabaseHas('prompt_templates', [
        'title' => 'Warm welcome',
        'master_prompt' => 'Speak with a warm support tone.',
        'prompt_text' => 'Welcome the caller with a calm and clear greeting.',
        'language_code' => 'lt-LT',
        'language_name' => 'Lithuanian (Lithuania)',
        'language_readiness' => 'Preview',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);
});

test('voice generator names are filtered by selected template gender', function () {
    Livewire::test(PromptTemplateFormPage::class)
        ->assertSet('form.selectedVoiceGender', 'Female')
        ->assertSet('form.selectedVoice', 'Kore')
        ->assertSeeHtml('<option value="Kore">Kore</option>')
        ->assertDontSeeHtml('<option value="Puck">Puck</option>')
        ->call('selectVoiceGender', 'Male')
        ->assertSet('form.selectedVoiceGender', 'Male')
        ->assertSet('form.selectedVoice', 'Puck')
        ->assertSeeHtml('<option value="Puck">Puck</option>')
        ->assertDontSeeHtml('<option value="Kore">Kore</option>');
});

test('it updates a prompt template', function () {
    $template = PromptTemplate::factory()->create([
        'title' => 'Short reminder',
        'master_prompt' => 'Speak like a calm assistant.',
        'prompt_text' => 'Remind the caller about the appointment.',
        'language_code' => 'en-US',
        'language_name' => 'English (United States)',
        'language_readiness' => 'GA',
        'tts_voice' => 'Kore',
        'tts_voice_gender' => 'Female',
        'tts_voice_label' => 'Female - Kore',
    ]);

    Livewire::test(PromptTemplateFormPage::class, ['promptTemplate' => $template])
        ->assertSet('editingTemplateId', $template->id)
        ->assertSet('form.title', 'Short reminder')
        ->assertSet('form.masterPrompt', 'Speak like a calm assistant.')
        ->assertSet('form.selectedLanguageCode', 'en-US')
        ->assertSet('form.selectedVoiceGender', 'Female')
        ->assertSet('form.selectedVoice', 'Kore')
        ->assertSet('form.promptText', 'Remind the caller about the appointment.')
        ->set('form.title', 'Updated reminder')
        ->set('form.masterPrompt', 'Speak in Lithuanian with a confident tone.')
        ->set('form.selectedLanguageCode', 'lt-LT')
        ->call('selectVoiceGender', 'Male')
        ->set('form.selectedVoice', 'Puck')
        ->set('form.promptText', 'Tell the caller their appointment starts at nine.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('audio.prompt-templates');

    expect(PromptTemplate::query()->count())->toBe(1);

    $this->assertDatabaseHas('prompt_templates', [
        'id' => $template->id,
        'title' => 'Updated reminder',
        'master_prompt' => 'Speak in Lithuanian with a confident tone.',
        'prompt_text' => 'Tell the caller their appointment starts at nine.',
        'language_code' => 'lt-LT',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
        'tts_voice_label' => 'Male - Puck',
    ]);
});

test('it cancels prompt template editing', function () {
    $template = PromptTemplate::factory()->create(['title' => 'Editable template']);

    Livewire::test(PromptTemplateFormPage::class, ['promptTemplate' => $template])
        ->assertSet('editingTemplateId', $template->id)
        ->call('cancelEdit')
        ->assertRedirectToRoute('audio.prompt-templates');
});

test('it validates prompt template fields', function () {
    Livewire::test(PromptTemplateFormPage::class)
        ->set('form.title', '')
        ->set('form.masterPrompt', '')
        ->set('form.selectedLanguageCode', 'missing')
        ->call('selectVoiceGender', 'Female')
        ->set('form.selectedVoice', 'Puck')
        ->set('form.promptText', '')
        ->call('save')
        ->assertHasErrors([
            'form.title' => ['required'],
            'form.masterPrompt' => ['required'],
            'form.selectedLanguageCode' => ['in'],
            'form.selectedVoice' => ['in'],
            'form.promptText' => ['required'],
        ])
        ->assertSee('Enter a template title.')
        ->assertSee('Enter a master prompt.')
        ->assertSee('Choose an available language.')
        ->assertSee('Choose a generator from the selected gender.')
        ->assertSee('Enter prompt text.');
});

test('it removes a prompt template', function () {
    $template = PromptTemplate::factory()->create([
        'title' => 'Short reminder',
        'prompt_text' => 'Remind the caller about the appointment.',
        'language_code' => 'en-US',
        'language_name' => 'English (United States)',
        'language_readiness' => 'GA',
        'tts_voice' => 'Kore',
        'tts_voice_gender' => 'Female',
        'tts_voice_label' => 'Female - Kore',
    ]);

    Livewire::test(PromptTemplateIndex::class)
        ->assertSee('Short reminder')
        ->call('remove', $template->id)
        ->assertSet('successMessage', 'Prompt template has been removed.')
        ->assertDontSee('Short reminder');

    $this->assertDatabaseMissing('prompt_templates', [
        'id' => $template->id,
    ]);
});

test('prompt template seeder stores complete prompt templates', function () {
    $this->seed(PromptTemplateSeeder::class);

    expect(PromptTemplate::query()->count())->toBeGreaterThan(1);

    $this->assertDatabaseHas('prompt_templates', [
        'title' => 'Warm support greeting',
        'language_code' => 'en-US',
        'tts_voice' => 'Kore',
        'tts_voice_gender' => 'Female',
    ]);

    $this->assertDatabaseHas('prompt_templates', [
        'title' => 'Lithuanian billing reminder',
        'language_code' => 'lt-LT',
        'tts_voice' => 'Puck',
        'tts_voice_gender' => 'Male',
    ]);
});
