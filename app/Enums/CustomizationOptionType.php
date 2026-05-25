<?php

namespace App\Enums;

enum CustomizationOptionType: string
{
    case Checkbox = 'checkbox';
    case Select = 'select';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Checkbox => 'Прапорець',
            self::Select => 'Випадаючий список',
            self::Text => 'Текстове поле',
        };
    }
}
