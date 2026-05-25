<?php

namespace App\Models;

use App\Services\BunnyStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (ProductImage $image): void {
            if (! $image->is_primary) {
                return;
            }

            static::query()
                ->where('product_id', $image->product_id)
                ->whereKeyNot($image->id)
                ->update(['is_primary' => false]);

            $image->product()->update([
                'primary_image_path' => $image->path,
            ]);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function url(): string
    {
        return app(BunnyStorageService::class)->publicUrl($this->path);
    }
}
