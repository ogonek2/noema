@extends('layouts.store')

@php
    $breadcrumbs = [
        ['label' => 'Головна', 'url' => route('home')],
        ['label' => 'Кошик'],
    ];
    $footerCatalogs = $footerCatalogs ?? collect();
@endphp

@section('store')
    <div class="cart-page" data-aos="fade-up">
        <h1 class="text-[2rem] font-light uppercase tracking-[0.06em] lg:text-[2.6rem]">Кошик</h1>

        @if (session('success'))
            <p class="mt-4 border border-black-brand/15 bg-black-brand/[0.03] px-4 py-3 text-[0.82rem] tracking-[0.08em] text-black-brand/75">
                {{ session('success') }}
            </p>
        @endif

        @if ($groups->isEmpty())
            <div class="mt-12 max-w-md">
                <p class="text-[1rem] leading-relaxed text-black-brand/65">Ваш кошик порожній.</p>
                <a href="{{ route('catalog.index') }}"
                    class="mt-8 inline-block border border-black-brand bg-black-brand px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition hover:bg-white-brand hover:text-black-brand">
                    До каталогу
                </a>
            </div>
        @else
            <div class="mt-10 grid gap-12 lg:grid-cols-[1fr_320px] lg:gap-16">
                <div class="space-y-0 divide-y divide-black-brand/10 border-t border-black-brand/10">
                    @foreach ($groups as $group)
                        @if ($group['type'] === 'batch')
                            @php
                                $first = $group['items']->first();
                                $sizeSummary = $group['items']
                                    ->groupBy(fn ($line) => trim(($line['color_name'] ?? '').' · '.$line['size'], ' ·'))
                                    ->map(fn ($lines, $label) => $label.' ×'.$lines->count())
                                    ->implode(' · ');
                            @endphp
                            <article class="py-8">
                                <div class="grid gap-6 sm:grid-cols-[120px_1fr] lg:grid-cols-[140px_1fr_auto] lg:gap-8">
                                    <a href="{{ route('product.show', $first['product_slug']) }}"
                                        class="block aspect-[4/5] overflow-hidden bg-black-brand/5">
                                        @if ($first['image'])
                                            <img src="{{ $first['image'] }}" alt="{{ $first['product_name'] }}"
                                                class="h-full w-full object-cover" loading="lazy">
                                        @endif
                                    </a>

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="border border-black-brand/20 px-2 py-0.5 text-[0.58rem] uppercase tracking-[0.14em] text-black-brand/55">
                                                {{ $group['label'] }}
                                            </span>
                                            <span class="text-[0.62rem] tracking-[0.1em] text-black-brand/40">
                                                {{ $group['items']->count() }} поз.
                                            </span>
                                        </div>
                                        <p class="mt-2 text-[0.65rem] uppercase tracking-[0.18em] text-black-brand/45">
                                            {{ $first['color_name'] }}
                                        </p>
                                        <h2 class="mt-1 text-[1.1rem] uppercase tracking-[0.05em] lg:text-[1.25rem]">
                                            <a href="{{ route('product.show', $first['product_slug']) }}" class="hover:underline">
                                                {{ $first['product_name'] }}
                                            </a>
                                        </h2>
                                        <p class="mt-3 text-[0.78rem] leading-relaxed text-black-brand/60">
                                            <span class="uppercase tracking-[0.14em] text-black-brand/45">Склад набору:</span>
                                            {{ $sizeSummary }}
                                        </p>

                                        <details class="cart-batch-details mt-5 group">
                                            <summary class="cursor-pointer list-none text-[0.65rem] uppercase tracking-[0.16em] text-black-brand/50 transition hover:text-black-brand [&::-webkit-details-marker]:hidden">
                                                <span class="group-open:hidden">Показати кожну позицію</span>
                                                <span class="hidden group-open:inline">Згорнути позиції</span>
                                            </summary>
                                            <ul class="mt-4 space-y-4 border-l border-black-brand/10 pl-4">
                                                @foreach ($group['items'] as $item)
                                                    <li class="text-[0.78rem] text-black-brand/70">
                                                        <span class="text-black-brand/40">№{{ $item['group_index'] }}</span>
                                                        @if ($item['color_name'])
                                                            {{ $item['color_name'] }} ·
                                                        @endif
                                                        {{ $item['size'] }}
                                                        @if (! empty($item['customizations']))
                                                            <ul class="mt-1 space-y-0.5 pl-0">
                                                                @foreach ($item['customizations'] as $customization)
                                                                    <li>
                                                                        <span class="text-black-brand/40">{{ $customization['name'] }}:</span>
                                                                        {{ $customization['label'] }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                        <span class="ml-2 text-black-brand/45">{{ $item['line_total_formatted'] }}</span>
                                                        <form action="{{ route('cart.destroy', $item['key']) }}" method="post" class="mt-2 inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-[0.58rem] uppercase tracking-[0.12em] text-black-brand/35 hover:text-black-brand">
                                                                Видалити
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </details>

                                        <form action="{{ route('cart.destroy-group', $group['group_id']) }}" method="post" class="mt-5">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-[0.65rem] uppercase tracking-[0.16em] text-black-brand/40 transition hover:text-black-brand">
                                                Видалити весь набір
                                            </button>
                                        </form>
                                    </div>

                                    <div class="flex flex-col items-start gap-2 sm:col-span-2 lg:col-span-1 lg:items-end">
                                        <p class="text-[1rem] tracking-[0.08em]">{{ $group['subtotal_formatted'] }}</p>
                                        <p class="text-[0.68rem] tracking-[0.1em] text-black-brand/45">за набір</p>
                                    </div>
                                </div>
                            </article>
                        @else
                            @php $item = $group['items']->first(); @endphp
                            <article class="grid gap-6 py-8 sm:grid-cols-[120px_1fr] lg:grid-cols-[140px_1fr_auto] lg:gap-8">
                                <a href="{{ route('product.show', $item['product_slug']) }}" class="block aspect-[4/5] overflow-hidden bg-black-brand/5">
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}"
                                            class="h-full w-full object-cover" loading="lazy">
                                    @endif
                                </a>

                                <div class="min-w-0">
                                    <p class="text-[0.65rem] uppercase tracking-[0.18em] text-black-brand/45">
                                        {{ $item['color_name'] }}
                                    </p>
                                    <h2 class="mt-1 text-[1.1rem] uppercase tracking-[0.05em] lg:text-[1.25rem]">
                                        <a href="{{ route('product.show', $item['product_slug']) }}" class="hover:underline">
                                            {{ $item['product_name'] }}
                                        </a>
                                    </h2>
                                    <p class="mt-2 text-[0.72rem] tracking-[0.12em] text-black-brand/50">
                                        Розмір: {{ $item['size'] }} · SKU: {{ $item['sku'] }}
                                    </p>

                                    @if (! empty($item['customizations']))
                                        <ul class="mt-4 space-y-1 text-[0.78rem] text-black-brand/65">
                                            @foreach ($item['customizations'] as $customization)
                                                <li>
                                                    <span class="text-black-brand/45">{{ $customization['name'] }}:</span>
                                                    {{ $customization['label'] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if ($item['notes'])
                                        <p class="mt-3 text-[0.78rem] leading-relaxed text-black-brand/60">
                                            <span class="uppercase tracking-[0.14em] text-black-brand/45">Побажання:</span>
                                            {{ $item['notes'] }}
                                        </p>
                                    @endif

                                    <form action="{{ route('cart.destroy', $item['key']) }}" method="post" class="mt-5">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-[0.65rem] uppercase tracking-[0.16em] text-black-brand/40 transition hover:text-black-brand">
                                            Видалити
                                        </button>
                                    </form>
                                </div>

                                <div class="flex flex-col items-start gap-4 sm:col-span-2 lg:col-span-1 lg:items-end">
                                    <p class="text-[1rem] tracking-[0.08em]">{{ $item['line_total_formatted'] }}</p>
                                    <p class="text-[0.68rem] tracking-[0.1em] text-black-brand/45">
                                        {{ $item['unit_price_formatted'] }} / од.
                                    </p>
                                    <form action="{{ route('cart.update', $item['key']) }}" method="post"
                                        class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="sr-only" for="qty-{{ $item['key'] }}">Кількість</label>
                                        <input type="number" name="quantity" id="qty-{{ $item['key'] }}"
                                            value="{{ $item['quantity'] }}" min="1" max="99"
                                            class="w-16 border border-black-brand/15 bg-transparent px-2 py-2 text-center text-[0.82rem] tracking-[0.08em]">
                                        <button type="submit"
                                            class="border border-black-brand/15 px-3 py-2 text-[0.62rem] uppercase tracking-[0.14em] transition hover:border-black-brand">
                                            Оновити
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>

                <aside class="h-fit border border-black-brand/10 p-6 lg:sticky lg:top-32">
                    <p class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/45">Разом</p>
                    <p class="mt-2 text-[1.6rem] tracking-[0.06em]">{{ $subtotalFormatted }}</p>
                    <p class="mt-3 text-[0.72rem] leading-relaxed text-black-brand/50">
                        Доставка та податки розраховуються на наступному кроці.
                    </p>
                    <a href="{{ route('checkout.index') }}"
                        class="mt-8 block w-full border border-black-brand bg-black-brand px-6 py-4 text-center text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition hover:bg-white-brand hover:text-black-brand">
                        Оформити замовлення
                    </a>
                    <a href="{{ route('catalog.index') }}"
                        class="mt-4 block text-center text-[0.68rem] uppercase tracking-[0.16em] text-black-brand/45 hover:text-black-brand">
                        Продовжити покупки
                    </a>
                </aside>
            </div>
        @endif
    </div>
@endsection
