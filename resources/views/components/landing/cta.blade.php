@props(['content' => []])

@php
    use App\Services\LandingPageService;

    $style = $content['style'] ?? 'dark';
    $classes = match ($style) {
        'light' => 'bg-white-brand text-black-brand',
        'blue' => 'bg-blue-brand text-white-brand',
        default => 'bg-black-brand text-white-brand',
    };
    $navTheme = in_array($style, ['light'], true) ? 'light' : 'dark';
    $primaryHref = LandingPageService::link($content['cta_primary_href'] ?? null, route('catalog.index'));
    $secondaryHref = LandingPageService::link($content['cta_secondary_href'] ?? null, '#');
@endphp

<section class="w-full {{ $classes }}" data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-16 text-center lg:px-8 lg:py-20">
        @if (filled($content['title'] ?? null))
            <h2 class="text-[2rem] font-thin uppercase tracking-[0.08em] lg:text-[3rem]">{{ $content['title'] }}</h2>
        @endif
        @if (filled($content['text'] ?? null))
            <p class="mx-auto mt-5 max-w-2xl text-[1rem] leading-relaxed opacity-80">{{ $content['text'] }}</p>
        @endif
        <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
            @if (filled($content['cta_primary_label'] ?? null))
                <a href="{{ $primaryHref }}"
                    class="min-w-[200px] border border-current bg-white-brand px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] text-black-brand transition-colors duration-300 hover:bg-transparent hover:text-white-brand">
                    {{ $content['cta_primary_label'] }}
                </a>
            @endif
            @if (filled($content['cta_secondary_label'] ?? null))
                <a href="{{ $secondaryHref }}"
                    class="min-w-[200px] border border-current px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] transition-colors duration-300 hover:opacity-70">
                    {{ $content['cta_secondary_label'] }}
                </a>
            @endif
        </div>
    </div>
</section>
