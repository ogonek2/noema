<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class CheckoutSettings extends Model
{
    protected $fillable = [
        'liqpay_enabled',
        'liqpay_public_key',
        'liqpay_private_key',
        'liqpay_sandbox',
        'liqpay_currency',
        'cod_enabled',
        'iban_enabled',
        'iban_holder',
        'iban_number',
        'iban_bank',
        'iban_purpose',
        'default_shipping_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'liqpay_enabled' => 'boolean',
            'liqpay_sandbox' => 'boolean',
            'cod_enabled' => 'boolean',
            'iban_enabled' => 'boolean',
            'default_shipping_cost' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'liqpay_enabled' => filled(config('services.liqpay.public_key')),
            'liqpay_public_key' => config('services.liqpay.public_key'),
            'liqpay_sandbox' => filter_var(config('services.liqpay.sandbox', true), FILTER_VALIDATE_BOOLEAN),
            'liqpay_currency' => config('services.liqpay.currency', 'UAH'),
            'iban_holder' => config('checkout.iban.recipient'),
            'iban_number' => config('checkout.iban.number'),
            'iban_bank' => config('checkout.iban.bank'),
            'iban_purpose' => config('checkout.iban.purpose'),
            'default_shipping_cost' => config('checkout.shipping_cost', 0),
        ]);
    }

    public function setLiqpayPrivateKeyAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['liqpay_private_key'] = Crypt::encryptString($value);
    }

    public function getLiqpayPrivateKeyAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function resolvedLiqpayPublicKey(): ?string
    {
        return $this->liqpay_public_key ?: config('services.liqpay.public_key');
    }

    public function resolvedLiqpayPrivateKey(): ?string
    {
        return $this->liqpay_private_key ?: config('services.liqpay.private_key');
    }
}
