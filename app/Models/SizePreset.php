<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SizePreset extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'size_chart_intro',
        'length_guide',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (SizePreset $preset): void {
            if (blank($preset->slug) && filled($preset->name)) {
                $preset->slug = Str::slug($preset->name);
            }
        });
    }

    public function chartRows(): HasMany
    {
        return $this->hasMany(SizePresetChartRow::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(SizePresetVariant::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
