<?php

namespace App\Models;

use App\Enums\HomepageBlockSlug;
use Illuminate\Database\Eloquent\Model;

class HomepageBlock extends Model
{
    protected $fillable = [
        'slug',
        'label',
        'content',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function forSlug(HomepageBlockSlug $slug): self
    {
        return static::query()->firstOrCreate(
            ['slug' => $slug->value],
            ['label' => $slug->label(), 'content' => []],
        );
    }
}
