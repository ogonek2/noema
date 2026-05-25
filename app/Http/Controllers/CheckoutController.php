<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\LiqPayService;
use App\Services\NovaPoshtaService;
use App\Services\StorefrontService;
use App\Support\PriceFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
        private readonly LiqPayService $liqPay,
        private readonly StorefrontService $storefront,
        private readonly NovaPoshtaService $novaPoshta,
    ) {}

    public function index(): View|RedirectResponse
    {
        if ($this->cart->count() === 0) {
            return redirect()
                ->route('cart.index')
                ->with('success', 'Додайте товари до кошика перед оформленням.');
        }

        $subtotal = $this->cart->subtotal();
        $shippingCost = $this->checkout->shippingCost();
        $total = round($subtotal + $shippingCost, 2);

        return view('checkout.index', [
            'groups' => $this->cart->displayGroups(),
            'subtotal' => $subtotal,
            'subtotalFormatted' => $this->cart->formattedSubtotal(),
            'shippingCost' => $shippingCost,
            'shippingCostFormatted' => PriceFormat::usd($shippingCost),
            'total' => $total,
            'totalFormatted' => PriceFormat::usd($total),
            'liqPayReady' => $this->liqPay->isConfigured(),
            'novaPoshtaConfigured' => $this->novaPoshta->isConfigured(),
            'shippingMethods' => ShippingMethod::cases(),
            'paymentMethods' => PaymentMethod::cases(),
            'footerCatalogs' => $this->storefront->activeCatalogs(),
        ]);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkout->placeOrder($request->validated());

        if ($order->payment_method->redirectsToGateway()) {
            if (! $this->liqPay->isConfigured()) {
                return redirect()
                    ->route('checkout.success', $order)
                    ->with('success', 'Замовлення прийнято. Оплата LiqPay буде доступна після налаштування ключів.');
            }

            return redirect()->route('checkout.pay', $order);
        }

        $message = match ($order->payment_method) {
            PaymentMethod::Cod => 'Замовлення прийнято. Оплата при отриманні (накладений платіж).',
            PaymentMethod::Iban => 'Замовлення прийнято. Реквізити для оплати на сторінці нижче.',
            default => 'Дякуємо! Замовлення оформлено.',
        };

        return redirect()
            ->route('checkout.success', $order)
            ->with('success', $message);
    }

    public function pay(Order $order): View|RedirectResponse
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            return redirect()->route('checkout.success', $order);
        }

        $form = $this->liqPay->checkoutForm($order);

        if (! $form) {
            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'Замовлення збережено. Оплата онлайн тимчасово недоступна.');
        }

        return view('checkout.pay', [
            'order' => $order,
            'liqpay' => $form,
            'footerCatalogs' => $this->storefront->activeCatalogs(),
        ]);
    }

    public function success(Order $order): View
    {
        $order->load('items');

        return view('checkout.success', [
            'order' => $order,
            'iban' => config('checkout.iban'),
            'footerCatalogs' => $this->storefront->activeCatalogs(),
        ]);
    }

    public function liqPayCallback(Request $request): Response
    {
        $data = (string) $request->input('data', '');
        $signature = (string) $request->input('signature', '');

        $payload = $this->liqPay->decodeCallback($data, $signature);

        if ($payload) {
            $this->checkout->applyLiqPayCallback($payload);
        }

        return response('OK');
    }
}
