<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSeoSettings extends Model
{
    protected $fillable = [
        'site_name',
        'title_separator',
        'home_meta_title',
        'home_meta_description',
        'home_meta_keywords',
        'default_meta_description',
        'default_meta_keywords',
        'catalog_index_meta_title',
        'catalog_index_meta_description',
        'catalog_index_meta_keywords',
        'robots',
        'og_site_name',
        'og_locale',
        'og_default_title',
        'og_default_description',
        'og_default_image',
        'favicon_path',
        'apple_touch_icon_path',
        'twitter_site',
        'google_site_verification',
        'notes',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'site_name' => config('app.name', 'NOEMA'),
            'title_separator' => ' | ',
            'home_meta_title' => 'NOEMA — преміальний медичний одяг',
            'home_meta_description' => 'NOEMA — преміальний бренд медичного одягу для лікарів.',
            'home_meta_keywords' => 'NOEMA, медичний одяг, хірургічний костюм, медична форма',
            'catalog_index_meta_title' => 'Каталог',
            'catalog_index_meta_description' => 'Каталог медичного одягу NOEMA.',
            'robots' => 'index, follow',
            'og_site_name' => config('app.name', 'NOEMA'),
            'og_locale' => 'uk_UA',
        ]);
    }
}
