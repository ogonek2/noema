<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class NovaPoshtaSettings extends Model
{
    protected $fillable = [
        'is_active',
        'api_key',
        'api_url',
        'verify_ssl',
        'timeout',
        'sender_ref',
        'contact_sender_ref',
        'city_sender_ref',
        'sender_address_ref',
        'sender_phone',
        'sender_name',
        'sender_warehouse_name',
        'default_weight',
        'default_seats',
        'default_description',
        'cargo_type',
        'payment_method',
        'payer_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'verify_ssl' => 'boolean',
            'default_weight' => 'decimal:2',
            'default_seats' => 'integer',
            'timeout' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'api_url' => config('services.nova_poshta.api_url'),
            'verify_ssl' => config('services.nova_poshta.verify_ssl', true),
            'timeout' => (int) config('services.nova_poshta.timeout', 20),
        ]);
    }

    public function setApiKeyAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['api_key'] = Crypt::encryptString($value);
    }

    public function getApiKeyAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function resolvedApiKey(): ?string
    {
        return $this->api_key ?: config('services.nova_poshta.api_key');
    }

    public function hasSenderConfigured(): bool
    {
        return filled($this->sender_ref)
            && filled($this->contact_sender_ref)
            && filled($this->city_sender_ref)
            && filled($this->sender_address_ref)
            && filled($this->sender_phone);
    }
}
