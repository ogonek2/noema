<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizePresetChartRow extends Model
{
    protected $fillable = [
        'size_preset_id',
        'size_label',
        'bust',
        'waist',
        'hip',
        'inseam',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(SizePreset::class, 'size_preset_id');
    }
}
