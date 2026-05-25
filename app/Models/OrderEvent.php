<?php

namespace App\Models;

use App\Enums\OrderEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEvent extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'from_status',
        'to_status',
        'body',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrderEventType::class,
            'meta' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
