<?php

namespace App\Enums;

enum ShippingMethod: string
{
    case NovaPoshtaWarehouse = 'nova_poshta_warehouse';
    case NovaPoshtaCourier = 'nova_poshta_courier';
    case Ukrposhta = 'ukrposhta';
    case Meest = 'meest';

    public function label(): string
    {
        return match ($this) {
            self::NovaPoshtaWarehouse => 'Нова Пошта — відділення',
            self::NovaPoshtaCourier => 'Нова Пошта — курʼєр',
            self::Ukrposhta => 'Укрпошта',
            self::Meest => 'Meest Express',
        };
    }

    public function usesNovaPoshtaApi(): bool
    {
        return $this === self::NovaPoshtaWarehouse;
    }

    public function requiresManualAddress(): bool
    {
        return match ($this) {
            self::NovaPoshtaCourier, self::Ukrposhta, self::Meest => true,
            default => false,
        };
    }
}
