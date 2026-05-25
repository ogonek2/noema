@extends('layouts.store')

@php
    $breadcrumbs = [
        ['label' => 'Головна', 'url' => route('home')],
        ['label' => 'Каталог'],
    ];
    $footerCatalogs = $catalogs;
@endphp

@section('store')
    <header class="mb-12 lg:mb-16" data-aos="fade-up">
        <p class="mb-3 text-[0.72rem] tracking-[0.24em] text-gray-text">[ КАТАЛОГ ]</p>
        <h1 class="text-[2.6rem] font-light uppercase leading-[0.88] tracking-[0.06em] lg:text-[5.5rem]">
            Оберіть<br>напрямок
        </h1>
        <p class="mt-6 max-w-[560px] text-[1rem] leading-relaxed text-black-brand/65">
            Преміальні медичні костюми NOEMA — колекції для жінок, чоловіків та аксесуарів.
        </p>
    </header>

    <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($catalogs as $catalog)
            <a href="{{ route('catalog.show', $catalog) }}"
                class="group block border border-black-brand/10 bg-black-brand/[0.02] transition-colors hover:border-black-brand/25"
                data-aos="fade-up" data-aos-delay="{{ $loop->index * 60 }}">
                <x-ui.media-image
                    :src="$catalog->imageUrl()"
                    :alt="$catalog->name"
                    aspect="4/5"
                    wrapper-class="bg-black-brand"
                    class="opacity-90 transition duration-500 group-hover:scale-[1.02]"
                />
                <div class="space-y-2 p-6 lg:p-8">
                    <p class="text-[0.68rem] tracking-[0.22em] text-gray-text">
                        {{ $catalog->products_count }}
                        @php
                            $n = $catalog->products_count;
                            $goodsLabel = ($n % 10 === 1 && $n % 100 !== 11) ? 'товар' : ((in_array($n % 10, [2, 3, 4], true) && ! in_array($n % 100, [12, 13, 14], true)) ? 'товари' : 'товарів');
                        @endphp
                        {{ $goodsLabel }}
                    </p>
                    <h2 class="text-[1.6rem] uppercase leading-[0.95] tracking-[0.05em]">{{ $catalog->name }}</h2>
                    @if ($catalog->description)
                        <p class="line-clamp-3 text-[0.9rem] leading-relaxed text-black-brand/60">
                            {{ $catalog->description }}
                        </p>
                    @endif
                    <span class="inline-block pt-2 text-[0.68rem] uppercase tracking-[0.18em] text-black-brand">
                        Переглянути →
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endsection
