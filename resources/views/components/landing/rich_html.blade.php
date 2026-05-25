@props(['content' => []])

@php
    $navTheme = $content['nav_theme'] ?? 'light';
    $proseSize = match ($content['prose_width'] ?? 'wide') {
        'default' => 'default',
        'full' => 'full',
        default => 'wide',
    };
@endphp

<section class="w-full {{ $navTheme === 'dark' ? 'bg-black-brand text-white-brand' : 'bg-white-brand text-black-brand' }}"
    data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-16 lg:px-8 lg:py-20">
        <x-landing.prose
            :html="$content['html'] ?? null"
            :theme="$navTheme"
            align="left"
            :size="$proseSize"
            class="w-full"
        />
    </div>
</section>
