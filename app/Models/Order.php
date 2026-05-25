<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\TtnStatus;
use App\Services\OrderDashboardStatsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'number',
        'status',
        'payment_status',
        'payment_method',
        'liqpay_payment_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_method',
        'shipping_city_ref',
        'shipping_city_name',
        'shipping_warehouse_ref',
        'shipping_warehouse_name',
        'shipping_address',
        'subtotal',
        'shipping_cost',
        'total',
        'customer_notes',
        'internal_notes',
        'assigned_to',
        'ttn_number',
        'ttn_ref',
        'ttn_status',
        'shipment_weight',
        'shipment_seats',
        'session_id',
        'paid_at',
        'shipped_at',
        'completed_at',
        'cancelled_at',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => app(OrderDashboardStatsService::class)->flush());
        static::deleted(fn () => app(OrderDashboardStatsService::class)->flush());
    }

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'shipping_method' => ShippingMethod::class,
            'ttn_status' => TtnStatus::class,
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'shipment_weight' => 'decimal:2',
            'shipment_seats' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->latest();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function canCreateTtn(): bool
    {
        return $this->shipping_method->usesNovaPoshtaApi()
            && filled($this->shipping_city_ref)
            && (filled($this->shipping_warehouse_ref) || filled($this->shipping_address))
            && blank($this->ttn_number);
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }
}
