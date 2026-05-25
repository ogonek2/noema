@props(['content' => []])

@php
    $navTheme = $content['nav_theme'] ?? 'light';
    $align = $content['align'] ?? 'center';
    $proseSize = match ($content['prose_width'] ?? 'default') {
        'wide' => 'wide',
        'full' => 'full',
        default => 'default',
    };
    $containerAlign = $align === 'left' ? 'items-start text-left' : 'items-center text-center';
@endphp

<section class="w-full {{ $navTheme === 'dark' ? 'bg-black-brand text-white-brand' : 'bg-white-brand text-black-brand' }}"
    data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto flex w-full max-w-layout flex-col px-5 py-16 lg:px-8 lg:py-24 {{ $containerAlign }}">
        @if (filled($content['badge'] ?? null))
            <p class="text-[0.72rem] uppercase tracking-[0.28em] opacity-50">{{ $content['badge'] }}</p>
        @endif
        @if (filled($content['title'] ?? null))
            <h2 class="mt-3 max-w-4xl text-[clamp(1.75rem,4vw,3.5rem)] font-thin uppercase leading-[1.05] tracking-[0.06em]">
                {{ $content['title'] }}
            </h2>
        @endif
        @if (filled($content['body'] ?? null))
            <x-landing.prose
                :html="$content['body']"
                :theme="$navTheme"
                :align="$align"
                :size="$proseSize"
                class="mt-8 w-full"
            />
        @endif
    </div>
</section>
