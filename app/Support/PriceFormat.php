<?php

namespace App\Support;

class PriceFormat
{
    public static function uah(float|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        return number_format((float) $amount, 0, ',', ' ').' ₴';
    }

    /** @deprecated Use uah() */
    public static function usd(float|string|null $amount): string
    {
        return self::uah($amount);
    }
}
