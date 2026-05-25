@props([
    'product',
    'aspect' => '7/10',
    'showMeta' => true,
])

@php
    /** @var \App\Models\Product $product */
    $href = filled($product->slug ?? null)
        ? route('product.show', $product)
        : route('catalog.index');
    $images = collect($product->galleryUrls())->filter()->values();
    if ($images->isEmpty()) {
        $images = collect([$product->imageUrl()]);
    }
@endphp

<article {{ $attributes->merge(['class' => 'product-card group relative aspect-[7/10] w-full overflow-hidden bg-black-brand']) }}
    data-catalog-card
    data-product-slug="{{ $product->slug }}"
    data-images='@json($images)'>
    <a href="{{ $href }}" class="product-card-link relative block h-full w-full">
        <div class="absolute inset-0">
            @foreach ($images as $layerIndex => $imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                    data-card-image-layer
                    class="absolute inset-0 h-full w-full object-cover transition-opacity duration-700 ease-out {{ $layerIndex === 0 ? 'z-[1] opacity-100' : 'z-0 opacity-0' }}"
                    loading="{{ $layerIndex === 0 ? 'eager' : 'lazy' }}"
                    decoding="async"
                    @if ($layerIndex > 0) fetchpriority="low" @endif>
            @endforeach
        </div>
        @if ($images->count() > 1)
            <div class="pointer-events-none absolute left-3 top-3 z-[2] flex gap-1" aria-hidden="true">
                @foreach ($images as $dotIndex => $_)
                    <span class="catalog-card-dot h-1 w-1 bg-white-brand/40 transition {{ $dotIndex === 0 ? 'bg-white-brand w-3' : '' }}"></span>
                @endforeach
            </div>
        @endif
        <div class="pointer-events-none absolute inset-0 z-[2] bg-gradient-to-t from-black/65 via-black/20 to-black/35 transition duration-500 group-hover:from-black/75"></div>
        @if ($showMeta)
            <div class="pointer-events-none absolute inset-x-0 bottom-0 z-[2] p-6 transition duration-500 group-hover:translate-y-[-3.25rem] lg:p-8">
                @if ($product->catalog)
                    <p class="mb-2 text-[0.68rem] tracking-[0.2em] text-gray-text">{{ $product->catalog->name }}</p>
                @endif
                <h3 class="text-[1.35rem] uppercase leading-[0.95] tracking-[0.05em] text-white-brand lg:text-[1.6rem]">
                    {{ $product->name }}
                </h3>
                @if ($product->subtitle)
                    <p class="mt-2 text-[0.72rem] tracking-[0.12em] text-white-brand/70">{{ $product->subtitle }}</p>
                @endif
                @if ($product->color_name)
                    <p class="mt-1 text-[0.65rem] tracking-[0.14em] text-white-brand/55">{{ $product->color_name }}</p>
                @endif
                <p class="mt-3 text-[0.82rem] tracking-[0.14em] text-white-brand">
                    {{ \App\Support\PriceFormat::usd($product->price) }}
                </p>
            </div>
        @endif
    </a>

    <button type="button"
        data-cart-open
        data-product-slug="{{ $product->slug }}"
        class="product-card-add absolute inset-x-0 bottom-0 z-[3] flex translate-y-full items-center justify-center gap-3 border-t border-white-brand/20 bg-black-brand/90 px-4 py-3.5 text-[0.62rem] font-medium uppercase tracking-[0.2em] text-white-brand backdrop-blur-md transition duration-500 group-hover:translate-y-0 focus-visible:translate-y-0"
        aria-label="Додати {{ $product->name }} в кошик">
        <span class="flex h-7 w-7 items-center justify-center rounded-full border border-white-brand/35 text-sm leading-none">+</span>
        <span>Додати в кошик</span>
    </button>
</article>

