<?php

namespace App\Services;

use App\Enums\LandingSectionType;
use App\Models\LandingPage;
use App\Models\LandingPageSection;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LandingPageService
{
    /** @return array<int, mixed> */
    public static function slugRules(?int $ignoreId = null): array
    {
        return [
            'required',
            'alpha_dash',
            'max:120',
            Rule::notIn(LandingPage::RESERVED_SLUGS),
            Rule::unique('landing_pages', 'slug')->ignore($ignoreId),
        ];
    }

    public function findPublished(string $slug): LandingPage
    {
        return LandingPage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /** @return Collection<int, LandingPageSection> */
    public function activeSections(LandingPage $page): Collection
    {
        return $page->activeSections()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (LandingPageSection $section): LandingPageSection => $this->hydrateSection($section));
    }

    public function hydrateSection(LandingPageSection $section): LandingPageSection
    {
        $section->content = $this->normalizeContent($section->type, $section->content ?? []);

        return $section;
    }

    /** @param  array<string, mixed>  $content */
    public function normalizeContent(LandingSectionType $type, array $content): array
    {
        $content = $this->normalizeMediaPaths($content);

        return match ($type) {
            LandingSectionType::Products => $this->normalizeProductsSection($content),
            LandingSectionType::Form => app(LandingFormService::class)->normalizeFormContent($content),
            default => $content,
        };
    }

    /** @param  array<string, mixed>  $content */
    private function normalizeProductsSection(array $content): array
    {
        $ids = collect($content['product_ids'] ?? [])
            ->filter(fn (mixed $id): bool => filled($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $content['product_ids'] = $ids->all();

        return $content;
    }

    /** @param  array<string, mixed>  $content */
    private function normalizeMediaPaths(array $content): array
    {
        $imageKeys = [
            'image',
            'image_path',
            'background_image',
            'hero_image',
        ];

        foreach ($imageKeys as $key) {
            if (array_key_exists($key, $content)) {
                $normalized = MediaUrl::normalizePath($content[$key]);
                $content[$key] = filled($normalized) ? $normalized : null;
            }
        }

        if (isset($content['images']) && is_array($content['images'])) {
            $content['images'] = collect($content['images'])
                ->map(function (mixed $item): array {
                    if (! is_array($item)) {
                        return ['path' => null, 'alt' => '', 'caption' => ''];
                    }

                    $path = MediaUrl::normalizePath($item['path'] ?? $item['image'] ?? null);

                    return [
                        'path' => filled($path) ? $path : null,
                        'alt' => (string) ($item['alt'] ?? ''),
                        'caption' => (string) ($item['caption'] ?? ''),
                    ];
                })
                ->all();
        }

        if (isset($content['items']) && is_array($content['items'])) {
            $content['items'] = collect($content['items'])
                ->map(function (mixed $item): array {
                    if (! is_array($item)) {
                        return [];
                    }

                    if (array_key_exists('image', $item)) {
                        $path = MediaUrl::normalizePath($item['image']);
                        $item['image'] = filled($path) ? $path : null;
                    }

                    return $item;
                })
                ->all();
        }

        return $content;
    }

    /** @param  array<string, mixed>  $content */
    public static function link(mixed $href, ?string $fallback = null): string
    {
        $href = is_string($href) ? trim($href) : '';

        if ($href !== '') {
            if (Str::startsWith($href, ['http://', 'https://', 'mailto:', 'tel:', '#', '/'])) {
                return $href;
            }

            return '/'.ltrim($href, '/');
        }

        return $fallback ?? '#';
    }
}
