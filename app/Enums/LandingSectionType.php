<?php

namespace App\Enums;

enum LandingSectionType: string
{
    case Hero = 'hero';
    case Split = 'split';
    case Content = 'content';
    case Features = 'features';
    case Cta = 'cta';
    case Stats = 'stats';
    case Faq = 'faq';
    case Gallery = 'gallery';
    case Products = 'products';
    case RichHtml = 'rich_html';
    case Spacer = 'spacer';
    case Form = 'form';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero (перший екран)',
            self::Split => 'Текст + зображення',
            self::Content => 'Текстовий блок',
            self::Features => 'Переваги / картки',
            self::Cta => 'Банер з кнопками',
            self::Stats => 'Цифри / статистика',
            self::Faq => 'FAQ',
            self::Gallery => 'Галерея',
            self::Products => 'Товари',
            self::RichHtml => 'Вільний HTML',
            self::Spacer => 'Відступ',
            self::Form => 'Форма (заявки)',
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
