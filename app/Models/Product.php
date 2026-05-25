<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'catalog_id',
        'model_slug',
        'name',
        'slug',
        'sku',
        'subtitle',
        'color_name',
        'color_slug',
        'color_hex',
        'short_description',
        'description',
        'price',
        'compare_at_price',
        'primary_image_path',
        'fit_summary',
        'fit_details',
        'fabric_summary',
        'fabric_details',
        'care_instructions',
        'size_chart_intro',
        'length_guide',
        'is_active',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_path',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order');
    }

    public function detailItems(): HasMany
    {
        return $this->hasMany(ProductDetailItem::class)->orderBy('sort_order');
    }

    public function sizeChartRows(): HasMany
    {
        return $this->hasMany(SizeChartRow::class)->orderBy('sort_order');
    }

    public function relations(): HasMany
    {
        return $this->hasMany(ProductRelation::class)->orderBy('sort_order');
    }

    public function customizationOptions(): HasMany
    {
        return $this->hasMany(ProductCustomizationOption::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function primaryImage(): ?ProductImage
    {
        if ($this->relationLoaded('images')) {
            return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        }

        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->orderBy('sort_order')->first();
    }

    public function imageUrl(): string
    {
        $primary = $this->primaryImage();

        if ($primary) {
            return $primary->url();
        }

        return MediaUrl::resolve($this->primary_image_path);
    }

    /** @return list<string> */
    public function galleryUrls(): array
    {
        $images = $this->relationLoaded('images')
            ? $this->images
            : $this->images()->get();

        $primary = $images->firstWhere('is_primary', true);
        $ordered = $primary
            ? collect([$primary])->merge($images->where('id', '!=', $primary->id)->sortBy('sort_order'))
            : $images->sortBy('sort_order');

        $urls = $ordered
            ->map(fn (ProductImage $image): string => $image->url())
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($urls !== []) {
            return $urls;
        }

        return [MediaUrl::resolve($this->primary_image_path)];
    }
}
