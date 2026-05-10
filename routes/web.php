<?php

use App\Livewire\AudioGenerator;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix('')
    ->name('audio.')
    ->group(function (): void {
        Route::livewire('/', AudioGenerator::class)->name('generator');
    });
