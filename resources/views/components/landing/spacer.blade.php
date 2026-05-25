@props(['content' => []])

@php
    $size = $content['size'] ?? 'md';
    $height = match ($size) {
        'sm' => 'h-8 lg:h-12',
        'lg' => 'h-20 lg:h-28',
        'xl' => 'h-28 lg:h-40',
        default => 'h-14 lg:h-20',
    };
@endphp

<div class="w-full bg-transparent {{ $height }}" aria-hidden="true"></div>
