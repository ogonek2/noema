<?php

namespace App\Enums;

enum LandingHeroImageFocal: string
{
    case Top = 'top';
    case Center = 'center';
    case Bottom = 'bottom';
    case Left = 'left';
    case Right = 'right';

    public function label(): string
    {
        return match ($this) {
            self::Top => 'Верх',
            self::Center => 'Центр',
            self::Bottom => 'Низ',
            self::Left => 'Ліворуч',
            self::Right => 'Праворуч',
        };
    }

    public function objectPosition(): string
    {
        return match ($this) {
            self::Top => 'center top',
            self::Center => 'center center',
            self::Bottom => 'center bottom',
            self::Left => 'left center',
            self::Right => 'right center',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $focal): array => [$focal->value => $focal->label()])
            ->all();
    }

    public static function tryFromContent(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Top;
    }
}
