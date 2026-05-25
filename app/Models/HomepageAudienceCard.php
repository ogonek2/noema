<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageAudienceCard extends Model
{
    protected $fillable = [
        'name',
        'image_path',
        'href',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
