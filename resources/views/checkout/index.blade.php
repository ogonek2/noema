@extends('layouts.store')

@php
    use App\Enums\PaymentMethod;
    use App\Enums\ShippingMethod;

    $breadcrumbs = [
        ['label' => 'Головна', 'url' => route('home')],
        ['label' => 'Кошик', 'url' => route('cart.index')],
        ['label' => 'Оформлення'],
    ];

    $oldShipping = old('shipping_method', ShippingMethod::NovaPoshtaWarehouse->value);
    $oldPayment = old('payment_method', PaymentMethod::LiqPay->value);
@endphp

@section('store')
    <div class="checkout-page" data-aos="fade-up">
        <h1 class="text-[2rem] font-light uppercase tracking-[0.06em] lg:text-[2.6rem]">Оформлення</h1>

        <form action="{{ route('checkout.store') }}" method="post" class="mt-10 grid gap-12 lg:grid-cols-[1fr_340px] lg:gap-16"
            data-checkout-form novalidate>
            @csrf

            <div class="space-y-10">
                <section>
                    <h2 class="text-[0.72rem] uppercase tracking-[0.2em] text-black-brand/50">Контакти</h2>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="customer_name" class="mb-2 block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/55">
                                Імʼя та прізвище <span class="text-black-brand">*</span>
                            </label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" required
                                autocomplete="name"
                                class="w-full border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.9rem] tracking-[0.04em] focus:border-black-brand focus:outline-none">
                            @error('customer_name')
                                <p class="mt-1 text-[0.72rem] text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="customer_phone" class="mb-2 block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/55">
                                Телефон <span class="text-black-brand">*</span>
                            </label>
                            <input type="tel" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}" required
                                autocomplete="tel" placeholder="+380"
                                class="w-full border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.9rem] tracking-[0.04em] focus:border-black-brand focus:outline-none">
                            @error('customer_phone')
                                <p class="mt-1 text-[0.72rem] text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="customer_email" class="mb-2 block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/55">
                                Email
                            </label>
                            <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email') }}"
                                autocomplete="email"
                                class="w-full border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.9rem] tracking-[0.04em] focus:border-black-brand focus:outline-none">
                            @error('customer_email')
                                <p class="mt-1 text-[0.72rem] text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section data-checkout-shipping-section>
                    <h2 class="text-[0.72rem] uppercase tracking-[0.2em] text-black-brand/50">Доставка</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($shippingMethods as $method)
                            <label class="checkout-shipping-option flex cursor-pointer items-start gap-3 border border-black-brand/15 px-4 py-4 transition has-[:checked]:border-black-brand">
                                <input type="radio" name="shipping_method" value="{{ $method->value }}"
                                    class="mt-1 accent-black-brand"
                                    data-checkout-shipping-option
                                    {{ $oldShipping === $method->value ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-[0.78rem] uppercase tracking-[0.12em]">{{ $method->label() }}</span>
                                    @if ($method->usesNovaPoshtaApi())
                                        <span class="mt-1 block text-[0.72rem] text-black-brand/50">Пошук міста та відділення через API Нової Пошти</span>
                                    @else
                                        <span class="mt-1 block text-[0.72rem] text-black-brand/50">Місто та адреса вводяться вручну</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('shipping_method')
                        <p class="mt-2 text-[0.72rem] text-red-700">{{ $message }}</p>
                    @enderror

                    {{-- Нова Пошта відділення (API) --}}
                    <div class="mt-6 space-y-5 {{ $oldShipping === ShippingMethod::NovaPoshtaWarehouse->value ? '' : 'hidden' }}"
                        data-checkout-shipping-panel="nova_poshta_warehouse"
                        data-np-root
                        data-cities-url="{{ route('nova-poshta.cities') }}"
                        data-warehouses-url="{{ route('nova-poshta.warehouses') }}">
                        <input type="hidden" name="shipping_city_ref" id="shipping_city_ref" value="{{ old('shipping_city_ref') }}">
                        <input type="hidden" name="shipping_city_name" id="shipping_city_name" value="{{ old('shipping_city_name') }}">
                        <input type="hidden" name="shipping_warehouse_ref" id="shipping_warehouse_ref" value="{{ old('shipping_warehouse_ref') }}">
                        <input type="hidden" name="shipping_warehouse_name" id="shipping_warehouse_name" value="{{ old('shipping_warehouse_name') }}">

                        <p class="{{ $novaPoshtaConfigured ? 'hidden' : '' }} text-[0.78rem] text-amber-800" data-np-config-warning>
                            API Нової Пошти не налаштовано. Додайте <code class="text-[0.7rem]">NOVA_POSHTA_API_KEY</code> у .env
                        </p>

                        <div class="relative">
                            <label for="np-city-search" class="mb-2 block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/55">
                                Місто <span class="text-black-brand">*</span>
                            </label>
                            <input type="text" id="np-city-search" value="{{ old('shipping_city_name') }}"
                                placeholder="Мінімум 2 символи — напр. Київ, Львів"
                                autocomplete="off"
                                class="w-full border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.9rem] focus:border-black-brand focus:outline-none"
                                data-np-city-input>
                            <p class="mt-1 hidden text-[0.68rem] text-black-brand/45" data-np-city-hint></p>
                            <ul class="checkout-np-dropdown absolute z-20 mt-1 hidden w-full border border-black-brand/15 bg-white-brand shadow-lg"
                                data-np-city-results></ul>
                            @error('shipping_city_ref')
                                <p class="mt-1 text-[0.72rem] text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="relative">
                            <label for="np-warehouse-search" class="mb-2 block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/55">
                                Відділення <span class="text-black-brand">*</span>
                            </label>
                            <input type="text" id="np-warehouse-search" value="{{ old('shipping_warehouse_name') }}"
                                placeholder="Спочатку оберіть місто, потім введіть № або вулицю"
                                autocomplete="off" disabled
                                class="w-full border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.9rem] focus:border-black-brand focus:outline-none disabled:cursor-not-allowed disabled:bg-black-brand/[0.03]"
                                data-np-warehouse-input>
                            <p class="mt-1 text-[0.68rem] text-black-brand/45" data-np-warehouse-hint>
                                Пошук відділень — після вибору міста, мінімум 2 символи
                            </p>
                            <ul class="checkout-np-dropdown absolute z-20 mt-1 hidden max-h-56 w-full overflow-y-auto border border-black-brand/15 bg-white-brand shadow-lg"
                                data-np-warehouse-results></ul>
                            @error('shipping_warehouse_ref')
                                <p class="mt-1 text-[0.72rem] text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Ручна адреса (курʼєр НП, Укрпошта, Meest) --}}
                    @php
                        $manualMethods = [
                            ShippingMethod::NovaPoshtaCourier->value,
                            ShippingMethod::Ukrposhta->value,
                            ShippingMethod::Meest->value,
                        ];
                        $showManual = in_array($oldShipping, $manualMethods, true);
                    @endphp
                    <div class="mt-6 space-y-5 {{ $showManual ? '' : 'hidden' }}"
                        data-checkout-shipping-panel-manual
                        data-panel-courier="{{ ShippingMethod::NovaPoshtaCourier->value }}"
                        data-panel-ukrposhta="{{ ShippingMethod::Ukrposhta->value }}"
                        data-panel-meest="{{ ShippingMethod::Meest->value }}">
                        <div>
                            <label for="manual_city" class="mb-2 block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/55">
                                Місто <span class="text-black-brand">*</span>
                            </label>
                            <input type="text" name="shipping_city_name" id="manual_city"
                                value="{{ $showManual ? old('shipping_city_name') : '' }}"
                                placeholder="Населений пункт"
                                class="w-full border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.9rem] focus:border-black-brand focus:outline-none">
                        </div>
                        <div>
                            <label for="manual_address" class="mb-2 block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/55"
                                data-checkout-manual-address-label>
                                Адреса доставки <span class="text-black-brand">*</span>
                            </label>
                            <textarea name="shipping_address" id="manual_address" rows="3"
                                placeholder="Повна адреса або номер відділення"
                                class="w-full resize-y border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.88rem] leading-relaxed focus:border-black-brand focus:outline-none">{{ $showManual ? old('shipping_address') : '' }}</textarea>
                        </div>
                    </div>
                    @error('shipping_city_name')
                        <p class="mt-2 text-[0.72rem] text-red-700">{{ $message }}</p>
                    @enderror
                    @error('shipping_address')
                        <p class="mt-1 text-[0.72rem] text-red-700">{{ $message }}</p>
                    @enderror
                </section>

                <section>
                    <h2 class="text-[0.72rem] uppercase tracking-[0.2em] text-black-brand/50">Оплата</h2>
                    <div class="mt-5 space-y-3">
                        <label class="flex cursor-pointer items-start gap-3 border border-black-brand/15 px-4 py-4 transition has-[:checked]:border-black-brand">
                            <input type="radio" name="payment_method" value="{{ PaymentMethod::LiqPay->value }}"
                                class="mt-1 accent-black-brand" {{ $oldPayment === PaymentMethod::LiqPay->value ? 'checked' : '' }}>
                            <span>
                                <span class="block text-[0.78rem] uppercase tracking-[0.12em]">{{ PaymentMethod::LiqPay->label() }}</span>
                                <span class="mt-1 block text-[0.72rem] text-black-brand/50">
                                    @if ($liqPayReady)
                                        Банківська картка, Apple Pay, Google Pay
                                    @else
                                        Ключі LiqPay ще не додані — замовлення збережеться
                                    @endif
                                </span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 border border-black-brand/15 px-4 py-4 transition has-[:checked]:border-black-brand">
                            <input type="radio" name="payment_method" value="{{ PaymentMethod::Cod->value }}"
                                class="mt-1 accent-black-brand" {{ $oldPayment === PaymentMethod::Cod->value ? 'checked' : '' }}>
                            <span>
                                <span class="block text-[0.78rem] uppercase tracking-[0.12em]">{{ PaymentMethod::Cod->label() }}</span>
                                <span class="mt-1 block text-[0.72rem] text-black-brand/50">Оплата при отриманні на пошті</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 border border-black-brand/15 px-4 py-4 transition has-[:checked]:border-black-brand">
                            <input type="radio" name="payment_method" value="{{ PaymentMethod::Iban->value }}"
                                class="mt-1 accent-black-brand" {{ $oldPayment === PaymentMethod::Iban->value ? 'checked' : '' }}>
                            <span>
                                <span class="block text-[0.78rem] uppercase tracking-[0.12em]">{{ PaymentMethod::Iban->label() }}</span>
                                <span class="mt-1 block text-[0.72rem] text-black-brand/50">Переказ на IBAN — реквізити після оформлення</span>
                            </span>
                        </label>
                    </div>
                    @error('payment_method')
                        <p class="mt-2 text-[0.72rem] text-red-700">{{ $message }}</p>
                    @enderror
                </section>

                <div>
                    <label for="customer_notes" class="mb-2 block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/55">
                        Коментар до замовлення
                    </label>
                    <textarea name="customer_notes" id="customer_notes" rows="3"
                        class="w-full resize-y border border-black-brand/15 bg-white-brand px-3 py-3 text-[0.88rem] leading-relaxed focus:border-black-brand focus:outline-none">{{ old('customer_notes') }}</textarea>
                </div>

                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="agree" value="1" class="mt-1 h-4 w-4 accent-black-brand" {{ old('agree') ? 'checked' : '' }}>
                    <span class="text-[0.78rem] leading-relaxed text-black-brand/65">
                        Погоджуюсь з обробкою персональних даних для оформлення та доставки замовлення.
                    </span>
                </label>
                @error('agree')
                    <p class="text-[0.72rem] text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <aside class="h-fit border border-black-brand/10 p-6 lg:sticky lg:top-32">
                <p class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/45">Ваше замовлення</p>
                <ul class="mt-5 max-h-64 space-y-4 overflow-y-auto border-b border-black-brand/10 pb-5">
                    @foreach ($groups as $group)
                        @foreach ($group['items'] as $item)
                            <li class="flex gap-3 text-[0.78rem]">
                                @if ($item['image'])
                                    <img src="{{ $item['image'] }}" alt="" class="h-14 w-11 shrink-0 object-cover bg-black-brand/5">
                                @endif
                                <div class="min-w-0">
                                    <p class="uppercase tracking-[0.06em]">{{ $item['product_name'] }}</p>
                                    <p class="mt-1 text-black-brand/50">
                                        @if ($item['color_name'])
                                            {{ $item['color_name'] }} ·
                                        @endif
                                        {{ $item['size'] }} × {{ $item['quantity'] }}
                                    </p>
                                    <p class="mt-1">{{ $item['line_total_formatted'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
                <dl class="mt-5 space-y-2 text-[0.82rem]">
                    <div class="flex justify-between gap-4">
                        <dt class="text-black-brand/50">Товари</dt>
                        <dd>{{ $subtotalFormatted }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-black-brand/50">Доставка</dt>
                        <dd>{{ $shippingCostFormatted }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-black-brand/10 pt-3 text-[1rem]">
                        <dt class="uppercase tracking-[0.1em]">Разом</dt>
                        <dd>{{ $totalFormatted }}</dd>
                    </div>
                </dl>
                <button type="submit"
                    class="mt-8 w-full border border-black-brand bg-black-brand px-6 py-4 text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition hover:bg-white-brand hover:text-black-brand">
                    Підтвердити замовлення
                </button>
                <a href="{{ route('cart.index') }}"
                    class="mt-4 block text-center text-[0.68rem] uppercase tracking-[0.16em] text-black-brand/45 hover:text-black-brand">
                    Назад до кошика
                </a>
            </aside>
        </form>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/checkout.js')
@endpush
