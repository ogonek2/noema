<?php

namespace App\Enums;

enum OrderEventType: string
{
    case StatusChange = 'status_change';
    case PaymentChange = 'payment_change';
    case Note = 'note';
    case TtnCreated = 'ttn_created';
    case TtnFailed = 'ttn_failed';
    case Assigned = 'assigned';

    public function label(): string
    {
        return match ($this) {
            self::StatusChange => 'Зміна статусу',
            self::PaymentChange => 'Оплата',
            self::Note => 'Коментар',
            self::TtnCreated => 'ТТН створено',
            self::TtnFailed => 'Помилка ТТН',
            self::Assigned => 'Призначення',
        };
    }
}
