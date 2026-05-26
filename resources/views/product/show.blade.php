@extends('layouts.store')

@php
    use App\Enums\ProductRelationType;
    use App\Support\PriceFormat;
    use App\Support\RichContent;

    $breadcrumbs = [
        ['label' => 'Головна', 'url' => route('home')],
        ['label' => 'Каталог', 'url' => route('catalog.index')],
        ['label' => $product->catalog->name, 'url' => route('catalog.show', $product->catalog)],
        ['label' => $product->name],
    ];
    $footerCatalogs = app(\App\Services\StorefrontService::class)->activeCatalogs();
    $galleryUrls = $product->galleryUrls();
    $dataUrlTemplate = url('/product/__SLUG__/data');
    $hasSizeChart = $product->sizeChartRows->isNotEmpty();
    $hasModelColors = $modelColorVariants->count() > 1;
    $showProductExtras = $hasSizeChart || $hasModelColors;
@endphp

@section('store')
    <div id="product-page" class="product-page relative min-w-0 max-w-full transition-opacity duration-300"
        data-current-slug="{{ $product->slug }}" data-data-url-template="{{ $dataUrlTemplate }}"
        data-initial-payload='@json($initialPayload)'>
        <div
            class="product-page-loader pointer-events-none absolute inset-0 z-30 hidden items-center justify-center bg-white-brand/60 opacity-0 transition-opacity duration-300 [[.is-loading]_&]:flex [[.is-loading]_&]:opacity-100">
            <div class="h-12 w-12 animate-pulse rounded-full border border-black-brand/20 bg-black-brand/5"></div>
        </div>

        <div class="product-page-hero grid min-w-0 items-start gap-10 lg:grid-cols-2 lg:gap-14 xl:gap-16" data-aos="fade-up">
            <div id="product-gallery-root" class="product-page-gallery min-w-0 max-w-full">
                <x-product.gallery :images="$galleryUrls" :alt="$product->name" />
            </div>

            <div class="product-page-buy min-w-0 lg:pt-1" id="product-info-panel">
                <p class="mb-2 text-[0.68rem] tracking-[0.22em] text-gray-text">
                    <a href="{{ route('catalog.show', $product->catalog) }}" class="hover:text-black-brand">
                        {{ $product->catalog->name }}
                    </a>
                </p>

                <h1 id="product-title"
                    class="text-[2rem] font-light uppercase leading-[0.92] tracking-[0.05em] lg:text-[2.8rem]">
                    {{ $product->name }}
                </h1>
                <p id="product-subtitle"
                    class="mt-3 text-[0.82rem] tracking-[0.14em] text-black-brand/55 {{ $product->subtitle ? '' : 'hidden' }}">
                    {{ $product->subtitle }}
                </p>

                <div class="mt-6 flex flex-wrap items-baseline justify-start gap-3" id="product-price-wrap">
                    <p class="w-fit whitespace-nowrap text-[1.35rem] tracking-[0.08em] tabular-nums" id="product-price">
                        {{ PriceFormat::uah($product->price) }}
                    </p>
                    <p class="text-[0.95rem] tracking-[0.08em] text-black-brand/35 line-through {{ $product->compare_at_price ? '' : 'hidden' }}"
                        id="product-compare-price">
                        {{ $product->compare_at_price ? PriceFormat::uah($product->compare_at_price) : '' }}
                    </p>
                </div>

                <p id="product-short-description"
                    class="mt-6 max-w-[520px] text-[1rem] leading-relaxed text-black-brand/70">
                    {{ $product->short_description }}
                </p>

                <div class="mt-8 space-y-6" id="product-variant-picker" data-base-price="{{ (float) $product->price }}"
                    data-variants='@json($initialPayload['variants'] ?? [])'>
                    @if ($colorAlternatives->count() > 1)
                        <div>
                            <p class="mb-3 text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55">Колір</p>
                            <div class="flex flex-wrap gap-2" id="product-color-swatches">
                                @foreach ($colorAlternatives as $alternative)
                                    <button type="button"
                                        class="product-color-btn flex items-center gap-2 border px-3 py-2 text-[0.68rem] uppercase tracking-[0.14em] transition {{ $alternative->slug === $product->slug ? 'border-black-brand bg-black-brand text-white-brand' : 'border-black-brand/15 text-black-brand hover:border-black-brand/40' }}"
                                        data-product-slug="{{ $alternative->slug }}"
                                        aria-pressed="{{ $alternative->slug === $product->slug ? 'true' : 'false' }}">
                                        @if ($alternative->color_hex)
                                            <span class="h-4 w-4 rounded-full border border-black-brand/15"
                                                style="background-color: {{ $alternative->color_hex }}"></span>
                                        @endif
                                        {{ $alternative->color_name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($sizes->isNotEmpty())
                        <div>
                            <p class="mb-3 text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/55">Розмір</p>
                            <div class="flex flex-wrap gap-2" id="product-sizes">
                                @foreach ($sizes as $index => $size)
                                    <button type="button"
                                        class="product-size-btn min-w-[3rem] border px-3 py-2.5 text-[0.68rem] uppercase tracking-[0.14em] transition {{ $index === 0 ? 'border-black-brand bg-black-brand text-white-brand' : 'border-black-brand/15 hover:border-black-brand/40' }}"
                                        data-size="{{ $size }}"
                                        aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">
                                        {{ $size }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <p class="text-[0.72rem] tracking-[0.12em] text-black-brand/45" id="product-variant-sku">
                        SKU: {{ $product->variants->first()?->sku ?? $product->sku }}
                    </p>
                </div>

                <div class="mt-8 flex flex-wrap gap-3 sm:mt-10">
                    <button type="button" id="product-add-to-cart" data-cart-open data-product-slug="{{ $product->slug }}"
                        class="min-w-[200px] cursor-pointer border border-black-brand bg-black-brand px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition hover:bg-white-brand hover:text-black-brand">
                        Додати в кошик
                    </button>
                    <a href="{{ route('catalog.show', $product->catalog) }}"
                        class="min-w-[200px] border border-black-brand/15 px-8 py-4 text-center text-xs font-medium uppercase tracking-[0.16em] transition hover:border-black-brand">
                        До каталогу
                    </a>
                </div>

                @if ($showProductExtras)
                    <div class="product-page-extras mt-10 flex min-w-0 flex-col gap-10 border-t border-black-brand/10 pt-10 lg:mt-12 lg:flex-row lg:gap-12 xl:gap-16"
                        data-aos="fade-up">
                        @if ($hasSizeChart)
                            <section id="product-size-chart-section"
                                class="product-page-extra product-page-extra--chart min-w-0 w-full">
                                <h2 class="mb-3 text-[1.1rem] uppercase tracking-[0.06em] lg:text-[1.25rem]">Розмірна сітка
                                </h2>
                                <p id="product-size-chart-intro"
                                    class="mb-5 max-w-[520px] text-[0.88rem] leading-relaxed text-black-brand/60 {{ $product->size_chart_intro ? '' : 'hidden' }}">
                                    {{ $product->size_chart_intro }}
                                </p>
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[480px] border-collapse text-left text-[0.82rem]">
                                        <thead>
                                            <tr
                                                class="border-b border-black-brand/15 text-[0.68rem] uppercase tracking-[0.16em] text-black-brand/50">
                                                <th class="py-3 pr-4">Розмір</th>
                                                <th class="py-3 pr-4">Груди</th>
                                                <th class="py-3 pr-4">Талія</th>
                                                <th class="py-3 pr-4">Стегна</th>
                                                <th class="py-3">Шов</th>
                                            </tr>
                                        </thead>
                                        <tbody id="product-size-chart-body">
                                            @foreach ($product->sizeChartRows as $row)
                                                <tr class="border-b border-black-brand/8">
                                                    <td class="py-3 pr-4 font-medium">{{ $row->size_label }}</td>
                                                    <td class="py-3 pr-4 text-black-brand/70">{{ $row->bust ?? '—' }}</td>
                                                    <td class="py-3 pr-4 text-black-brand/70">{{ $row->waist ?? '—' }}</td>
                                                    <td class="py-3 pr-4 text-black-brand/70">{{ $row->hip ?? '—' }}</td>
                                                    <td class="py-3 text-black-brand/70">{{ $row->inseam ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif
                    </div>
                @else
                    <div class="product-page-extras hidden" aria-hidden="true">
                        <section id="product-size-chart-section"
                            class="product-page-extra product-page-extra--chart hidden">
                            <p id="product-size-chart-intro" class="hidden"></p>
                            <table class="hidden">
                                <tbody id="product-size-chart-body"></tbody>
                            </table>
                        </section>
                    </div>
                @endif
                @if ($hasModelColors)
                    <div
                        class="product-page-extra product-page-extra--colors mt-10 min-w-0">
                        <x-product.color-gallery :variants="$modelColorVariants" :current-slug="$product->slug" :model-name="$modelName" embedded />
                    </div>
                @endif
            </div>
        </div>


        <div class="mt-14 pt-12 lg:mt-16" data-aos="fade-up">
            <div class="product-tabs mb-8 flex flex-wrap gap-6 border-b border-black-brand/10 pb-4">
                @foreach (['description' => 'Опис', 'fit' => 'Посадка', 'fabric' => 'Тканина', 'care' => 'Догляд'] as $tabId => $tabLabel)
                    <button type="button"
                        class="product-tab-btn text-[0.68rem] uppercase tracking-[0.18em] transition {{ $loop->first ? 'text-black-brand' : 'text-black-brand/40 hover:text-black-brand/70' }}"
                        data-tab="{{ $tabId }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $tabLabel }}
                    </button>
                @endforeach
            </div>

            <div class="product-tab-panels max-w-[820px] space-y-8 text-[0.98rem] leading-relaxed text-black-brand/70">
                <div class="product-tab-panel product-rich-content" data-panel="description" id="product-panel-description">
                    {!! RichContent::render($product->description, $product->short_description) !!}
                </div>
                <div class="product-tab-panel hidden" data-panel="fit" id="product-panel-fit-wrap">
                    <p id="product-fit-summary"
                        class="mb-4 font-medium uppercase tracking-[0.12em] text-black-brand {{ $product->fit_summary ? '' : 'hidden' }}">
                        {{ $product->fit_summary }}
                    </p>
                    <div id="product-panel-fit" class="product-rich-content">{!! RichContent::render($product->fit_details) !!}</div>
                </div>
                <div class="product-tab-panel hidden" data-panel="fabric" id="product-panel-fabric-wrap">
                    <p id="product-fabric-summary"
                        class="mb-4 font-medium uppercase tracking-[0.12em] text-black-brand {{ $product->fabric_summary ? '' : 'hidden' }}">
                        {{ $product->fabric_summary }}
                    </p>
                    <div id="product-panel-fabric" class="product-rich-content">{!! RichContent::render($product->fabric_details) !!}</div>
                </div>
                <div class="product-tab-panel product-rich-content hidden" data-panel="care" id="product-panel-care">
                    {!! RichContent::render($product->care_instructions) !!}
                </div>
            </div>

            <dl class="mt-12 grid gap-6 sm:grid-cols-2" id="product-detail-items">
                @foreach ($product->detailItems as $item)
                    <div class="border-t border-black-brand/10 pt-5">
                        <dt class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/50">{{ $item->label }}
                        </dt>
                        <dd class="mt-2 text-[0.92rem] leading-relaxed text-black-brand/75">{{ $item->content }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        @php
            $crossModelAlternatives = $relationGroups[ProductRelationType::Alternative->value] ?? collect();
        @endphp

        <x-product.products-grid title="Рекомендовані альтернативи" :products="$crossModelAlternatives" :catalog-url="route('catalog.show', $product->catalog)" />

        <x-product.products-grid title="Схожі товари" :products="$similarProducts" :catalog-url="route('catalog.index')"
            catalog-label="Увесь каталог →" />

        @foreach ([
            ProductRelationType::Related->value => 'Інші моделі',
            ProductRelationType::Upsell->value => 'Доповніть образ',
        ] as $type => $sectionTitle)
            <x-product.products-grid :title="$sectionTitle" :products="$relationGroups[$type] ?? collect()" :catalog-url="route('catalog.show', $product->catalog)" />
        @endforeach
    </div>
@endsection
