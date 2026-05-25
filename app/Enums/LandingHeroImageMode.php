<?php

namespace App\Enums;

enum LandingHeroImageMode: string
{
    case ColumnCover = 'column_cover';
    case ColumnContain = 'column_contain';
    case ColumnBleed = 'column_bleed';
    case BackgroundCover = 'background_cover';
    case AmbientRight = 'ambient_right';
    case StackBanner = 'stack_banner';
    case FreeObject = 'free_object';

    public function label(): string
    {
        return match ($this) {
            self::ColumnCover => 'Колонка · заливка (cover)',
            self::ColumnContain => 'Колонка · контейнер (вписати)',
            self::ColumnBleed => 'Колонка · на всю висоту',
            self::BackgroundCover => 'Фон секції · заливка',
            self::AmbientRight => 'М’який акцент праворуч',
            self::StackBanner => 'Широка смуга під текстом',
            self::FreeObject => 'Вільний об’єкт (absolute)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ColumnCover => 'Фото в правій колонці, обрізка по рамці. Універсальний варіант.',
            self::ColumnContain => 'Фото повністю в рамці без обрізки, з полями.',
            self::ColumnBleed => 'Фото на всю висоту колонки без рамки, ефект editorial.',
            self::BackgroundCover => 'Зображення на весь блок, текст поверх затемнення.',
            self::AmbientRight => 'Напівпрозоре фото справа, як на головній NOEMA.',
            self::StackBanner => 'Широкий банер під заголовком (зручно для горизонтальних фото).',
            self::FreeObject => 'Фото як окремий шар: позиція X/Y, ширина та висота у %.',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $mode): array => [$mode->value => $mode->label()])
            ->all();
    }

    public static function tryFromContent(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::ColumnCover;
    }
}
