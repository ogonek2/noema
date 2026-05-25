<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Очікує оплати',
            self::Paid => 'Оплачено',
            self::Processing => 'В обробці',
            self::Shipped => 'Відправлено',
            self::Completed => 'Завершено',
            self::Cancelled => 'Скасовано',
        };
    }
}
