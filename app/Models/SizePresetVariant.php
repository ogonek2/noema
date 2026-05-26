<?php

namespace App\Models;

use App\Enums\ProductLength;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizePresetVariant extends Model
{
    protected $fillable = [
        'size_preset_id',
        'size',
        'length',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'length' => ProductLength::class,
            'sort_order' => 'integer',
        ];
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(SizePreset::class, 'size_preset_id');
    }
}
