@props([
    'images' => [],
    'alt' => '',
])

@php
    $urls = collect($images)->filter()->values();
    if ($urls->isEmpty()) {
        $urls = collect([\App\Support\MediaUrl::resolve(null)]);
    }
@endphp

<div class="product-gallery-viewer min-w-0 max-w-full" data-product-gallery data-gallery-images='@json($urls)' data-gallery-alt="{{ $alt }}">
    <div class="product-gallery-layout flex min-w-0 max-w-full flex-col gap-3 lg:flex-row lg:gap-4">
        @if ($urls->count() > 1)
            <div class="product-gallery-thumbs order-2 flex w-full min-w-0 max-w-full touch-pan-x gap-2 overflow-x-auto overscroll-x-contain pb-1 [-webkit-overflow-scrolling:touch] lg:order-1 lg:max-h-[min(72vh,720px)] lg:w-20 lg:flex-col lg:overflow-x-hidden lg:overflow-y-auto lg:pb-0"
                id="product-gallery-thumbs" role="tablist" aria-label="Мініатюри">
                @foreach ($urls as $index => $url)
                    <button type="button"
                        class="product-gallery-thumb relative shrink-0 overflow-hidden border bg-black-brand/5 transition {{ $index === 0 ? 'is-active border-black-brand' : 'border-black-brand/10 hover:border-black-brand/40' }}"
                        style="aspect-ratio: 1; width: 4.5rem;"
                        data-gallery-index="{{ $index }}"
                        role="tab"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                        aria-label="Фото {{ $index + 1 }}">
                        <img src="{{ $url }}" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
                    </button>
                @endforeach
            </div>
        @endif

        <div class="product-gallery-stage-wrap relative order-1 min-w-0 max-w-full flex-1 overflow-hidden lg:order-2" id="product-gallery-stage-wrap">
            <div class="product-gallery-stage relative aspect-[4/5] w-full max-w-full cursor-crosshair overflow-hidden bg-black-brand"
                data-gallery-stage tabindex="0" role="button"
                aria-label="Відкрити фото на весь екран">
                <img src="{{ $urls->first() }}" alt="{{ $alt }}"
                    class="product-gallery-main-image h-full w-full object-cover transition-transform duration-150 ease-out will-change-transform"
                    data-gallery-main decoding="async" fetchpriority="high">
                <div class="product-gallery-zoom-hint pointer-events-none absolute right-3 top-3 z-10 hidden items-center gap-1.5 bg-black-brand/55 px-2.5 py-1 text-[0.58rem] uppercase tracking-[0.16em] text-white-brand backdrop-blur-sm lg:flex"
                    aria-hidden="true">
                    <span>+</span> Наведіть для зуму
                </div>
                <button type="button"
                    class="product-gallery-fullscreen-btn absolute bottom-3 right-3 z-10 flex h-10 w-10 items-center justify-center border border-white-brand/30 bg-black-brand/50 text-white-brand backdrop-blur-sm transition hover:bg-black-brand"
                    data-gallery-open aria-label="На весь екран">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M8 3H3v5M16 3h5v5M16 21h5v-5M8 21H3v-5" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
