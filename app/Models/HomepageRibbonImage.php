<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageRibbonImage extends Model
{
    protected $fillable = [
        'path',
        'alt_text',
        'width',
        'height',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
