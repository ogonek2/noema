<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageReview extends Model
{
    protected $fillable = [
        'quote',
        'author_name',
        'author_role',
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
