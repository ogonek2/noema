@props([
    'variants',
    'currentSlug',
    'modelName',
    'embedded' => false,
])

@if ($variants->count() > 1)
    <section id="product-model-colors"
        @class([
            'min-w-0 max-w-full overflow-hidden',
            'border-t border-black-brand/10 pt-10 mt-12 lg:mt-14' => ! $embedded,
            'product-page-extra-inner h-full' => $embedded,
        ])
        data-aos="{{ $embedded ? '' : 'fade-up' }}">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/45">Кольори моделі</p>
                <h2 @class([
                    'mt-1 uppercase tracking-[0.06em]',
                    'text-[1.25rem] lg:text-[1.5rem]' => ! $embedded,
                    'text-[1.1rem] lg:text-[1.25rem]' => $embedded,
                ])>
                    {{ $modelName }}
                </h2>
            </div>
            <p class="text-[0.68rem] tracking-[0.14em] text-black-brand/45">
                {{ $variants->count() }} {{ $variants->count() === 1 ? 'колір' : ($variants->count() < 5 ? 'кольори' : 'кольорів') }}
            </p>
        </div>

        <div id="product-model-colors-track"
            class="product-model-colors-track flex w-full min-w-0 max-w-full touch-pan-x gap-3 overflow-x-auto overscroll-x-contain pb-2 scroll-smooth [-webkit-overflow-scrolling:touch]">
            @foreach ($variants as $variant)
                @php
                    $isCurrent = $variant->slug === $currentSlug;
                    $thumb = $variant->imageUrl();
                @endphp
                <button type="button"
                    class="product-model-color-card product-color-btn group shrink-0 w-[7rem] text-left transition sm:w-[8rem] {{ $isCurrent ? 'is-active' : '' }}"
                    data-product-slug="{{ $variant->slug }}"
                    aria-pressed="{{ $isCurrent ? 'true' : 'false' }}"
                    aria-label="{{ $variant->color_name }}">
                    <span class="relative block aspect-[3/4] overflow-hidden bg-black-brand/5 ring-1 ring-black-brand/10 transition group-hover:ring-black-brand/30 {{ $isCurrent ? 'ring-2 ring-black-brand' : '' }}">
                        @if ($thumb)
                            <img src="{{ $thumb }}" alt="{{ $variant->color_name }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                loading="lazy"
                                decoding="async">
                        @elseif ($variant->color_hex)
                            <span class="absolute inset-0" style="background-color: {{ $variant->color_hex }}"></span>
                        @endif
                        @if ($isCurrent)
                            <span class="product-model-color-badge absolute inset-x-0 bottom-0 bg-black-brand py-1.5 text-center text-[0.58rem] uppercase tracking-[0.16em] text-white-brand">
                                Обрано
                            </span>
                        @endif
                    </span>
                    <span class="mt-2 flex items-center gap-2">
                        @if ($variant->color_hex)
                            <span class="h-3 w-3 shrink-0 rounded-full border border-black-brand/15"
                                style="background-color: {{ $variant->color_hex }}"></span>
                        @endif
                        <span class="text-[0.65rem] uppercase tracking-[0.14em] text-black-brand/75 group-hover:text-black-brand">
                            {{ $variant->color_name }}
                        </span>
                    </span>
                </button>
            @endforeach
        </div>
    </section>
@endif
