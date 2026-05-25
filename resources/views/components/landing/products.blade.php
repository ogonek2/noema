@props(['content' => []])

@php
    use App\Models\Product;

    $navTheme = $content['nav_theme'] ?? 'light';

    $productIds = collect($content['product_ids'] ?? [])
        ->filter(fn (mixed $id): bool => filled($id))
        ->map(fn (mixed $id): int => (int) $id)
        ->unique()
        ->values();

    $products = $productIds->isEmpty()
        ? collect()
        : Product::query()
            ->active()
            ->whereIn('id', $productIds)
            ->get()
            ->sortBy(fn (Product $product): int => $productIds->search($product->id))
            ->values();
@endphp

<section class="w-full {{ $navTheme === 'dark' ? 'bg-black-brand text-white-brand' : 'bg-white-brand text-black-brand' }}"
    data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-16 lg:px-8 lg:py-24">
        @if (filled($content['title'] ?? null))
            <h2 class="text-center text-[2rem] font-thin uppercase tracking-[0.08em] lg:text-[3rem]">{{ $content['title'] }}</h2>
        @endif
        @if (filled($content['subtitle'] ?? null))
            <p class="mx-auto mt-4 max-w-2xl text-center text-[1rem] leading-relaxed opacity-70">{{ $content['subtitle'] }}</p>
        @endif

        @if ($products->isNotEmpty())
            <div class="mt-12 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                @foreach ($products as $product)
                    <x-ui.product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>
</section>
