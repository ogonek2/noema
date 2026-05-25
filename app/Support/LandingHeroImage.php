<?php

namespace App\Support;

use App\Enums\LandingHeroImageFocal;
use App\Enums\LandingHeroImageMode;

class LandingHeroImage
{
    /** @param  array<string, mixed>  $content */
    public static function mode(array $content): LandingHeroImageMode
    {
        return LandingHeroImageMode::tryFromContent($content['image_mode'] ?? null);
    }

    /** @param  array<string, mixed>  $content */
    public static function focal(array $content): LandingHeroImageFocal
    {
        return LandingHeroImageFocal::tryFromContent($content['image_focal'] ?? null);
    }

    /** @param  array<string, mixed>  $content */
    public static function cssClass(array $content): string
    {
        return 'landing-hero--img-'.self::mode($content)->value;
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, string>
     */
    public static function objectStyles(array $content): array
    {
        $focal = self::focal($content)->objectPosition();

        return [
            'left' => self::clampPercent($content['img_pos_x'] ?? 72, 0, 100).'%',
            'top' => self::clampPercent($content['img_pos_y'] ?? 50, 0, 100).'%',
            'width' => self::clampPercent($content['img_width'] ?? 42, 8, 100).'%',
            'height' => self::clampPercent($content['img_height'] ?? 78, 8, 100).'%',
            'opacity' => (string) (self::clampPercent($content['img_opacity'] ?? 100, 0, 100) / 100),
            'object-fit' => in_array($content['img_fit'] ?? 'cover', ['cover', 'contain', 'fill', 'none'], true)
                ? (string) $content['img_fit']
                : 'cover',
            'object-position' => $focal,
        ];
    }

    /** @param  array<string, mixed>  $content */
    public static function usesBackground(array $content): bool
    {
        return self::mode($content) === LandingHeroImageMode::BackgroundCover;
    }

    /** @param  array<string, mixed>  $content */
    public static function showsMediaColumn(array $content): bool
    {
        $mode = self::mode($content);

        return ! in_array($mode, [
            LandingHeroImageMode::BackgroundCover,
            LandingHeroImageMode::AmbientRight,
            LandingHeroImageMode::FreeObject,
        ], true);
    }

    private static function clampPercent(mixed $value, int $min, int $max): float
    {
        $number = is_numeric($value) ? (float) $value : (float) $min;

        return max($min, min($max, $number));
    }
}
