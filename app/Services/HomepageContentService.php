<?php

namespace App\Services;

use App\Enums\HomepageBlockSlug;
use App\Models\HomepageAudienceCard;
use App\Models\HomepageBenefit;
use App\Models\HomepageBlock;
use App\Models\HomepageGlobals;
use App\Models\HomepageReview;
use App\Models\HomepageRibbonImage;
use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HomepageContentService
{
    public function __construct(private readonly StorefrontService $storefront) {}

    public function isInstalled(): bool
    {
        return Schema::hasTable('homepage_globals')
            && Schema::hasTable('homepage_blocks');
    }

    /** @return array<string, mixed> */
    public function adminPayload(): array
    {
        if (! $this->isInstalled()) {
            abort(503, 'Таблиці контенту головної не створені. Запустіть: php artisan migrate --force');
        }

        $this->ensureDefaults();

        $products = Product::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return [
            'blocks' => HomepageBlock::query()->orderBy('id')->get()->map(fn (HomepageBlock $block) => [
                'slug' => $block->slug,
                'label' => $block->label,
                'content' => $block->content ?? [],
                'is_active' => $block->is_active,
            ])->values()->all(),
            'globals' => $this->globalsPayload(),
            'reviews' => $this->reviewsQuery()->get(),
            'audience_cards' => HomepageAudienceCard::query()->orderBy('sort_order')->get(),
            'benefits' => HomepageBenefit::query()->orderBy('sort_order')->get(),
            'ribbon_images' => HomepageRibbonImage::query()->orderBy('sort_order')->get(),
            'products' => $products,
        ];
    }

    /** @return array<string, mixed> */
    public function globalsPayload(): array
    {
        if (! $this->isInstalled()) {
            return [
                'spotlight_product_id' => null,
                'featured_product_ids' => [],
                'use_catalog_audience' => true,
            ];
        }

        $globals = HomepageGlobals::current();

        return [
            'spotlight_product_id' => $globals->spotlight_product_id,
            'featured_product_ids' => $globals->featured_product_ids ?? [],
            'use_catalog_audience' => $globals->use_catalog_audience,
        ];
    }

    public function block(HomepageBlockSlug $slug): HomepageBlock
    {
        $this->ensureDefaults();

        return HomepageBlock::forSlug($slug);
    }

    /** @param  array<string, mixed>  $content */
    public function updateBlock(HomepageBlockSlug $slug, array $content, ?bool $isActive = null): HomepageBlock
    {
        $block = HomepageBlock::forSlug($slug);
        $block->content = $this->normalizeBlockContent($content);

        if ($isActive !== null) {
            $block->is_active = $isActive;
        }

        $block->save();

        return $block;
    }

    /** @param  array<string, mixed>  $data */
    public function updateGlobals(array $data): HomepageGlobals
    {
        $globals = HomepageGlobals::current();
        $globals->fill([
            'spotlight_product_id' => $data['spotlight_product_id'] ?? null,
            'featured_product_ids' => $data['featured_product_ids'] ?? [],
            'use_catalog_audience' => (bool) ($data['use_catalog_audience'] ?? true),
        ]);
        $globals->save();

        return $globals;
    }

    /** @return EloquentCollection<int, Product> */
    public function featuredProducts(): EloquentCollection
    {
        if (! $this->isInstalled()) {
            return $this->storefront->featuredProducts();
        }

        $globals = HomepageGlobals::current();
        $ids = collect($globals->featured_product_ids)->filter()->values();

        if ($ids->isNotEmpty()) {
            return Product::query()
                ->active()
                ->whereIn('id', $ids)
                ->with([
                    'catalog:id,name,slug',
                    'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->limit(8),
                ])
                ->get()
                ->sortBy(fn (Product $product) => $ids->search($product->id) ?? PHP_INT_MAX)
                ->values();
        }

        return $this->storefront->featuredProducts();
    }

    public function spotlightProduct(): ?Product
    {
        if (! $this->isInstalled()) {
            return $this->storefront->featuredProducts()->first();
        }

        $globals = HomepageGlobals::current();

        if ($globals->spotlight_product_id) {
            return Product::query()
                ->active()
                ->with([
                    'catalog:id,name,slug',
                    'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->limit(8),
                ])
                ->find($globals->spotlight_product_id);
        }

        return $this->featuredProducts()->first();
    }

    /** @return array<string, mixed> */
    public function blockContent(HomepageBlockSlug $slug): array
    {
        if (! $this->isInstalled()) {
            return $this->defaultBlockContent($slug);
        }

        $block = $this->block($slug);

        if (! $block->is_active) {
            return $this->defaultBlockContent($slug);
        }

        return $this->normalizeBlockContent(
            array_replace_recursive($this->defaultBlockContent($slug), $block->content ?? []),
        );
    }

    /** @param  array<string, mixed>  $content */
    public function normalizeBlockContent(array $content): array
    {
        $imageKeys = ['hero_image', 'fallback_image', 'image_path', 'path'];

        foreach ($content as $key => $value) {
            if (in_array($key, $imageKeys, true)) {
                $normalized = MediaUrl::normalizePath($value);
                $content[$key] = $key === 'fallback_image' && $normalized === 'images/cloth.png'
                    ? null
                    : $normalized;

                continue;
            }

            if (is_array($value)) {
                $content[$key] = $this->normalizeBlockContent($value);
            }
        }

        return $content;
    }

    /** @return Collection<int, array{quote: string, name: string, role: string}> */
    public function reviews(): Collection
    {
        if (! $this->isInstalled()) {
            return $this->defaultReviews();
        }

        $this->ensureDefaults();

        $items = $this->reviewsQuery()->get();

        if ($items->isEmpty()) {
            return $this->defaultReviews();
        }

        return $items->map(fn (HomepageReview $review) => [
            'quote' => $review->quote,
            'name' => $review->author_name,
            'role' => $review->author_role ?? '',
        ]);
    }

    /** @return Collection<int, array{name: string, image: string, href: string}> */
    public function audienceCards(): Collection
    {
        if (! $this->isInstalled()) {
            return $this->storefront->audienceCards();
        }

        $globals = HomepageGlobals::current();

        if ($globals->use_catalog_audience) {
            return $this->storefront->audienceCards();
        }

        $this->ensureDefaults();

        $cards = HomepageAudienceCard::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($cards->isEmpty()) {
            return $this->storefront->audienceCards();
        }

        return $cards->map(fn (HomepageAudienceCard $card) => [
            'name' => $card->name,
            'image' => MediaUrl::resolve($card->image_path, 'images/audience/a1.png'),
            'href' => $card->href ?: route('catalog.index'),
        ]);
    }

    /** @return list<array{n: string, title: string, text: string}> */
    public function benefits(?Product $spotlight = null): array
    {
        if (! $this->isInstalled()) {
            return $this->defaultBenefits($spotlight);
        }

        $this->ensureDefaults();

        $items = HomepageBenefit::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty()) {
            return $this->defaultBenefits($spotlight);
        }

        return $items->map(fn (HomepageBenefit $benefit) => [
            'n' => $benefit->number_label,
            'title' => $benefit->title,
            'text' => $benefit->text ?? '',
        ])->all();
    }

    /** @return Collection<int, array{url: string, alt: string, width: int, height: int}> */
    public function ribbonGalleryItems(): Collection
    {
        if (! $this->isInstalled()) {
            return $this->storefront->ribbonGalleryItems();
        }

        $this->ensureDefaults();

        $images = HomepageRibbonImage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($images->isNotEmpty()) {
            return $images->map(fn (HomepageRibbonImage $image) => [
                'url' => MediaUrl::resolve($image->path),
                'alt' => $image->alt_text ?? 'NOEMA gallery',
                'width' => $image->width,
                'height' => $image->height,
            ]);
        }

        return $this->storefront->ribbonGalleryItems();
    }

    public function ensureDefaults(): void
    {
        if (! $this->isInstalled()) {
            return;
        }

        foreach (HomepageBlockSlug::cases() as $slug) {
            HomepageBlock::forSlug($slug);
        }

        HomepageGlobals::current();

        if (HomepageReview::query()->doesntExist()) {
            foreach ($this->defaultReviews() as $index => $review) {
                HomepageReview::query()->create([
                    'quote' => $review['quote'],
                    'author_name' => $review['name'],
                    'author_role' => $review['role'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        if (HomepageBenefit::query()->doesntExist()) {
            foreach ($this->defaultBenefits() as $index => $benefit) {
                HomepageBenefit::query()->create([
                    'number_label' => $benefit['n'],
                    'title' => $benefit['title'],
                    'text' => $benefit['text'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function defaultBlockContent(HomepageBlockSlug $slug): array
    {
        return match ($slug) {
            HomepageBlockSlug::Hero => [
                'tagline' => 'преміальний бренд одягу для лікарів',
                'hero_image' => 'images/women.png',
                'side_link_label' => "МЕДИЧНИЙ\nОДЯГ",
                'side_link_href' => route('catalog.index'),
                'footer_tagline' => 'ПРЕМІУМ КОСТЮМИ ДЛЯ МЕДИКІВ',
                'scroll_hint' => 'ВНИЗ',
                'instagram_url' => 'https://www.instagram.com/noema.ua/',
                'facebook_url' => 'https://www.facebook.com/noema.ua/',
                'tiktok_url' => 'https://www.tiktok.com/@noema.ua',
            ],
            HomepageBlockSlug::AboutUs => [
                'badge' => '[ NOEMA ]',
                'title_line1' => 'Про',
                'title_line2' => 'бренд',
                'paragraph_1' => 'NOEMA — преміальний бренд медичного одягу для лікарів, які працюють довгі зміни і цінують посадку, тканину та стриманий професійний вигляд.',
                'paragraph_2' => 'Ми поєднуємо еластичні тканини, міцні шви та мінімалістичний дизайн, щоб форма працювала стільки ж, скільки й ви.',
                'cta_primary' => 'Обрати костюм',
                'cta_primary_href' => '',
                'cta_secondary' => 'Каталог',
                'cta_secondary_href' => '',
                'footer_note' => 'Преміум костюми для медиків — від комплектів до аксесуарів. Доставка по Україні.',
            ],
            HomepageBlockSlug::ProductBox => [
                'title' => 'Продукт',
                'catalog_label' => '[ КАТАЛОГ ]',
                'catalog_href' => '',
                'made_with' => 'Made with Noema',
                'use_product_fallback' => true,
                'prepend_product_fabric_tag' => true,
                'headline' => '',
                'subtitle' => '',
                'fabric_tags' => [],
                'column_left_text' => '',
                'column_left_caption' => '',
                'column_right_text' => '',
                'column_right_caption' => '',
                'cta_primary_label' => 'Обрати костюм',
                'cta_primary_link_product' => true,
                'cta_primary_href' => '',
                'cta_secondary_label' => 'Каталог',
                'cta_secondary_href' => '',
            ],
            HomepageBlockSlug::Benefits => [
                'title_line1' => 'Наші',
                'title_line2' => 'переваги',
                'badge' => '[ NOEMA ]',
                'description_fallback' => 'NOEMA створює медичний одяг, який витримує інтенсивні зміни: стійкість до прання, збереження форми та комфорт протягом усього дня.',
                'made_with' => 'Made with Noema',
                'fallback_image' => null,
            ],
            HomepageBlockSlug::Statement => [
                'brand_title' => 'NOEMA',
                'quote_fallback' => 'Преміальні медичні костюми для тих, хто тримає відповідальність за життя — з комфортом, посадкою та деталями, які відчуваються з першої зміни.',
                'made_with' => 'Made with Noema',
            ],
            HomepageBlockSlug::Footer => [
                'description' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.",
                'cta_primary' => 'Обрати костюм',
                'cta_primary_href' => '',
                'cta_secondary' => 'Консультація',
                'cta_secondary_href' => '',
                'made_with' => 'Made with Noema',
                'phone_1' => '+380 (99) 999 99-99',
                'phone_2' => '+380 (99) 999 99-99',
                'email' => 'office@email.com',
                'office_title' => 'Офіс',
                'office_address' => 'Вул. Гетьмана Сагайдачного, 15-Б — Корпус А, Кв-1234',
                'partners_title' => 'Партнери',
                'partners_address' => 'Вул. Жилянська 107. Вхід біля пиріжкової «Тітка Клара» — 2 поверх',
                'copyright' => 'Всі права захищені NOEMA',
                'footer_groups' => [
                    [
                        'title' => 'Навігатор',
                        'items' => array_map(fn (array $item): array => [
                            'type' => 'link',
                            'label' => $item['label'],
                            'href' => $item['href'],
                            'new_tab' => false,
                        ], $this->defaultNavigatorLinks()),
                    ],
                    [
                        'title' => 'Контакти',
                        'items' => [
                            ['type' => 'link', 'label' => '+380 (99) 999 99-99', 'href' => 'tel:380999999999', 'new_tab' => false],
                            ['type' => 'link', 'label' => '+380 (99) 999 99-99', 'href' => 'tel:380999999999', 'new_tab' => false],
                            ['type' => 'link', 'label' => 'office@email.com', 'href' => 'mailto:office@email.com', 'new_tab' => false],
                            ['type' => 'text', 'label' => 'Офіс', 'href' => '', 'new_tab' => false],
                            ['type' => 'text', 'label' => 'Вул. Гетьмана Сагайдачного, 15-Б — Корпус А, Кв-1234', 'href' => '', 'new_tab' => false],
                        ],
                    ],
                ],
                'footer_bottom_items' => [
                    ['type' => 'link', 'label' => 'Публічна оферта', 'href' => '#', 'new_tab' => false],
                    ['type' => 'link', 'label' => 'Умови використання', 'href' => '#', 'new_tab' => false],
                    ['type' => 'link', 'label' => 'Умови повернення', 'href' => '#', 'new_tab' => false],
                    ['type' => 'link', 'label' => 'Політика конфіденційності', 'href' => '#', 'new_tab' => false],
                ],
                'legal_links' => [
                    ['label' => 'Публічна оферта', 'href' => '#'],
                    ['label' => 'Умови використання', 'href' => '#'],
                    ['label' => 'Умови повернення', 'href' => '#'],
                    ['label' => 'Політика конфіденційності', 'href' => '#'],
                ],
                'navigator_links' => $this->defaultNavigatorLinks(),
            ],
            HomepageBlockSlug::Navigator => [
                'links' => [
                    ['label' => 'ПРО БРЕНД', 'href' => route('home').'#about-us'],
                    ['label' => 'КАТАЛОГ', 'href' => route('catalog.index')],
                    ['label' => 'ПРОДУКТ', 'href' => route('home').'#product'],
                    ['label' => 'ПЕРЕВАГИ', 'href' => '#benefits'],
                    ['label' => 'ДЛЯ КОГО', 'href' => '#audience'],
                    ['label' => 'ВІДГУКИ', 'href' => '#reviews'],
                ],
            ],
        };
    }

    /** @return list<array{label: string, href: string}> */
    public function defaultNavigatorLinks(): array
    {
        $home = route('home');

        return [
            ['label' => 'Про Нас \\ Хто ми', 'href' => $home.'#about-us'],
            ['label' => 'Каталог', 'href' => route('catalog.index')],
            ['label' => 'Продукт та опис', 'href' => $home.'#product'],
            ['label' => 'Переваги наших костюмів', 'href' => $home.'#benefits'],
            ['label' => 'Для кого ми виготовляємо', 'href' => $home.'#audience'],
            ['label' => 'Відгуки про нас', 'href' => $home.'#reviews'],
            ['label' => 'Каталог та галерея', 'href' => $home.'#ribbon'],
            ['label' => 'FAQ \\ Додаткові питання', 'href' => $home.'#statement'],
        ];
    }

    /** @return Collection<int, array{quote: string, name: string, role: string}> */
    private function defaultReviews(): Collection
    {
        return collect([
            ['quote' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.", 'name' => 'Андрій К.', 'role' => 'Стоматолог'],
            ['quote' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.", 'name' => 'Олена М.', 'role' => 'Хірург'],
            ['quote' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.", 'name' => 'Ігор В.', 'role' => 'Косметолог'],
            ['quote' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.", 'name' => 'Марія С.', 'role' => 'Ветеринар'],
            ['quote' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.", 'name' => 'Петро Л.', 'role' => 'Лаборант'],
            ['quote' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.", 'name' => 'Наталія Р.', 'role' => 'Стоматолог'],
        ]);
    }

    /** @return list<array{n: string, title: string, text: string}> */
    private function defaultBenefits(?Product $spotlight = null): array
    {
        return [
            ['n' => '1.', 'title' => 'Довговічність', 'text' => 'Служить від 3 до 12 років'],
            ['n' => '2.', 'title' => 'Комфорт', 'text' => 'Понад 12+ годинних змін'],
            ['n' => '3.', 'title' => 'Свобода рухів', 'text' => $spotlight?->fit_summary ?? 'Еластичні тканини'],
            ['n' => '4.', 'title' => 'Мінімалістичний дизайн', 'text' => 'Чистий, професійний вигляд'],
            ['n' => '5.', 'title' => 'Унісекс посадка', 'text' => 'Підходить для різних силуетів'],
            ['n' => '6.', 'title' => 'Tall / Small розміри', 'text' => $spotlight?->length_guide ?? 'Ідеальна посадка для будь-якого зросту'],
        ];
    }

    private function reviewsQuery()
    {
        return HomepageReview::query()
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
