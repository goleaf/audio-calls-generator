<?php

use App\Http\Controllers\AudioFileController;
use App\Livewire\AudioGenerator;
use App\Livewire\PromptTemplateManager;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix('')
    ->name('audio.')
    ->group(function (): void {
        Route::get('/storage/audio/{fileName}', AudioFileController::class)
            ->where('fileName', '[A-Za-z0-9._-]+')
            ->name('files.show');

        Route::livewire('/', AudioGenerator::class)->name('generator');

        Route::livewire('/prompt-templates', PromptTemplateManager::class)->name('prompt-templates');
    });
