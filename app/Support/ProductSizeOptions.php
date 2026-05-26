<?php

namespace App\Support;

class ProductSizeOptions
{
    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'XXS' => 'XXS',
            'XS' => 'XS',
            'S' => 'S',
            'M' => 'M',
            'L' => 'L',
            'XL' => 'XL',
            '2XL' => '2XL',
        ];
    }
}
