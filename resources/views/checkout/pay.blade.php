@extends('layouts.store')

@section('title', 'Оплата '.$order->number.' | NOEMA')

@section('store')
    <div class="checkout-pay mx-auto max-w-lg py-16 text-center" data-aos="fade-up">
        <p class="text-[0.68rem] uppercase tracking-[0.2em] text-black-brand/45">Перенаправлення на оплату</p>
        <h1 class="mt-3 text-[1.6rem] uppercase tracking-[0.06em]">Замовлення {{ $order->number }}</h1>
        <p class="mt-4 text-[0.9rem] text-black-brand/60">
            Зараз ви будете перенаправлені на захищену сторінку LiqPay.
        </p>

        <form method="POST" action="{{ $liqpay['url'] }}" accept-charset="utf-8" class="mt-10" id="liqpay-form">
            <input type="hidden" name="data" value="{{ $liqpay['data'] }}">
            <input type="hidden" name="signature" value="{{ $liqpay['signature'] }}">
            <button type="submit"
                class="border border-black-brand bg-black-brand px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition hover:bg-white-brand hover:text-black-brand">
                Перейти до оплати
            </button>
        </form>

        <a href="{{ route('checkout.success', $order) }}"
            class="mt-6 inline-block text-[0.68rem] uppercase tracking-[0.14em] text-black-brand/45 hover:text-black-brand">
            Повернутись до замовлення
        </a>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('liqpay-form')?.submit();
    </script>
@endpush
