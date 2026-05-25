<?php

namespace App\Enums;

enum ProductRelationType: string
{
    case Alternative = 'alternative';
    case Related = 'related';
    case Upsell = 'upsell';

    public function label(): string
    {
        return match ($this) {
            self::Alternative => 'Альтернатива (колір / варіант)',
            self::Related => 'Схожий товар',
            self::Upsell => 'Допродаж',
        };
    }
}
