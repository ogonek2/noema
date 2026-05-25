<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageGlobals extends Model
{
    protected $table = 'homepage_globals';

    protected $fillable = [
        'spotlight_product_id',
        'featured_product_ids',
        'use_catalog_audience',
    ];

    protected function casts(): array
    {
        return [
            'featured_product_ids' => 'array',
            'use_catalog_audience' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function spotlightProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'spotlight_product_id');
    }
}
