@props([
    'src',
    'alt' => '',
    'class' => '',
    'wrapperClass' => '',
    'aspect' => null,
    'priority' => false,
])

@php
    $shellClass = trim('media-shell relative overflow-hidden '.$wrapperClass);
    $imgClass = trim('media-img h-full w-full object-cover opacity-0 transition-opacity duration-500 '.$class);
@endphp

<div class="{{ $shellClass }}" data-media-shell @if($aspect) style="aspect-ratio: {{ $aspect }};" @endif>
    <x-ui.skeleton class="media-skeleton absolute inset-0 rounded-none" />
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        class="{{ $imgClass }}"
        @unless($priority) loading="lazy" @endunless
        decoding="async"
        data-media-img
        onload="this.classList.add('is-loaded'); this.closest('[data-media-shell]')?.setAttribute('data-loaded','true')"
        onerror="this.closest('[data-media-shell]')?.setAttribute('data-error','true')"
    >
    {{ $slot }}
</div>
