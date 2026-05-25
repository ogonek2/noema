@extends('layouts.store')

@php
    use App\Enums\PaymentMethod;
    use App\Support\PriceFormat;

    $breadcrumbs = [
        ['label' => 'Головна', 'url' => route('home')],
        ['label' => 'Замовлення '.$order->number],
    ];
@endphp

@section('store')
    <div class="checkout-success max-w-2xl" data-aos="fade-up">
        @if (session('success'))
            <p class="border border-black-brand/15 bg-black-brand/[0.03] px-4 py-3 text-[0.82rem] tracking-[0.08em] text-black-brand/75">
                {{ session('success') }}
            </p>
        @endif

        <h1 class="mt-6 text-[2rem] font-light uppercase tracking-[0.06em] lg:text-[2.4rem]">Дякуємо за замовлення</h1>
        <p class="mt-4 text-[1rem] leading-relaxed text-black-brand/65">
            Номер замовлення: <strong class="text-black-brand">{{ $order->number }}</strong>
        </p>

        @if ($order->payment_method === PaymentMethod::Iban && filled($iban['iban'] ?? null))
            <div class="mt-8 border border-black-brand/10 bg-black-brand/[0.02] p-5">
                <p class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/50">Реквізити для оплати</p>
                <dl class="mt-4 space-y-2 text-[0.88rem]">
                    @if ($iban['recipient'])
                        <div>
                            <dt class="text-black-brand/45">Отримувач</dt>
                            <dd class="mt-0.5 font-medium">{{ $iban['recipient'] }}</dd>
                        </div>
                    @endif
                    @if ($iban['iban'])
                        <div>
                            <dt class="text-black-brand/45">IBAN</dt>
                            <dd class="mt-0.5 font-mono text-[0.95rem] tracking-wide">{{ $iban['iban'] }}</dd>
                        </div>
                    @endif
                    @if ($iban['bank'])
                        <div>
                            <dt class="text-black-brand/45">Банк</dt>
                            <dd class="mt-0.5">{{ $iban['bank'] }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-black-brand/45">Призначення платежу</dt>
                        <dd class="mt-0.5">{{ $iban['purpose'] }} {{ $order->number }}</dd>
                    </div>
                    <div>
                        <dt class="text-black-brand/45">Сума</dt>
                        <dd class="mt-0.5 font-medium">{{ PriceFormat::usd($order->total) }}</dd>
                    </div>
                </dl>
            </div>
        @elseif ($order->payment_method === PaymentMethod::Iban)
            <p class="mt-6 text-[0.88rem] text-black-brand/60">
                Реквізити IBAN будуть надіслані менеджером. Додайте <code>CHECKOUT_IBAN_*</code> у .env для відображення на цій сторінці.
            </p>
        @endif

        <dl class="mt-8 space-y-3 border-t border-black-brand/10 pt-8 text-[0.88rem]">
            <div class="flex justify-between gap-4">
                <dt class="text-black-brand/50">Статус</dt>
                <dd>{{ $order->status->label() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-black-brand/50">Оплата</dt>
                <dd>{{ $order->payment_method->label() }} — {{ $order->payment_status->label() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-black-brand/50">Доставка</dt>
                <dd>{{ $order->shipping_method->label() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-black-brand/50">Сума</dt>
                <dd>{{ PriceFormat::usd($order->total) }}</dd>
            </div>
            @if ($order->shipping_city_name || $order->shipping_address)
                <div>
                    <dt class="text-black-brand/50">Адреса</dt>
                    <dd class="mt-1">
                        @if ($order->shipping_city_name)
                            {{ $order->shipping_city_name }}
                        @endif
                        @if ($order->shipping_warehouse_name)
                            , {{ $order->shipping_warehouse_name }}
                        @endif
                        @if ($order->shipping_address)
                            <br>{{ $order->shipping_address }}
                        @endif
                    </dd>
                </div>
            @endif
        </dl>

        <ul class="mt-10 space-y-4 border-t border-black-brand/10 pt-8">
            @foreach ($order->items as $item)
                <li class="flex justify-between gap-4 text-[0.82rem]">
                    <span>
                        {{ $item->product_name }}
                        @if ($item->color_name)
                            · {{ $item->color_name }}
                        @endif
                        · {{ $item->size }} × {{ $item->quantity }}
                    </span>
                    <span>{{ PriceFormat::usd($item->line_total) }}</span>
                </li>
            @endforeach
        </ul>

        <div class="mt-12 flex flex-wrap gap-3">
            <a href="{{ route('catalog.index') }}"
                class="border border-black-brand bg-black-brand px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition hover:bg-white-brand hover:text-black-brand">
                До каталогу
            </a>
            <a href="{{ route('home') }}"
                class="border border-black-brand/15 px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] transition hover:border-black-brand">
                На головну
            </a>
        </div>
    </div>
@endsection
