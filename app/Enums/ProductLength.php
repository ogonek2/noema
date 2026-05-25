<?php

namespace App\Enums;

enum ProductLength: string
{
    case Petite = 'petite';
    case Regular = 'regular';
    case Tall = 'tall';

    public function label(): string
    {
        return match ($this) {
            self::Petite => 'Petite',
            self::Regular => 'Regular',
            self::Tall => 'Tall',
        };
    }
}
