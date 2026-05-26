@extends('layouts.store')

@php
    $singleCatalogMode = $singleCatalogMode ?? false;
    $breadcrumbs = $singleCatalogMode
        ? [
            ['label' => 'Головна', 'url' => route('home')],
            ['label' => $catalog->name],
        ]
        : [
            ['label' => 'Головна', 'url' => route('home')],
            ['label' => 'Каталог', 'url' => route('catalog.index')],
            ['label' => $catalog->name],
        ];
    $footerCatalogs = app(\App\Services\StorefrontService::class)->activeCatalogs();
@endphp

@section('store')
    <header class="mb-10 flex flex-col gap-6 lg:mb-14 lg:flex-row lg:items-end lg:justify-between" data-aos="fade-up">
        <div>
            <p class="mb-3 text-[0.72rem] tracking-[0.24em] text-gray-text">[ {{ $catalog->name }} ]</p>
            <h1 class="text-[2.4rem] font-light uppercase leading-[0.9] tracking-[0.06em] lg:text-[4.8rem]">
                {{ $catalog->name }}
            </h1>
            @if ($catalog->description)
                <p class="mt-5 max-w-[640px] text-[1rem] leading-relaxed text-black-brand/65">{{ $catalog->description }}</p>
            @endif
        </div>
        @unless ($singleCatalogMode)
            <a href="{{ route('catalog.index') }}"
                class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/50 transition hover:text-black-brand">
                ← Усі каталоги
            </a>
        @endunless
    </header>

    @if ($products->isEmpty())
        <div class="border border-dashed border-black-brand/20 px-8 py-16 text-center" data-aos="fade-up">
            <p class="text-[0.82rem] uppercase tracking-[0.18em] text-black-brand/50">У каталозі поки немає товарів</p>
        </div>
    @else
        <div class="grid gap-[1px] sm:grid-cols-2 lg:grid-cols-3" data-aos="fade-up" data-aos-delay="120">
            @foreach ($products as $product)
                <x-ui.product-card :product="$product" class="min-h-[420px]" />
            @endforeach
        </div>

        <div class="mt-12">
            {{ $products->links('pagination.simple') }}
        </div>
    @endif
@endsection
