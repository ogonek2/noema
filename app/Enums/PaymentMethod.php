<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case LiqPay = 'liqpay';
    case Cod = 'cod';
    case Iban = 'iban';

    public function label(): string
    {
        return match ($this) {
            self::LiqPay => 'На сайті (LiqPay)',
            self::Cod => 'Накладений платіж',
            self::Iban => 'На рахунок (IBAN)',
        };
    }

    public function redirectsToGateway(): bool
    {
        return $this === self::LiqPay;
    }
}
