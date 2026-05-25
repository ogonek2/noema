@props([
    'spotlight' => null,
    'content' => [],
])

@php
    $brandTitle = $content['brand_title'] ?? 'NOEMA';
    $quoteFallback = $content['quote_fallback'] ?? 'Преміальні медичні костюми для тих, хто тримає відповідальність за життя — з комфортом, посадкою та деталями, які відчуваються з першої зміни.';
    $madeWith = $content['made_with'] ?? 'Made with Noema';
@endphp

<section id="statement" class="flex w-full items-center justify-center bg-black-brand py-24 text-white-brand lg:min-h-[70vh] lg:py-32"
    data-nav-theme="dark" data-aos="fade-up">
    <div class="mx-auto flex w-full max-w-layout flex-col items-center px-5 text-center lg:px-8">
        <h2 class="mb-10 text-[2rem] font-light uppercase tracking-[0.32em] text-white-brand lg:mb-14 lg:text-[2.75rem]"
            data-aos="fade-up" data-aos-delay="80">
            {{ $brandTitle }}
        </h2>

        <p class="max-w-[800px] text-[0.95rem] italic leading-[1.85] text-gray-text lg:text-[1.05rem]"
            data-aos="fade-up" data-aos-delay="140">
            {{ $spotlight?->short_description ?? $quoteFallback }}
        </p>

        @if ($spotlight)
            <a href="{{ route('product.show', $spotlight) }}"
                class="mt-10 text-[0.68rem] uppercase tracking-[0.22em] text-white-brand/80 transition hover:text-white-brand"
                data-aos="fade-up" data-aos-delay="180">
                {{ $spotlight->name }} →
            </a>
        @endif

        <p class="mt-16 text-[0.72rem] uppercase tracking-[0.32em] text-white-brand/75 lg:mt-24" data-aos="fade-up"
            data-aos-delay="200">
            {{ $madeWith }}
        </p>
    </div>
</section>
