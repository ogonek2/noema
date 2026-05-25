<?php

namespace App\Services;

use App\Models\CheckoutSettings;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class LiqPayService
{
    public function isConfigured(): bool
    {
        $settings = CheckoutSettings::current();

        if (! $settings->liqpay_enabled) {
            return false;
        }

        return filled($settings->resolvedLiqpayPublicKey())
            && filled($settings->resolvedLiqpayPrivateKey());
    }

    private function settings(): CheckoutSettings
    {
        return CheckoutSettings::current();
    }

    /** @return array{data: string, signature: string, url: string}|null */
    public function checkoutForm(Order $order): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $settings = $this->settings();

        $params = [
            'version' => 3,
            'public_key' => $settings->resolvedLiqpayPublicKey(),
            'action' => 'pay',
            'amount' => round((float) $order->total, 2),
            'currency' => $settings->liqpay_currency ?? 'UAH',
            'description' => 'Замовлення '.$order->number.' | NOEMA',
            'order_id' => $order->number,
            'result_url' => route('checkout.success', $order),
            'server_url' => route('checkout.liqpay.callback'),
        ];

        if ($settings->liqpay_sandbox) {
            $params['sandbox'] = 1;
        }

        $data = base64_encode(json_encode($params, JSON_UNESCAPED_UNICODE));

        return [
            'data' => $data,
            'signature' => $this->signature($data),
            'url' => config('services.liqpay.checkout_url', 'https://www.liqpay.ua/api/3/checkout'),
        ];
    }

    /** @return array<string, mixed>|null */
    public function decodeCallback(string $data, string $signature): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        if (! hash_equals($this->signature($data), $signature)) {
            Log::warning('LiqPay callback signature mismatch.');

            return null;
        }

        $decoded = json_decode(base64_decode($data, true) ?: '', true);

        return is_array($decoded) ? $decoded : null;
    }

    public function signature(string $data): string
    {
        $privateKey = (string) $this->settings()->resolvedLiqpayPrivateKey();

        return base64_encode(sha1($privateKey.$data.$privateKey, true));
    }
}
