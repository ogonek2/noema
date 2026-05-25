@props(['content' => []])

@php
    use App\Enums\LandingHeroImageMode;
    use App\Services\LandingPageService;
    use App\Support\LandingHeroImage;
    use App\Support\MediaUrl;

    $navTheme = $content['nav_theme'] ?? 'dark';
    $isDark = $navTheme === 'dark';
    $image = MediaUrl::resolve($content['image'] ?? null, 'images/women.png');
    $imageMode = LandingHeroImage::mode($content);
    $imageFocal = LandingHeroImage::focal($content);
    $objectStyles = LandingHeroImage::objectStyles($content);

    $primaryHref = LandingPageService::link($content['cta_primary_href'] ?? null, route('catalog.index'));
    $secondaryHref = LandingPageService::link($content['cta_secondary_href'] ?? null, route('catalog.index'));

    $titleLines = filled($content['title'] ?? null)
        ? preg_split('/\s*\\\\\s*|\r\n|\n/', (string) $content['title'], -1, PREG_SPLIT_NO_EMPTY)
        : [];
@endphp

<section @class([
    'landing-hero',
    'landing-hero--dark' => $isDark,
    'landing-hero--light' => ! $isDark,
    LandingHeroImage::cssClass($content),
]) data-nav-theme="{{ $navTheme }}" data-image-mode="{{ $imageMode->value }}" data-aos="fade-up">
    <div class="landing-hero__backdrop" aria-hidden="true"></div>
    <div class="landing-hero__grain" aria-hidden="true"></div>

    @if ($imageMode === LandingHeroImageMode::BackgroundCover)
        <div class="landing-hero__bg-media" aria-hidden="true">
            <img src="{{ $image }}" alt="" class="landing-hero__bg-image"
                style="object-position: {{ $imageFocal->objectPosition() }}">
        </div>
        <div class="landing-hero__bg-scrim" aria-hidden="true"></div>
    @endif

    @if ($imageMode === LandingHeroImageMode::AmbientRight)
        <div class="landing-hero__ambient" aria-hidden="true">
            <img src="{{ $image }}" alt="" class="landing-hero__ambient-image"
                style="object-position: {{ $imageFocal->objectPosition() }}">
        </div>
    @endif

    @if ($imageMode === LandingHeroImageMode::FreeObject)
        <div class="landing-hero__object-layer" aria-hidden="true">
            <img src="{{ $image }}" alt="" class="landing-hero__object-img" loading="eager" decoding="async"
                style="left: {{ $objectStyles['left'] }}; top: {{ $objectStyles['top'] }}; width: {{ $objectStyles['width'] }}; height: {{ $objectStyles['height'] }}; opacity: {{ $objectStyles['opacity'] }}; object-fit: {{ $objectStyles['object-fit'] }}; object-position: {{ $objectStyles['object-position'] }};">
        </div>
    @endif

    <div class="landing-hero__inner mx-auto w-full max-w-layout px-5 lg:px-8">
        <div class="landing-hero__grid">
            <div class="landing-hero__content" data-aos="fade-up" data-aos-delay="80">
                @if (filled($content['badge'] ?? null))
                    <p class="landing-hero__badge">{{ $content['badge'] }}</p>
                @endif

                @if ($titleLines !== [])
                    <h1 class="landing-hero__title">
                        @foreach ($titleLines as $line)
                            <span class="landing-hero__title-line">{{ trim($line) }}</span>
                        @endforeach
                    </h1>
                @endif

                @if (filled($content['subtitle'] ?? null))
                    <p class="landing-hero__subtitle">{{ $content['subtitle'] }}</p>
                @endif

                @if (filled($content['cta_primary_label'] ?? null) || filled($content['cta_secondary_label'] ?? null))
                    <div class="landing-hero__actions">
                        @if (filled($content['cta_primary_label'] ?? null))
                            <a href="{{ $primaryHref }}" class="landing-hero__btn landing-hero__btn--primary">
                                {{ $content['cta_primary_label'] }}
                            </a>
                        @endif
                        @if (filled($content['cta_secondary_label'] ?? null))
                            <a href="{{ $secondaryHref }}" class="landing-hero__btn landing-hero__btn--secondary">
                                {{ $content['cta_secondary_label'] }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if (LandingHeroImage::showsMediaColumn($content))
                <div class="landing-hero__media" data-aos="fade-up" data-aos-delay="160">
                    <div class="landing-hero__media-frame">
                        <img src="{{ $image }}" alt="" class="landing-hero__image" loading="eager" decoding="async"
                            style="object-position: {{ $imageFocal->objectPosition() }}">
                        <div class="landing-hero__media-fade" aria-hidden="true"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
