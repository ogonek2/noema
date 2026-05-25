<?php

namespace App\Models;

use App\Enums\CustomizationOptionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductCustomizationOption extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'slug',
        'description',
        'type',
        'options',
        'price_delta',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => CustomizationOptionType::class,
            'options' => 'array',
            'price_delta' => 'decimal:2',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductCustomizationOption $option): void {
            if (blank($option->slug) && filled($option->name)) {
                $option->slug = Str::slug($option->name);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
