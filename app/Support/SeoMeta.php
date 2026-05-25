<?php

namespace App\Support;

readonly class SeoMeta
{
    /**
     * @param  list<array<string, mixed>>|null  $jsonLd
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $keywords = null,
        public ?string $canonical = null,
        public ?string $robots = null,
        public string $ogType = 'website',
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $ogImage = null,
        public ?string $ogUrl = null,
        public ?string $ogSiteName = null,
        public ?string $ogLocale = null,
        public ?string $twitterCard = 'summary_large_image',
        public ?string $twitterSite = null,
        public ?string $googleSiteVerification = null,
        public ?string $faviconUrl = null,
        public ?string $appleTouchIconUrl = null,
        public ?array $jsonLd = null,
    ) {}
}
