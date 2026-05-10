<?php

test('shared hosting document root has a root front controller', function () {
    $frontController = file_get_contents(base_path('index.php'));

    expect($frontController)->not->toBeFalse()
        ->and($frontController)->toContain("require __DIR__.'/vendor/autoload.php';")
        ->and($frontController)->toContain("require_once __DIR__.'/bootstrap/app.php';")
        ->and($frontController)->toContain('$app->handleRequest(Request::capture());');
});

test('laravel public path points at the shared hosting document root', function () {
    expect(public_path())->toBe(base_path());
});

test('shared hosting static files live in the document root', function () {
    expect(base_path('public'))->not->toBeDirectory()
        ->and(base_path('favicon.ico'))->toBeFile()
        ->and(base_path('robots.txt'))->toBeFile();
});

test('vite builds directly into the document root', function () {
    $viteConfig = file_get_contents(base_path('vite.config.js'));

    expect($viteConfig)->not->toBeFalse()
        ->and($viteConfig)->toContain("publicDirectory: '.'")
        ->and($viteConfig)->toContain("hotFile: 'hot'");
});

test('shared hosting document root protects laravel internals', function () {
    $htaccess = file_get_contents(base_path('.htaccess'));

    expect($htaccess)->not->toBeFalse()
        ->and($htaccess)->toContain('RewriteRule ^(?:app|bootstrap|config|database|node_modules|public|resources|routes|tests|vendor)(?:/|$) - [F,L]')
        ->and($htaccess)->toContain('RewriteRule ^storage/(.*)$ storage/app/public/$1 [L]')
        ->and($htaccess)->toContain('RewriteRule ^ index.php [L]')
        ->and($htaccess)->not->toContain('public/build')
        ->and($htaccess)->not->toContain('public/favicon.ico')
        ->and($htaccess)->not->toContain('public/robots.txt');
});
