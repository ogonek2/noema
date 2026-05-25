<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPage extends Model
{
    /** @var list<string> */
    public const RESERVED_SLUGS = [
        'admin',
        'api',
        'cart',
        'catalog',
        'checkout',
        'product',
        'p',
        'login',
        'register',
    ];

    protected $fillable = [
        'slug',
        'title',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_path',
        'is_published',
        'published_at',
        'show_navigator',
        'show_footer',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'show_navigator' => 'boolean',
            'show_footer' => 'boolean',
        ];
    }

    /** @return HasMany<LandingPageSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(LandingPageSection::class)->orderBy('sort_order');
    }

    /** @return HasMany<LandingPageSection, $this> */
    public function activeSections(): HasMany
    {
        return $this->sections()->where('is_active', true);
    }

    /** @param  Builder<LandingPage>  $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function publicUrl(): string
    {
        return route('landing.show', $this);
    }

    /**
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?static
    {
        return static::query()
            ->published()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->first();
    }
}
