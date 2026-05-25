<?php

namespace App\Enums;

enum TtnStatus: string
{
    case Draft = 'draft';
    case Created = 'created';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Чернетка',
            self::Created => 'Створено',
            self::InTransit => 'В дорозі',
            self::Delivered => 'Доставлено',
            self::Cancelled => 'Скасовано',
        };
    }
}
