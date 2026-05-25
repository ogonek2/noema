@props([
    'items' => collect(),
])

@php
    $reviews = $items instanceof \Illuminate\Support\Collection ? $items->all() : (array) $items;
    if (empty($reviews)) {
        $reviews = [
            [
                'quote' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
                'name' => 'Андрій К.',
                'role' => 'Стоматолог',
            ],
        ];
    }
@endphp

<section id="reviews" class="w-full bg-black-brand py-16 text-white-brand lg:py-24" data-nav-theme="dark"
    data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 lg:px-8">
        <div class="mb-10 flex items-center justify-between gap-4" data-aos="fade-up">
            <h2 class="text-[2.85rem] font-thin uppercase leading-[0.86] tracking-[0.06em] lg:text-[7.2rem]">
                Відгуки
            </h2>
            <p class="pt-2 text-[0.9rem] tracking-[0.24em] text-gray-text">[ NOEMA ]</p>
        </div>

        <div class="reviews-swiper swiper w-full overflow-visible" data-aos="fade-up" data-aos-delay="120">
            <div class="swiper-wrapper">
                @foreach ($reviews as $review)
                    <div class="swiper-slide !h-auto">
                        <article
                            class="flex h-full min-h-[280px] flex-col justify-between rounded-[10px] border border-white-brand/10 bg-black-brand-2 p-6 lg:min-h-[300px] lg:p-8">
                            <p class="text-[0.82rem] leading-relaxed text-gray-text lg:text-[0.9rem]">
                                {{ $review['quote'] }}
                            </p>
                            <div class="mt-8 space-y-1">
                                <p class="text-[0.95rem] font-bold tracking-[0.02em] text-white-brand">
                                    {{ $review['name'] }}
                                </p>
                                <p class="text-[0.78rem] text-gray-text">{{ $review['role'] }}</p>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-10 flex items-center justify-between gap-6" data-aos="fade-up" data-aos-delay="180">
            <div class="flex items-center gap-3">
                <button type="button" aria-label="Попередній відгук"
                    class="reviews-swiper-prev flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-full border border-white-brand/35 text-white-brand transition-colors duration-300 hover:border-white-brand [&.swiper-button-disabled]:cursor-not-allowed [&.swiper-button-disabled]:opacity-35">
                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M10 3L5 8L10 13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
                <button type="button" aria-label="Наступний відгук"
                    class="reviews-swiper-next flex h-11 min-w-[72px] shrink-0 cursor-pointer items-center justify-center rounded-full bg-white-brand px-5 text-black-brand transition-opacity duration-300 hover:opacity-85 [&.swiper-button-disabled]:cursor-not-allowed [&.swiper-button-disabled]:opacity-35">
                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div class="reviews-pagination flex h-px w-full max-w-[220px] items-center gap-2 lg:max-w-[280px]"
                aria-hidden="true"></div>
        </div>
    </div>
</section>
