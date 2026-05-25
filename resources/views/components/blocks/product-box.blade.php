@props([
    'products' => collect(),
    'spotlight' => null,
    'content' => [],
])

@php
    use App\Models\Product;
    use App\Support\HomepageProductBox;
    use App\Support\PriceFormat;

    /** @var \Illuminate\Support\Collection<int, Product> $products */
    $swiperProducts = $products->isNotEmpty() ? $products : collect();
    /** @var Product|null $spotlightProduct */
    $spotlightProduct = $spotlight ?? $swiperProducts->first();
    $box = HomepageProductBox::resolve($content, $spotlightProduct);
@endphp

<section id="product" class="w-full bg-white-brand text-black-brand" data-nav-theme="light" data-aos="fade-up">
    <div class="product-cards-swiper w-full overflow-hidden" data-aos="fade-up" data-aos-delay="100">
        <div class="swiper-wrapper">
            @forelse ($swiperProducts as $product)
                <div class="swiper-slide h-auto">
                    <a href="{{ route('product.show', $product) }}" class="group relative block aspect-[7/10] w-full overflow-hidden bg-black-brand">
                        <x-ui.media-image
                            :src="$product->imageUrl()"
                            :alt="$product->name"
                            wrapper-class="absolute inset-0"
                            class="transition duration-500 group-hover:scale-105"
                        />
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/45 via-black/20 to-black/55"></div>
                        <div class="absolute right-6 top-6 text-[0.95rem] tracking-[0.18em] text-gray-text">[ {{ $product->catalog?->name ?? 'ПРОДУКТ' }} ]</div>
                        <div class="absolute bottom-8 left-8">
                            <p class="mb-2 text-[2.1rem] uppercase leading-[0.9] tracking-[0.06em] text-white-brand">
                                {{ $product->name }}
                            </p>
                            <p class="mb-4 text-[0.78rem] tracking-[0.14em] text-white-brand/75">
                                {{ PriceFormat::usd($product->price) }}
                            </p>
                            <span class="block h-px w-16 bg-white-brand/70"></span>
                        </div>
                    </a>
                </div>
            @empty
                @for ($i = 0; $i < 3; $i++)
                    <div class="swiper-slide h-auto">
                        <article class="relative aspect-[7/10] w-full overflow-hidden bg-black-brand/5">
                            <x-ui.skeleton class="absolute inset-0" />
                        </article>
                    </div>
                @endfor
            @endforelse
        </div>
    </div>

    <div class="mx-auto w-full max-w-layout px-5 py-14 lg:px-8 lg:py-40">
        <div class="mb-12 flex items-center justify-between gap-4" data-aos="fade-up" data-aos-delay="140">
            <h2 class="text-[2.9rem] font-light uppercase leading-[0.85] tracking-[0.06em] lg:text-[7.4rem]">{{ $box['title'] }}</h2>
            <a href="{{ $box['catalog_href'] }}" class="text-[0.9rem] tracking-[0.22em] text-gray-text transition hover:text-black-brand">{{ $box['catalog_label'] }}</a>
        </div>

        <div class="mb-10 max-w-[450px] space-y-4" data-aos="fade-up" data-aos-delay="180">
            <p class="text-[1.8rem] leading-tight tracking-[0.02em] text-black-brand/90">
                {{ $box['headline'] }}
            </p>
            <p class="text-[0.76rem] uppercase tracking-[0.14em] text-black-brand/55">
                {{ $box['subtitle'] }}
            </p>
        </div>

        @if ($box['fabric_tags']->isNotEmpty())
            <div class="mb-10 flex flex-wrap gap-[1px] p-[1px]" data-aos="fade-up" data-aos-delay="220">
                @foreach ($box['fabric_tags'] as $tag)
                    <div class="bg-black-brand px-4 py-5 text-center text-[1rem] uppercase tracking-[0.04em] text-white-brand lg:text-[1.8rem]">
                        {{ \Illuminate\Support\Str::limit($tag, 28) }}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-2 lg:gap-10" data-aos="fade-up" data-aos-delay="260">
            <article class="space-y-5">
                <p class="text-[1.05rem] leading-relaxed text-black-brand/65">
                    {{ $box['column_left_text'] }}
                </p>
                <div class="h-px w-full bg-black-brand/20"></div>
                <p class="text-[0.78rem] font-medium uppercase tracking-[0.07em] text-black-brand/75">
                    {{ $box['column_left_caption'] }}
                </p>
            </article>
            <article class="space-y-5">
                <p class="text-[1.05rem] leading-relaxed text-black-brand/65">
                    {{ $box['column_right_text'] }}
                </p>
                <div class="h-px w-full bg-black-brand/20"></div>
                <p class="text-[0.78rem] font-medium uppercase tracking-[0.07em] text-black-brand/75">
                    {{ $box['column_right_caption'] }}
                </p>
            </article>
        </div>

        <div class="mt-12 flex flex-wrap items-center justify-between gap-6" data-aos="fade-up" data-aos-delay="300">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ $box['cta_primary_href'] }}"
                    class="min-w-[210px] cursor-pointer border border-black-brand bg-black-brand px-8 py-4 text-center text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition-colors duration-300 hover:bg-white-brand hover:text-black-brand">
                    {{ $box['cta_primary_label'] }}
                </a>
                <a href="{{ $box['cta_secondary_href'] }}"
                    class="min-w-[210px] cursor-pointer border border-black-brand/10 bg-black-brand/5 px-8 py-4 text-center text-xs font-medium uppercase tracking-[0.16em] text-black-brand transition-colors duration-300 hover:bg-black-brand hover:text-white-brand">
                    {{ $box['cta_secondary_label'] }}
                </a>
            </div>
            <p class="text-[0.8rem] uppercase tracking-[0.28em] text-black-brand/35">{{ $box['made_with'] }}</p>
        </div>
    </div>
</section>
