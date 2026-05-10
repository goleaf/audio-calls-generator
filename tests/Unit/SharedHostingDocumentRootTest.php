<?php

test('shared hosting document root has a root front controller', function () {
    $frontController = file_get_contents(base_path('index.php'));

    expect($frontController)->not->toBeFalse()
        ->and($frontController)->toContain("require __DIR__.'/vendor/autoload.php';")
        ->and($frontController)->toContain("require_once __DIR__.'/bootstrap/app.php';")
        ->and($frontController)->toContain('$app->handleRequest(Request::capture());');
});

test('shared hosting document root protects laravel internals', function () {
    $htaccess = file_get_contents(base_path('.htaccess'));

    expect($htaccess)->not->toBeFalse()
        ->and($htaccess)->toContain('RewriteRule ^(?:app|bootstrap|config|database|node_modules|resources|routes|tests|vendor)(?:/|$) - [F,L]')
        ->and($htaccess)->toContain('RewriteRule ^build/(.*)$ public/build/$1 [L]')
        ->and($htaccess)->toContain('RewriteRule ^storage/(.*)$ storage/app/public/$1 [L]')
        ->and($htaccess)->toContain('RewriteRule ^ index.php [L]');
});
