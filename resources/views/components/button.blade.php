@props([
    'as' => 'button',
    'type' => 'button',
    'size' => 'md',
])

@php
    $sizeClasses = [
        'sm' => 'min-h-8 px-3 py-2',
        'md' => 'min-h-10 px-4 py-2',
        'lg' => 'min-h-11 px-4 py-2',
    ];

    $class =
        'audio-generator__button inline-flex items-center justify-center gap-1.5 rounded-md border border-slate-900 bg-slate-950 text-sm font-medium text-white transition-colors duration-150 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950 hover:bg-slate-900 hover:text-white disabled:cursor-not-allowed disabled:opacity-60';

    $class .= ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<{{ $as }}
    @if ($as === 'button')
        type="{{ $type }}"
    @endif
    {{ $attributes->class($class) }}
>
    {{ $slot }}
</{{ $as }}>
