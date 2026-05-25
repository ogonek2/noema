@props(['content' => []])

@php
    use App\Services\LandingPageService;
    use App\Support\MediaUrl;

    $navTheme = $content['nav_theme'] ?? 'light';
    $imageRight = ($content['layout'] ?? 'image_right') === 'image_right';
    $image = MediaUrl::resolve($content['image'] ?? null);
    $ctaHref = LandingPageService::link($content['cta_href'] ?? null, route('catalog.index'));
    $proseSize = match ($content['prose_width'] ?? 'wide') {
        'full' => 'full',
        'default' => 'default',
        default => 'wide',
    };
@endphp

<section class="w-full {{ $navTheme === 'dark' ? 'bg-black-brand text-white-brand' : 'bg-white-brand text-black-brand' }}"
    data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto grid w-full max-w-layout items-center gap-10 px-5 py-16 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
        <div class="{{ $imageRight ? 'lg:order-1' : 'lg:order-2' }}">
            @if (filled($content['badge'] ?? null))
                <p class="text-[0.72rem] uppercase tracking-[0.28em] opacity-45">{{ $content['badge'] }}</p>
            @endif
            @if (filled($content['title'] ?? null))
                <h2 class="mt-3 text-[clamp(1.5rem,3.5vw,2.75rem)] font-thin uppercase leading-[1.05] tracking-[0.06em]">
                    {{ $content['title'] }}
                </h2>
            @endif
            @if (filled($content['body'] ?? null))
                <x-landing.prose
                    :html="$content['body']"
                    :theme="$navTheme"
                    align="left"
                    :size="$proseSize"
                    class="mt-6"
                />
            @endif
            @if (filled($content['cta_label'] ?? null))
                <a href="{{ $ctaHref }}"
                    class="mt-8 inline-flex min-w-[200px] items-center justify-center border border-blue-brand bg-blue-brand px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition-colors duration-300 hover:bg-transparent hover:text-blue-brand">
                    {{ $content['cta_label'] }}
                </a>
            @endif
        </div>

        @if ($image)
            <div class="{{ $imageRight ? 'lg:order-2' : 'lg:order-1' }}">
                <img src="{{ $image }}" alt="{{ $content['title'] ?? '' }}" class="aspect-[4/5] w-full object-cover lg:aspect-auto lg:min-h-[420px]">
            </div>
        @endif
    </div>
</section>
