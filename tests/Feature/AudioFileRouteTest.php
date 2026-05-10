<?php

use App\Livewire\AudioFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

uses(RefreshDatabase::class);

test('generated wav files can be played from the storage url', function () {
    Storage::fake('public');
    Storage::disk('public')->put('audio/demo.wav', 'RIFFdemoWAVE');

    $response = $this->get('/storage/audio/demo.wav')
        ->assertOk()
        ->assertHeader('content-type', 'audio/wav')
        ->assertHeader('content-disposition', 'inline; filename="demo.wav"');

    expect($response->baseResponse)->toBeInstanceOf(BinaryFileResponse::class);
});

test('audio file route is owned by livewire instead of a controller', function () {
    $legacyController = app_path('Http/Controllers/'.'AudioFile'.'Controller.php');

    expect(file_exists($legacyController))->toBeFalse();
    expect(Route::getRoutes()->getByName('audio.files.show')?->getActionName())->toBe(AudioFile::class);
});

test('non wav audio storage paths are not served', function () {
    Storage::fake('public');
    Storage::disk('public')->put('audio/demo.txt', 'not audio');

    $this->get('/storage/audio/demo.txt')
        ->assertNotFound();
});
