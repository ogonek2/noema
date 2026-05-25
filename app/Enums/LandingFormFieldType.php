<?php

namespace App\Enums;

enum LandingFormFieldType: string
{
    case Text = 'text';
    case Email = 'email';
    case Tel = 'tel';
    case Textarea = 'textarea';
    case Number = 'number';
    case Select = 'select';
    case Checkbox = 'checkbox';
    case Date = 'date';
    case Url = 'url';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Текст',
            self::Email => 'Email',
            self::Tel => 'Телефон',
            self::Number => 'Число',
            self::Textarea => 'Багаторядковий текст',
            self::Select => 'Випадаючий список',
            self::Checkbox => 'Прапорець (згода)',
            self::Date => 'Дата',
            self::Url => 'Посилання (URL)',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
