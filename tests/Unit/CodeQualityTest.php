<?php

test('application methods have phpdoc summaries', function () {
    $missing = collect(appClassNames())
        ->flatMap(function (string $class): array {
            $reflection = new ReflectionClass($class);

            return collect($reflection->getMethods())
                ->filter(fn (ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $class)
                ->filter(fn (ReflectionMethod $method): bool => $method->getDocComment() === false)
                ->map(fn (ReflectionMethod $method): string => "{$class}::{$method->getName()}()")
                ->all();
        })
        ->values()
        ->all();

    expect($missing)->toBe([]);
});

test('application does not use livewire volt', function () {
    $files = collect([
        app_path(),
        resource_path('views'),
        base_path('routes'),
        base_path('config'),
        base_path('composer.json'),
        base_path('package.json'),
        base_path('vite.config.js'),
    ]);

    $matches = $files
        ->flatMap(fn (string $path): array => scanTextFilesForVolt($path))
        ->values()
        ->all();

    expect($matches)->toBe([]);
});

/**
 * Return all autoloadable application class names from the app directory.
 *
 * @return list<class-string>
 */
function appClassNames(): array
{
    return collect(appPhpFiles())
        ->map(fn (SplFileInfo $file): ?string => classNameFromFile($file->getPathname()))
        ->filter()
        ->values()
        ->all();
}

/**
 * Return PHP files from the application source directory.
 *
 * @return list<SplFileInfo>
 */
function appPhpFiles(): array
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path()));

    return collect($iterator)
        ->filter(fn (SplFileInfo $file): bool => $file->isFile() && $file->getExtension() === 'php')
        ->values()
        ->all();
}

/**
 * Derive a class name from a PHP file when it contains a concrete class declaration.
 */
function classNameFromFile(string $path): ?string
{
    $contents = file_get_contents($path);

    if ($contents === false) {
        return null;
    }

    preg_match('/namespace\s+([^;]+);/', $contents, $namespace);
    preg_match('/(?:abstract\s+|final\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)/', $contents, $class);

    if (! isset($namespace[1], $class[1])) {
        return null;
    }

    $className = "{$namespace[1]}\\{$class[1]}";

    return class_exists($className) ? $className : null;
}

/**
 * Scan a file or directory tree for Livewire Volt usage.
 *
 * @return list<string>
 */
function scanTextFilesForVolt(string $path): array
{
    if (is_file($path)) {
        return fileContainsVolt($path) ? [$path] : [];
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    return collect($iterator)
        ->filter(fn (SplFileInfo $file): bool => $file->isFile())
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['php', 'blade.php', 'json', 'js'], true))
        ->map(fn (SplFileInfo $file): string => $file->getPathname())
        ->filter(fn (string $file): bool => fileContainsVolt($file))
        ->values()
        ->all();
}

/**
 * Check whether a text file contains a Livewire Volt reference.
 */
function fileContainsVolt(string $path): bool
{
    $contents = file_get_contents($path);

    if ($contents === false) {
        return false;
    }

    return preg_match('/Livewire\\\\Volt|@volt|livewire:volt/i', $contents) === 1;
}
