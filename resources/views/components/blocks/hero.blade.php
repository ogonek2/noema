@props([
    'content' => [],
])

@php
    use App\Support\MediaUrl;

    $tagline = $content['tagline'] ?? 'преміальний бренд одягу для лікарів';
    $heroImage = MediaUrl::resolve($content['hero_image'] ?? null, 'images/women.png');
    $sideLinkLabel = $content['side_link_label'] ?? "МЕДИЧНИЙ\nОДЯГ";
    $sideLinkHref = $content['side_link_href'] ?? route('catalog.index');
    $footerTagline = $content['footer_tagline'] ?? 'ПРЕМІУМ КОСТЮМИ ДЛЯ МЕДИКІВ';
    $scrollHint = $content['scroll_hint'] ?? 'ВНИЗ';
    $instagram = $content['instagram_url'] ?? 'https://www.instagram.com/noema.ua/';
    $facebook = $content['facebook_url'] ?? 'https://www.facebook.com/noema.ua/';
    $tiktok = $content['tiktok_url'] ?? 'https://www.tiktok.com/@noema.ua';
@endphp

<section id="hero" class="relative w-full overflow-hidden bg-black-brand" data-nav-theme="dark" data-aos="fade-up">
    <div class="pointer-events-none absolute inset-0 right-[-40%] z-10 mx-auto w-full max-w-[600px]" data-aos="zoom-in"
        data-aos-delay="120">
        <img src="{{ $heroImage }}" alt="NOEMA" class="h-auto w-full  object-cover opacity-75 ">
    </div>
    <div class="relative z-20 mx-auto flex w-full max-w-layout flex-col justify-between py-12">
        <div
            class="relative lg:h-screen lg:max-h-[807px] z-10 mx-auto grid w-full grid-cols-1 items-center px-5 py-56 lg:px-8 lg:py-36">
            <div class="animate-fade-soft lg:text-center [animation-delay:140ms]" data-aos="fade-up" data-aos-delay="180">
                <img src="{{ asset('storage/logo/WHITE_NOEMA.svg') }}" alt="NOEMA"
                    class="w-full hidden lg:block max-w-[500px] mx-auto">
                <h1
                    class="mt-3 text-[2.25rem] font-thin text-white-brand lg:text-white/70 lg:tracking-[0.16em] lg:text-[0.82rem] lg:text-center uppercase">
                    {{ $tagline }}
                </h1>
            </div>
        </div>

        <div class="z-30 py-8 lg:absolute inset-0 pointer-events-none">
            <div class="mx-auto h-full w-full px-5 lg:px-8">
                <a href="{{ $sideLinkHref }}"
                    class="absolute pointer-events-auto cursor-pointer left-5 top-1/2 flex -translate-y-1/2 items-start gap-2 text-[0.68rem] tracking-[0.16em] text-gray-text lg:right-8">
                    <span class="mt-[1px] text-sm">→</span>
                    <p class="leading-tight whitespace-pre-line">{{ $sideLinkLabel }}</p>
                </a>
            </div>
        </div>

        <div class="relative z-30 h-full px-4 py-12 lg:px-8" style="align-content: end;" data-aos="fade-up"
            data-aos-delay="260">
            <ul class="flex items-center gap-2 py-4">
                <li class="text-md cursor-pointer opacity-75 hover:opacity-100 transition-all duration-300">
                    <a href="{{ $instagram }}"
                        class="rounded-lg border border-gray-text w-10 h-10 flex justify-center items-center text-center"
                        target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
                </li>
                <li class="text-md cursor-pointer opacity-75 hover:opacity-100 transition-all duration-300">
                    <a href="{{ $facebook }}"
                        class="rounded-lg border border-gray-text w-10 h-10 flex justify-center items-center text-center"
                        target="_blank" rel="noopener"><i class="fa-brands fa-facebook"></i></a>
                </li>
                <li class="text-md cursor-pointer opacity-75 hover:opacity-100 transition-all duration-300">
                    <a href="{{ $tiktok }}"
                        class="rounded-lg border border-gray-text w-10 h-10 flex justify-center items-center text-center"
                        target="_blank" rel="noopener"><i class="fa-brands fa-tiktok"></i></a>
                </li>
            </ul>
            <p class="mx-auto w-full text-[0.62rem] tracking-[0.22em] text-gray-text">
                {{ $footerTagline }}
            </p>
        </div>

        <div
            class="pointer-events-none relative z-30 lg:absolute inset-x-0 bottom-8 flex flex-col items-center gap-2 justify-center">
            <p
                class="animate-fade-soft text-center text-[0.66rem] tracking-[0.22em] text-gray-text [animation-delay:320ms]">
                {{ $scrollHint }}
            </p>
            <div
                class="animate-fade-soft w-[2px] h-[22px] bg-gray-text text-center text-[0.66rem] tracking-[0.22em] text-gray-text [animation-delay:320ms]">
            </div>
        </div>
    </div>
</section>
