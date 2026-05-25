<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\CheckoutSettings;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    public function shippingCost(): float
    {
        return (float) (CheckoutSettings::current()->default_shipping_cost
            ?? config('checkout.shipping_cost', 0));
    }

    /** @param  array<string, mixed>  $input */
    public function placeOrder(array $input): Order
    {
        $items = $this->cart->enrichedItems();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Кошик порожній.',
            ]);
        }

        $subtotal = $this->cart->subtotal();
        $shippingCost = $this->shippingCost();
        $total = round($subtotal + $shippingCost, 2);

        $input = $this->normalizeShippingInput($input);

        return DB::transaction(function () use ($input, $items, $subtotal, $shippingCost, $total): Order {
            $order = Order::query()->create([
                'number' => $this->generateOrderNumber(),
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'payment_method' => PaymentMethod::from($input['payment_method']),
                'customer_name' => $input['customer_name'],
                'customer_phone' => $input['customer_phone'],
                'customer_email' => $input['customer_email'] ?? null,
                'shipping_method' => ShippingMethod::from($input['shipping_method']),
                'shipping_city_ref' => $input['shipping_city_ref'] ?? null,
                'shipping_city_name' => $input['shipping_city_name'] ?? null,
                'shipping_warehouse_ref' => $input['shipping_warehouse_ref'] ?? null,
                'shipping_warehouse_name' => $input['shipping_warehouse_name'] ?? null,
                'shipping_address' => $input['shipping_address'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'customer_notes' => $input['customer_notes'] ?? null,
                'session_id' => session()->getId(),
            ]);

            foreach ($items as $line) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $line['product_id'] ?? null,
                    'variant_id' => $line['variant_id'] ?? null,
                    'product_name' => $line['product_name'],
                    'product_slug' => $line['product_slug'] ?? null,
                    'color_name' => $line['color_name'] ?? null,
                    'size' => $line['size'] ?? null,
                    'sku' => $line['sku'] ?? null,
                    'quantity' => (int) $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                    'customizations' => $line['customizations'] ?? null,
                    'notes' => $line['notes'] ?? null,
                    'image' => $line['image'] ?? null,
                    'group_id' => $line['group_id'] ?? null,
                    'group_index' => $line['group_index'] ?? null,
                    'group_total' => $line['group_total'] ?? null,
                ]);
            }

            $this->cart->clear();

            app(\App\Services\OrderManagementService::class)->addNote(
                $order,
                'Замовлення створено на сайті',
            );

            return $order->load('items');
        });
    }

    /** @param  array<string, mixed>  $payload */
    public function applyLiqPayCallback(array $payload): ?Order
    {
        $orderNumber = (string) ($payload['order_id'] ?? '');

        if ($orderNumber === '') {
            return null;
        }

        $order = Order::query()->where('number', $orderNumber)->first();

        if (! $order) {
            return null;
        }

        $status = (string) ($payload['status'] ?? '');

        $order->liqpay_payment_id = (string) ($payload['payment_id'] ?? $payload['transaction_id'] ?? $order->liqpay_payment_id);

        if (in_array($status, ['success', 'sandbox', 'wait_accept'], true)) {
            $order->payment_status = PaymentStatus::Paid;
            $order->status = OrderStatus::Paid;
        } elseif (in_array($status, ['failure', 'error', 'reversed'], true)) {
            $order->payment_status = PaymentStatus::Failed;
        }

        $order->save();

        return $order;
    }

    /** @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeShippingInput(array $input): array
    {
        $method = ShippingMethod::from($input['shipping_method']);

        if ($method->usesNovaPoshtaApi()) {
            $input['shipping_address'] = null;

            return $input;
        }

        $input['shipping_city_ref'] = null;
        $input['shipping_warehouse_ref'] = null;
        $input['shipping_warehouse_name'] = null;

        return $input;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'N'.now()->format('ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
