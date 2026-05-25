<?php

namespace App\Services;

use App\Enums\ProductRelationType;
use App\Models\Catalog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRelation;
use App\Support\MediaUrl;
use App\Support\PriceFormat;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorefrontService
{
    /** @return EloquentCollection<int, Product> */
    public function featuredProducts(int $limit = 8): EloquentCollection
    {
        return Product::query()
            ->active()
            ->featured()
            ->with([
                'catalog:id,name,slug',
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->limit(8),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /** @return EloquentCollection<int, Catalog> */
    public function activeCatalogs(): EloquentCollection
    {
        return Catalog::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('sort_order')
            ->get();
    }

    public function findProduct(string $slug): Product
    {
        return Product::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'catalog',
                'variants' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order'),
                'detailItems' => fn ($query) => $query->orderBy('sort_order'),
                'sizeChartRows' => fn ($query) => $query->orderBy('sort_order'),
                'customizationOptions' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'relations.relatedProduct' => fn ($query) => $query
                    ->active()
                    ->with(['catalog:id,name,slug', 'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order')->limit(1)]),
            ])
            ->firstOrFail();
    }

    public function findCatalog(string $slug): Catalog
    {
        return Catalog::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function catalogProducts(Catalog $catalog, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->active()
            ->where('catalog_id', $catalog->id)
            ->with(['images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order')->limit(8)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @return EloquentCollection<int, Product> */
    public function modelColorVariants(Product $product): EloquentCollection
    {
        if (! $product->model_slug) {
            return new EloquentCollection([$product]);
        }

        return Product::query()
            ->active()
            ->where('model_slug', $product->model_slug)
            ->with([
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->limit(1),
            ])
            ->orderBy('sort_order')
            ->orderBy('color_name')
            ->get();
    }

    public function modelDisplayName(Product $product): string
    {
        $fromName = Str::before($product->name, ' — ');

        if ($fromName !== '') {
            return $fromName;
        }

        if ($product->model_slug) {
            return Str::title(str_replace('-', ' ', $product->model_slug));
        }

        return $product->name;
    }

    /** @return list<array{slug: string, name: string, hex: ?string, image: ?string, url: string, is_current: bool}> */
    public function modelColorAlternativesPayload(Product $product): array
    {
        return $this->modelColorVariants($product)
            ->map(fn (Product $variant): array => [
                'slug' => $variant->slug,
                'name' => $variant->color_name,
                'hex' => $variant->color_hex,
                'image' => $variant->imageUrl(),
                'url' => route('product.show', $variant),
                'is_current' => $variant->slug === $product->slug,
            ])
            ->values()
            ->all();
    }

    /** @return EloquentCollection<int, Product> */
    public function colorAlternatives(Product $product): EloquentCollection
    {
        $byModel = $this->modelColorVariants($product);

        if ($byModel->count() > 1) {
            return $byModel;
        }

        $outgoing = $product->relations
            ->where('type', ProductRelationType::Alternative)
            ->pluck('related_product_id');

        $incoming = ProductRelation::query()
            ->where('related_product_id', $product->id)
            ->where('type', ProductRelationType::Alternative)
            ->pluck('product_id');

        $ids = collect([$product->id])
            ->merge($outgoing)
            ->merge($incoming)
            ->unique()
            ->values();

        if ($ids->count() <= 1) {
            return new EloquentCollection([$product]);
        }

        return Product::query()
            ->active()
            ->whereIn('id', $ids)
            ->with([
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->limit(1),
            ])
            ->orderBy('sort_order')
            ->orderBy('color_name')
            ->get();
    }

    /** @return EloquentCollection<int, Product> */
    public function similarProducts(Product $product, int $limit = 6): EloquentCollection
    {
        $excludeIds = Product::query()
            ->when(
                $product->model_slug,
                fn ($query) => $query->where('model_slug', $product->model_slug),
                fn ($query) => $query->whereKey($product->id),
            )
            ->pluck('id')
            ->push($product->id)
            ->unique()
            ->values();

        $imageConstraint = fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->limit(8);

        $sameCatalog = Product::query()
            ->active()
            ->where('catalog_id', $product->catalog_id)
            ->whereNotIn('id', $excludeIds)
            ->with(['catalog:id,name,slug', 'images' => $imageConstraint])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        if ($sameCatalog->count() >= $limit) {
            return $sameCatalog;
        }

        $remaining = $limit - $sameCatalog->count();
        $otherCatalog = Product::query()
            ->active()
            ->where('catalog_id', '!=', $product->catalog_id)
            ->whereNotIn('id', $excludeIds->merge($sameCatalog->pluck('id')))
            ->with(['catalog:id,name,slug', 'images' => $imageConstraint])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($remaining)
            ->get();

        return $sameCatalog->merge($otherCatalog);
    }

    /** @return array<string, mixed> */
    public function productPayload(Product $product): array
    {
        $product = $this->findProduct($product->slug);

        return [
            'slug' => $product->slug,
            'url' => route('product.show', $product),
            'name' => $product->name,
            'subtitle' => $product->subtitle,
            'short_description' => $product->short_description,
            'price' => PriceFormat::usd($product->price),
            'price_raw' => (float) $product->price,
            'compare_at_price' => $product->compare_at_price ? PriceFormat::usd($product->compare_at_price) : null,
            'gallery' => $product->galleryUrls(),
            'color' => [
                'name' => $product->color_name,
                'slug' => $product->color_slug,
                'hex' => $product->color_hex,
            ],
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'size' => $variant->size,
                'price' => (float) ($variant->price ?? $product->price),
                'stock' => $variant->stock_quantity,
            ])->values()->all(),
            'sizes' => $product->variants->pluck('size')->filter()->unique()->values()->all(),
            'description' => $product->description,
            'fit_summary' => $product->fit_summary,
            'fit_details' => $product->fit_details,
            'length_guide' => $product->length_guide,
            'fabric_summary' => $product->fabric_summary,
            'fabric_details' => $product->fabric_details,
            'care_instructions' => $product->care_instructions,
            'detail_items' => $product->detailItems->map(fn ($item) => [
                'label' => $item->label,
                'content' => $item->content,
            ])->values()->all(),
            'size_chart' => $product->sizeChartRows->map(fn ($row) => [
                'size_label' => $row->size_label,
                'bust' => $row->bust,
                'waist' => $row->waist,
                'hip' => $row->hip,
                'inseam' => $row->inseam,
            ])->values()->all(),
            'size_chart_intro' => $product->size_chart_intro,
            'meta_title' => $product->meta_title ?? $product->name.' | NOEMA',
            'model_name' => $this->modelDisplayName($product),
            'color_alternatives' => $this->modelColorAlternativesPayload($product),
        ];
    }

    /** @return list<array{slug: string, name: string, hex: ?string, image: ?string, is_current: bool}> */
    public function cartColorAlternativesPayload(Product $product): array
    {
        return $this->colorAlternatives($product)
            ->map(fn (Product $alternative): array => [
                'slug' => $alternative->slug,
                'name' => $alternative->color_name,
                'hex' => $alternative->color_hex,
                'image' => $alternative->imageUrl(),
                'is_current' => $alternative->id === $product->id,
            ])
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    public function cartConfigPayload(Product $product): array
    {
        $product = $this->findProduct($product->slug);

        return [
            'slug' => $product->slug,
            'name' => $product->name,
            'subtitle' => $product->subtitle,
            'image' => $product->imageUrl(),
            'color_name' => $product->color_name,
            'color_hex' => $product->color_hex,
            'model_name' => $this->modelDisplayName($product),
            'price' => (float) $product->price,
            'price_formatted' => PriceFormat::usd($product->price),
            'url' => route('product.show', $product),
            'color_alternatives' => $this->cartColorAlternativesPayload($product),
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'size' => $variant->size,
                'length' => $variant->length?->value,
                'price' => (float) ($variant->price ?? $product->price),
                'price_formatted' => PriceFormat::usd($variant->price ?? $product->price),
                'stock' => $variant->stock_quantity,
            ])->values()->all(),
            'sizes' => $product->variants->pluck('size')->filter()->unique()->values()->all(),
            'customizations' => $product->customizationOptions->map(fn ($option) => [
                'slug' => $option->slug,
                'name' => $option->name,
                'description' => $option->description,
                'type' => $option->type->value,
                'options' => $option->options ?? [],
                'price_delta' => (float) $option->price_delta,
                'price_delta_formatted' => $option->price_delta > 0
                    ? '+'.PriceFormat::usd($option->price_delta)
                    : null,
                'is_required' => $option->is_required,
            ])->values()->all(),
        ];
    }

    /** @return Collection<int, array{url: string, alt: string, width: int, height: int}> */
    public function ribbonGalleryItems(int $limit = 24): Collection
    {
        $fromDb = ProductImage::query()
            ->with('product:id,name')
            ->whereHas('product', fn ($q) => $q->active())
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->map(fn (ProductImage $image): array => [
                'url' => $image->url(),
                'alt' => $image->alt_text ?? $image->product?->name ?? 'NOEMA',
                'width' => 900,
                'height' => 1200,
            ]);

        if ($fromDb->isNotEmpty()) {
            return $fromDb;
        }

        return collect(Storage::disk('public')->files('gallery'))
            ->filter(fn (string $file): bool => (bool) preg_match('/\.(jpe?g|png|webp|gif)$/i', $file))
            ->sort()
            ->take($limit)
            ->map(function (string $file): array {
                $fullPath = Storage::disk('public')->path($file);
                $size = @getimagesize($fullPath) ?: [900, 1200];

                return [
                    'url' => MediaUrl::local($file),
                    'alt' => pathinfo($file, PATHINFO_FILENAME),
                    'width' => (int) $size[0],
                    'height' => (int) $size[1],
                ];
            })
            ->values();
    }

    /** @return array<string, Collection<int, Product>> */
    public function groupedRelations(Product $product): array
    {
        $grouped = $product->relations
            ->groupBy(fn ($relation) => $relation->type->value);

        $alternatives = $grouped->get(ProductRelationType::Alternative->value, collect())
            ->pluck('relatedProduct')
            ->filter()
            ->reject(fn (Product $related) => $related->model_slug === $product->model_slug);

        return [
            ProductRelationType::Alternative->value => $alternatives,
            ProductRelationType::Related->value => $grouped->get(ProductRelationType::Related->value, collect())->pluck('relatedProduct')->filter(),
            ProductRelationType::Upsell->value => $grouped->get(ProductRelationType::Upsell->value, collect())->pluck('relatedProduct')->filter(),
        ];
    }

    /** @return Collection<int, array{name: string, image: string, href: string}> */
    public function audienceCards(): Collection
    {
        return $this->activeCatalogs()->map(function (Catalog $catalog, int $index): array {
            $fallbacks = [
                'images/audience/a1.png',
                'images/audience/a2.png',
                'images/audience/a3.png',
                'images/audience/a4.png',
                'images/audience/a5.png',
            ];

            return [
                'name' => $catalog->name,
                'image' => MediaUrl::resolve($catalog->image_path, $fallbacks[$index % count($fallbacks)]),
                'href' => route('catalog.show', $catalog),
            ];
        });
    }
}
