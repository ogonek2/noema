<?php

namespace App\Enums;

enum HomepageBlockSlug: string
{
    case Hero = 'hero';
    case AboutUs = 'about_us';
    case ProductBox = 'product_box';
    case Benefits = 'benefits';
    case Statement = 'statement';
    case Footer = 'footer';
    case Navigator = 'navigator';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero',
            self::AboutUs => 'Про бренд',
            self::ProductBox => 'Продукт',
            self::Benefits => 'Переваги (заголовок)',
            self::Statement => 'Statement',
            self::Footer => 'Футер',
            self::Navigator => 'Навігація',
        };
    }
}
