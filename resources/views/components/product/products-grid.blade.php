@props([
    'title',
    'products',
    'catalogUrl' => null,
    'catalogLabel' => 'Каталог →',
])

@if ($products->isNotEmpty())
    <section class="mt-16 border-t border-black-brand/10 pt-12 lg:mt-20" data-aos="fade-up">
        <div class="mb-8 flex items-end justify-between gap-4">
            <h2 class="text-[1.5rem] uppercase tracking-[0.06em] lg:text-[2rem]">{{ $title }}</h2>
            @if ($catalogUrl)
                <a href="{{ $catalogUrl }}"
                    class="text-[0.68rem] uppercase tracking-[0.16em] text-black-brand/45 hover:text-black-brand">
                    {{ $catalogLabel }}
                </a>
            @endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 lg:gap-6">
            @foreach ($products as $relatedProduct)
                <x-ui.product-card :product="$relatedProduct" />
            @endforeach
        </div>
    </section>
@endif
