<?php

use App\Livewire\AudioFile;
use App\Livewire\AudioGenerator;
use App\Livewire\PromptTemplateFormPage;
use App\Livewire\PromptTemplateIndex;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix('')
    ->name('audio.')
    ->group(function (): void {
        Route::get('/storage/audio/{fileName}', AudioFile::class)
            ->where('fileName', '[A-Za-z0-9._-]+')
            ->name('files.show');

        Route::livewire('/', AudioGenerator::class)->name('generator');

        Route::livewire('/prompt-templates', PromptTemplateIndex::class)->name('prompt-templates');
        Route::livewire('/prompt-templates/create', PromptTemplateFormPage::class)->name('prompt-templates.create');
        Route::livewire('/prompt-templates/{promptTemplate}/edit', PromptTemplateFormPage::class)
            ->whereNumber('promptTemplate')
            ->name('prompt-templates.edit');
    });
