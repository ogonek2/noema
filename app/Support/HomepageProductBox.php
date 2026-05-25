<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HomepageProductBox
{
    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function resolve(array $content, ?Product $spotlight): array
    {
        $useFallback = (bool) ($content['use_product_fallback'] ?? true);

        $headline = self::text($content['headline'] ?? null, $useFallback ? $spotlight?->short_description : null)
            ?? 'Хірургічні костюми, які витримують вашу роботу';

        $subtitle = self::text($content['subtitle'] ?? null, $useFallback ? $spotlight?->subtitle : null)
            ?? 'Служать до 10 років';

        $columnLeft = self::text($content['column_left_text'] ?? null, $useFallback && $spotlight?->description
            ? Str::limit(strip_tags($spotlight->description), 320)
            : null) ?? 'Преміальні медичні костюми NOEMA поєднують еластичність, стійкість до прання та стриманий професійний силует.';

        $columnLeftCaption = self::text($content['column_left_caption'] ?? null, $useFallback ? $spotlight?->fit_summary : null)
            ?? 'Посадка та тканина';

        $columnRight = self::text($content['column_right_text'] ?? null, $useFallback && $spotlight?->fabric_details
            ? Str::limit(strip_tags($spotlight->fabric_details), 320)
            : ($useFallback ? $spotlight?->fabric_summary : null))
            ?? 'Тканина розроблена для 12+ годинних змін: дихає, не мнеться, легко доглядається.';

        $columnRightCaption = self::text($content['column_right_caption'] ?? null, $useFallback ? $spotlight?->fabric_summary : null)
            ?? 'FIONx™';

        $fabricTags = self::fabricTags($content, $spotlight);

        $primaryHref = ($content['cta_primary_link_product'] ?? true) && $spotlight
            ? route('product.show', $spotlight)
            : (filled($content['cta_primary_href'] ?? null) ? $content['cta_primary_href'] : route('catalog.index'));

        $secondaryHref = filled($content['cta_secondary_href'] ?? null)
            ? $content['cta_secondary_href']
            : route('catalog.index');

        return [
            'title' => $content['title'] ?? 'Продукт',
            'catalog_label' => $content['catalog_label'] ?? '[ КАТАЛОГ ]',
            'catalog_href' => filled($content['catalog_href'] ?? null) ? $content['catalog_href'] : route('catalog.index'),
            'made_with' => $content['made_with'] ?? 'Made with Noema',
            'headline' => $headline,
            'subtitle' => $subtitle,
            'fabric_tags' => $fabricTags,
            'column_left_text' => $columnLeft,
            'column_left_caption' => $columnLeftCaption,
            'column_right_text' => $columnRight,
            'column_right_caption' => $columnRightCaption,
            'cta_primary_label' => $content['cta_primary_label'] ?? 'Обрати костюм',
            'cta_primary_href' => $primaryHref,
            'cta_secondary_label' => $content['cta_secondary_label'] ?? 'Каталог',
            'cta_secondary_href' => $secondaryHref,
        ];
    }

    /** @param  array<string, mixed>  $content */
    public static function fabricTags(array $content, ?Product $spotlight): Collection
    {
        $custom = collect($content['fabric_tags'] ?? [])
            ->map(function (mixed $tag): ?string {
                if (is_string($tag)) {
                    return trim($tag);
                }

                if (is_array($tag)) {
                    return trim((string) ($tag['label'] ?? $tag['text'] ?? ''));
                }

                return null;
            })
            ->filter();

        if ($custom->isNotEmpty()) {
            $tags = $custom;

            if (($content['prepend_product_fabric_tag'] ?? true) && filled($spotlight?->fabric_summary)) {
                $tags = collect([$spotlight->fabric_summary])->merge($tags);
            }

            return $tags->unique()->take(6)->values();
        }

        return collect([
            ($content['prepend_product_fabric_tag'] ?? true) ? $spotlight?->fabric_summary : null,
            'Тягнеться',
            'Легко прасується',
            'Дихає',
            'Не мнеться',
        ])->filter()->unique()->take(6)->values();
    }

    private static function text(?string $custom, ?string $fromProduct): ?string
    {
        if (filled($custom)) {
            return trim($custom);
        }

        return filled($fromProduct) ? trim($fromProduct) : null;
    }
}
