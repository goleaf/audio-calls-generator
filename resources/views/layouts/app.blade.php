<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <meta name="format-detection" content="telephone=no">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%2309090b'/%3E%3Cpath d='M20 40V24h7l11-9v34L27 40h-7Z' fill='%2367e8f9'/%3E%3Cpath d='M43 24c3 4 3 12 0 16M49 18c6 8 6 20 0 28' fill='none' stroke='%23a7f3d0' stroke-width='4' stroke-linecap='round'/%3E%3C/svg%3E">

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js'])
        @endif

        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <nav class="border-b border-slate-200 bg-white text-slate-950">
            <div class="mx-auto flex w-full max-w-[120rem] items-center gap-2 px-3 py-2 sm:px-4">
                <x-button
                    as="a"
                    href="{{ route('audio.generator') }}"
                    wire:navigate
                    class="min-h-9 px-3 text-sm"
                >
                    <x-icon name="audio-lines" class="size-3.5" />
                    <span>Audio generator</span>
                </x-button>

                <x-button
                    as="a"
                    href="{{ route('audio.prompt-templates') }}"
                    wire:navigate
                    class="min-h-9 px-3 text-sm"
                >
                    <x-icon name="notebook-pen" class="size-3.5" />
                    <span>Prompt templates</span>
                </x-button>
            </div>
        </nav>

        {{ $slot }}

        @livewireScripts
    </body>
</html>
