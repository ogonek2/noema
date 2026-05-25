<?php

namespace Database\Seeders;

use App\Enums\CustomizationOptionType;
use App\Enums\ProductLength;
use App\Enums\ProductRelationType;
use App\Models\Catalog;
use App\Models\Product;
use App\Models\ProductCustomizationOption;
use App\Models\ProductDetailItem;
use App\Models\ProductRelation;
use App\Models\ProductVariant;
use App\Models\SizeChartRow;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private const PRICE = 100.00;

    /** @var list<string> */
    private const SIZES = ['XS', 'S', 'M', 'L', 'XL'];

    /** @var list<array{name: string, slug: string, hex: string}> */
    private const COLOR_POOL = [
        ['name' => 'Чорний', 'slug' => 'black', 'hex' => '#1A1A1A'],
        ['name' => 'Графіт', 'slug' => 'graphite', 'hex' => '#4A4F57'],
        ['name' => 'Смарагд', 'slug' => 'emerald', 'hex' => '#0F5C4A'],
        ['name' => 'Білий', 'slug' => 'white', 'hex' => '#F5F5F0'],
        ['name' => 'Ніч', 'slug' => 'midnight', 'hex' => '#1E2A3A'],
        ['name' => 'Пудра', 'slug' => 'blush', 'hex' => '#E8C4C0'],
        ['name' => 'Синій', 'slug' => 'navy', 'hex' => '#1B2A4A'],
        ['name' => 'Олива', 'slug' => 'olive', 'hex' => '#3D4F3A'],
        ['name' => 'Бордо', 'slug' => 'burgundy', 'hex' => '#5C1F2E'],
        ['name' => 'Сірий', 'slug' => 'slate', 'hex' => '#5C6570'],
    ];

    /** @var list<array{model: string, title: string, subtitle: string, featured: bool}> */
    private const MODELS = [
        ['model' => 'celestia', 'title' => 'Celestia', 'subtitle' => '4-way stretch · antimicrobial', 'featured' => true],
        ['model' => 'nova', 'title' => 'Nova', 'subtitle' => 'легка тканина · slim fit', 'featured' => true],
        ['model' => 'atlas', 'title' => 'Atlas', 'subtitle' => 'classic fit · reinforced knees', 'featured' => true],
        ['model' => 'forge', 'title' => 'Forge', 'subtitle' => 'athletic fit', 'featured' => false],
        ['model' => 'aria', 'title' => 'Aria', 'subtitle' => 'мандриновий комір', 'featured' => false],
        ['model' => 'helix', 'title' => 'Helix', 'subtitle' => 'терморегуляція', 'featured' => false],
    ];

    public function run(): void
    {
        $catalog = Catalog::query()->where('slug', 'hirurgichni-kostyumy')->firstOrFail();

        $createdByModel = [];

        foreach (self::MODELS as $index => $model) {
            $colors = collect(self::COLOR_POOL)->shuffle()->take(random_int(3, 5))->values();
            $modelProducts = [];

            foreach ($colors as $colorIndex => $color) {
                $slug = $model['model'].'-'.$color['slug'];
                $product = Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'catalog_id' => $catalog->id,
                        'model_slug' => $model['model'],
                        'name' => $model['title'].' — '.$color['name'],
                        'sku' => 'NM-SUR-'.strtoupper($model['model']).'-'.strtoupper($color['slug']),
                        'subtitle' => $model['subtitle'],
                        'color_name' => $color['name'],
                        'color_slug' => $color['slug'],
                        'color_hex' => $color['hex'],
                        'short_description' => 'Хірургічний костюм '.$model['title'].' у кольорі '.$color['name'].'. Стійкий до прання, еластичний, для змін 12+ годин.',
                        'description' => 'Комплект '.$model['title'].' — преміальний хірургічний костюм NOEMA. Колір: '.$color['name'].'. Тканина FIONx™ зберігає форму та колір після десятків циклів прання.',
                        'price' => self::PRICE,
                        'compare_at_price' => null,
                        'fit_summary' => 'Прямий професійний крій',
                        'fabric_summary' => '77% поліестер, 23% спандекс',
                        'fabric_details' => 'Дихаюча еластична тканина з антимікробним покриттям. Підходить для операційної та стаціонару.',
                        'care_instructions' => 'Прати при 40°C, не відбілювати, сушити на повітрі.',
                        'size_chart_intro' => 'Виміри в сантиметрах.',
                        'is_active' => true,
                        'is_featured' => $model['featured'] && $colorIndex === 0,
                        'sort_order' => ($index + 1) * 10 + $colorIndex,
                        'meta_title' => $model['title'].' '.$color['name'].' | NOEMA',
                        'meta_description' => 'Хірургічний костюм '.$model['title'].' — '.$color['name'].', $100',
                    ],
                );

                $this->seedSizes($product);
                $this->seedDetails($product);
                $this->seedSizeChart($product);

                $modelProducts[] = $product;
            }

            $createdByModel[$model['model']] = $modelProducts;
        }

        $this->linkColorAlternatives($createdByModel);
        $this->linkCrossModelSuggestions($createdByModel);
        $this->seedCustomizations($createdByModel);
    }

    private function seedSizes(Product $product): void
    {
        foreach (self::SIZES as $index => $size) {
            ProductVariant::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sku' => $product->sku.'-'.$size,
                ],
                [
                    'name' => $size,
                    'size' => $size,
                    'length' => ProductLength::Regular,
                    'price' => self::PRICE,
                    'stock_quantity' => random_int(8, 35),
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ],
            );
        }
    }

    private function seedDetails(Product $product): void
    {
        $items = [
            ['label' => 'Кишені', 'content' => '6 функціональних кишень'],
            ['label' => 'Призначення', 'content' => 'Хірургія, стаціонар, амбулаторія'],
            ['label' => 'Колір', 'content' => $product->color_name],
        ];

        foreach ($items as $index => $item) {
            ProductDetailItem::query()->updateOrCreate(
                ['product_id' => $product->id, 'label' => $item['label']],
                ['content' => $item['content'], 'sort_order' => ($index + 1) * 10],
            );
        }
    }

    private function seedSizeChart(Product $product): void
    {
        $rows = [
            ['size_label' => 'XS', 'bust' => '80–84', 'waist' => '60–64', 'hip' => '86–90', 'inseam' => '76'],
            ['size_label' => 'S', 'bust' => '84–88', 'waist' => '64–68', 'hip' => '90–94', 'inseam' => '77'],
            ['size_label' => 'M', 'bust' => '88–92', 'waist' => '68–72', 'hip' => '94–98', 'inseam' => '78'],
            ['size_label' => 'L', 'bust' => '92–96', 'waist' => '72–76', 'hip' => '98–102', 'inseam' => '79'],
            ['size_label' => 'XL', 'bust' => '96–100', 'waist' => '76–80', 'hip' => '102–106', 'inseam' => '80'],
        ];

        foreach ($rows as $index => $row) {
            SizeChartRow::query()->updateOrCreate(
                ['product_id' => $product->id, 'size_label' => $row['size_label']],
                [...$row, 'sort_order' => ($index + 1) * 10],
            );
        }
    }

    /**
     * @param  array<string, list<Product>>  $createdByModel
     */
    private function linkColorAlternatives(array $createdByModel): void
    {
        foreach ($createdByModel as $products) {
            foreach ($products as $product) {
                foreach ($products as $related) {
                    if ($product->id === $related->id) {
                        continue;
                    }

                    ProductRelation::query()->updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'related_product_id' => $related->id,
                            'type' => ProductRelationType::Alternative->value,
                        ],
                        ['sort_order' => 10],
                    );
                }
            }
        }
    }

    /**
     * @param  array<string, list<Product>>  $createdByModel
     */
    /**
     * @param  array<string, list<Product>>  $createdByModel
     */
    private function seedCustomizations(array $createdByModel): void
    {
        foreach ($createdByModel as $products) {
            $product = $products[0] ?? null;

            if (! $product) {
                continue;
            }

            ProductCustomizationOption::query()->updateOrCreate(
                ['product_id' => $product->id, 'slug' => 'extra-pockets'],
                [
                    'name' => 'Додаткові кишені',
                    'description' => 'Вкажіть розташування та кількість додаткових кишень.',
                    'type' => CustomizationOptionType::Checkbox,
                    'price_delta' => 15,
                    'is_required' => false,
                    'is_active' => true,
                    'sort_order' => 10,
                ],
            );

            ProductCustomizationOption::query()->updateOrCreate(
                ['product_id' => $product->id, 'slug' => 'tailoring'],
                [
                    'name' => 'Індивідуальний пошив',
                    'description' => 'Корекція довжини рукава, штанини або талії.',
                    'type' => CustomizationOptionType::Select,
                    'options' => [
                        'standard' => 'Стандарт',
                        'short-sleeve' => 'Коротший рукав',
                        'long-pants' => 'Подовжені штанини',
                        'full-custom' => 'Повний пошив під замовлення',
                    ],
                    'price_delta' => 25,
                    'is_required' => false,
                    'is_active' => true,
                    'sort_order' => 20,
                ],
            );

            ProductCustomizationOption::query()->updateOrCreate(
                ['product_id' => $product->id, 'slug' => 'embroidery'],
                [
                    'name' => 'Вишивка / нанесення',
                    'description' => 'Імʼя, логотип клініки або ініціали.',
                    'type' => CustomizationOptionType::Text,
                    'price_delta' => 20,
                    'is_required' => false,
                    'is_active' => true,
                    'sort_order' => 30,
                ],
            );
        }
    }

    /**
     * @param  array<string, list<Product>>  $createdByModel
     */
    private function linkCrossModelSuggestions(array $createdByModel): void
    {
        $anchors = collect($createdByModel)
            ->map(fn (array $products): ?Product => $products[0] ?? null)
            ->filter()
            ->values();

        foreach ($anchors as $index => $product) {
            $others = $anchors->except($index)->values();

            foreach ($others->take(2) as $offset => $alternative) {
                ProductRelation::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'related_product_id' => $alternative->id,
                        'type' => ProductRelationType::Alternative->value,
                    ],
                    ['sort_order' => ($offset + 1) * 10],
                );
            }

            if ($related = $others->get($index % max(1, $others->count()))) {
                ProductRelation::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'related_product_id' => $related->id,
                        'type' => ProductRelationType::Related->value,
                    ],
                    ['sort_order' => 10],
                );
            }

            if ($upsell = $others->get(($index + 1) % max(1, $others->count()))) {
                ProductRelation::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'related_product_id' => $upsell->id,
                        'type' => ProductRelationType::Upsell->value,
                    ],
                    ['sort_order' => 10],
                );
            }
        }
    }
}
