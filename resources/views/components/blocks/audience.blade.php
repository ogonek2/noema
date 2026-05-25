@props([
    'cards' => collect(),
])

@php
    $fallbackCards = collect([
        ['name' => 'Хірурги', 'image' => asset('storage/images/audience/a1.png'), 'href' => route('catalog.index')],
        ['name' => 'Стоматологи', 'image' => asset('storage/images/audience/a2.png'), 'href' => route('catalog.index')],
        ['name' => 'Косметологи', 'image' => asset('storage/images/audience/a3.png'), 'href' => route('catalog.index')],
    ]);
    $items = $cards->isNotEmpty() ? $cards : $fallbackCards;
@endphp

<section id="audience" class="relative w-full overflow-hidden bg-white-brand py-16 text-black-brand lg:py-24"
    data-nav-theme="light" data-aos="fade-up">
    <div class="relative z-10 mx-auto w-full max-w-layout px-5 lg:px-8">
        <div class="mb-10 flex items-center justify-between gap-4">
            <h2 class="text-[2.85rem] font-light uppercase leading-[0.86] tracking-[0.06em] lg:text-[7.2rem]">
                Для<br>кого
            </h2>
            <p class="pt-2 text-[0.9rem] tracking-[0.24em] text-gray-text">[ NOEMA ]</p>
        </div>

        <div class="audience-cards-swiper w-full overflow-hidden py-6">
            <div class="swiper-wrapper">
                @foreach ($items as $card)
                    <div class="swiper-slide audience-card !h-auto">
                        <a href="{{ $card['href'] ?? route('catalog.index') }}"
                            class="audience-card-inner group relative block aspect-[4/6] w-full overflow-hidden bg-gradient-to-br from-[#6d6d6d] via-[#8b8b8b] to-[#5b5b5b] opacity-0 translate-y-6 scale-[0.985] will-change-[opacity,transform] transition-[opacity,transform] duration-[1400ms] ease-[cubic-bezier(0.19,1,0.22,1)]">
                            <x-ui.media-image
                                :src="$card['image']"
                                :alt="$card['name']"
                                wrapper-class="absolute inset-0"
                                class="relative z-10 object-contain p-1 grayscale transition duration-500 group-hover:scale-105 group-hover:grayscale-0"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-black/45"></div>
                            <p class="absolute z-10 bottom-6 left-6 text-[1.5rem] uppercase tracking-[0.08em] text-white-brand">
                                {{ $card['name'] }}
                            </p>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <img src="{{ asset('storage/logo/BLACK_NOEMA.svg') }}" alt="" class="pointer-events-none absolute -bottom-12 left-1/2 z-0 -translate-x-1/2 lg:-bottom-20 opacity-10" loading="lazy">
</section>
