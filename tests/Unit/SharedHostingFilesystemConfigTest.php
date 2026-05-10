<?php

use Illuminate\Support\Env;

test('public disk can target the shared hosting document root', function () {
    Env::getRepository()->set('APP_URL', 'https://audio-calls-generator.prus.dev');
    Env::getRepository()->set('PUBLIC_DISK_ROOT', 'storage/app/public');
    Env::getRepository()->set('PUBLIC_DISK_URL', 'https://audio-calls-generator.prus.dev/storage');
    Env::getRepository()->set('PUBLIC_STORAGE_LINK', '');

    $filesystems = require config_path('filesystems.php');

    expect($filesystems['disks']['public']['root'])->toBe(storage_path('app/public'))
        ->and($filesystems['disks']['public']['url'])->toBe('https://audio-calls-generator.prus.dev/storage')
        ->and($filesystems['links'])->toBe([]);
});
