<?php

namespace App\Services;

use App\Models\Catalog;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\SiteSeoSettings;
use App\Support\MediaUrl;
use App\Support\SeoMeta;
use Illuminate\Support\Str;

class SiteSeoService
{
    private ?SiteSeoSettings $settings = null;

    public function settings(): SiteSeoSettings
    {
        return $this->settings ??= SiteSeoSettings::current();
    }

    public function forHome(): SeoMeta
    {
        $settings = $this->settings();

        $title = $settings->home_meta_title ?: $this->siteName();
        $description = $settings->home_meta_description ?: $settings->default_meta_description;

        return $this->make([
            'title' => $title,
            'description' => $description,
            'keywords' => $settings->home_meta_keywords ?: $settings->default_meta_keywords,
            'canonical' => route('home'),
            'ogType' => 'website',
            'ogTitle' => $settings->og_default_title ?: $title,
            'ogDescription' => $settings->og_default_description ?: $description,
            'ogImage' => $settings->og_default_image,
            'jsonLd' => [
                $this->organizationJsonLd(),
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $this->siteName(),
                    'url' => route('home'),
                ],
            ],
        ]);
    }

    public function forCatalogIndex(): SeoMeta
    {
        $settings = $this->settings();

        $title = $this->formatTitle($settings->catalog_index_meta_title ?: 'Каталог');

        return $this->make([
            'title' => $title,
            'description' => $settings->catalog_index_meta_description ?: $settings->default_meta_description,
            'keywords' => $settings->catalog_index_meta_keywords ?: $settings->default_meta_keywords,
            'canonical' => route('catalog.index'),
            'ogTitle' => $title,
            'ogDescription' => $settings->catalog_index_meta_description ?: $settings->default_meta_description,
            'ogImage' => $settings->og_default_image,
        ]);
    }

    public function forCatalog(Catalog $catalog): SeoMeta
    {
        $settings = $this->settings();
        $title = $this->formatTitle($catalog->meta_title ?: $catalog->name);
        $description = $catalog->meta_description ?: Str::limit(strip_tags((string) $catalog->description), 160);
        $image = $catalog->og_image_path ?: $catalog->image_path ?: $settings->og_default_image;

        return $this->make([
            'title' => $title,
            'description' => $description,
            'keywords' => $catalog->meta_keywords ?: $settings->default_meta_keywords,
            'canonical' => route('catalog.show', $catalog),
            'ogTitle' => $title,
            'ogDescription' => $description,
            'ogImage' => $image,
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => $catalog->name,
                    'description' => $description,
                    'url' => route('catalog.show', $catalog),
                ],
            ],
        ]);
    }

    public function forProduct(Product $product): SeoMeta
    {
        $settings = $this->settings();
        $title = $product->meta_title ?: $this->formatTitle($product->name);
        $description = $product->meta_description ?: $product->short_description ?: $settings->default_meta_description;
        $image = $product->og_image_path ?: $product->primary_image_path ?: $settings->og_default_image;
        $keywords = $product->meta_keywords ?: $this->defaultProductKeywords($product, $settings);

        return $this->make([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => route('product.show', $product),
            'ogType' => 'product',
            'ogTitle' => $title,
            'ogDescription' => $description,
            'ogImage' => $image,
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Product',
                    'name' => $product->name,
                    'description' => strip_tags((string) $description),
                    'image' => MediaUrl::resolve($image, 'images/mask/m1.png'),
                    'sku' => $product->sku,
                    'brand' => [
                        '@type' => 'Brand',
                        'name' => $this->siteName(),
                    ],
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => route('product.show', $product),
                        'priceCurrency' => 'UAH',
                        'price' => (string) $product->price,
                        'availability' => 'https://schema.org/InStock',
                    ],
                ],
            ],
        ]);
    }

    public function forLanding(LandingPage $page): SeoMeta
    {
        $settings = $this->settings();
        $title = $page->meta_title ?: $this->formatTitle($page->title);
        $description = $page->meta_description ?: $settings->default_meta_description;

        return $this->make([
            'title' => $title,
            'description' => $description,
            'keywords' => $page->meta_keywords ?: $settings->default_meta_keywords,
            'canonical' => $page->publicUrl(),
            'ogTitle' => $title,
            'ogDescription' => $description,
            'ogImage' => $page->og_image_path ?: $settings->og_default_image,
            'ogType' => 'article',
            'jsonLd' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebPage',
                    'name' => $page->title,
                    'description' => $description,
                    'url' => $page->publicUrl(),
                ],
            ],
        ]);
    }

    public function forUtilityPage(string $title, ?string $canonical = null, bool $noindex = true): SeoMeta
    {
        $settings = $this->settings();
        $formattedTitle = $this->formatTitle($title);

        return $this->make([
            'title' => $formattedTitle,
            'description' => $settings->default_meta_description,
            'keywords' => $settings->default_meta_keywords,
            'canonical' => $canonical,
            'robots' => $noindex ? 'noindex, nofollow' : $settings->robots,
            'ogTitle' => $formattedTitle,
            'ogDescription' => $settings->default_meta_description,
            'ogImage' => $settings->og_default_image,
        ]);
    }

    /** @param  array<string, mixed>  $data */
    private function make(array $data): SeoMeta
    {
        $settings = $this->settings();
        $canonical = $data['canonical'] ?? null;
        $ogImage = $this->resolveImage($data['ogImage'] ?? $settings->og_default_image);

        return new SeoMeta(
            title: (string) ($data['title'] ?? $this->siteName()),
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            keywords: filled($data['keywords'] ?? null) ? (string) $data['keywords'] : null,
            canonical: $canonical,
            robots: $data['robots'] ?? $settings->robots,
            ogType: (string) ($data['ogType'] ?? 'website'),
            ogTitle: (string) ($data['ogTitle'] ?? $data['title'] ?? $this->siteName()),
            ogDescription: filled($data['ogDescription'] ?? $data['description'] ?? null)
                ? (string) ($data['ogDescription'] ?? $data['description'])
                : null,
            ogImage: $ogImage,
            ogUrl: $canonical,
            ogSiteName: $settings->og_site_name ?: $this->siteName(),
            ogLocale: $settings->og_locale ?: 'uk_UA',
            twitterSite: $settings->twitter_site,
            googleSiteVerification: $settings->google_site_verification,
            faviconUrl: $this->resolveImage($settings->favicon_path),
            appleTouchIconUrl: $this->resolveImage($settings->apple_touch_icon_path ?: $settings->favicon_path),
            jsonLd: $data['jsonLd'] ?? null,
        );
    }

    public function formatTitle(?string $title): string
    {
        $siteName = $this->siteName();
        $separator = $this->settings()->title_separator ?: ' | ';

        if (blank($title)) {
            return $siteName;
        }

        if (str_contains($title, $siteName)) {
            return $title;
        }

        return $title.$separator.$siteName;
    }

    public function siteName(): string
    {
        return $this->settings()->site_name ?: config('app.name', 'NOEMA');
    }

    private function resolveImage(mixed $path): ?string
    {
        $normalized = MediaUrl::normalizePath($path);

        if (blank($normalized)) {
            return null;
        }

        return MediaUrl::resolve($normalized);
    }

    private function defaultProductKeywords(Product $product, SiteSeoSettings $settings): ?string
    {
        $parts = array_filter([
            $this->siteName(),
            $product->name,
            $product->catalog?->name,
            $product->color_name,
            'медичний одяг',
        ]);

        return $parts !== [] ? implode(', ', $parts) : $settings->default_meta_keywords;
    }

    /** @return array<string, mixed> */
    private function organizationJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->siteName(),
            'url' => route('home'),
            'logo' => $this->resolveImage($this->settings()->og_default_image),
        ];
    }
}
