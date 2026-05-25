<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageBenefit extends Model
{
    protected $fillable = [
        'number_label',
        'title',
        'text',
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
