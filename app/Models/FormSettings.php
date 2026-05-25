<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class FormSettings extends Model
{
    protected $fillable = [
        'telegram_enabled',
        'telegram_bot_token',
        'telegram_chat_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'telegram_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function setTelegramBotTokenAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['telegram_bot_token'] = Crypt::encryptString($value);
    }

    public function getTelegramBotTokenAttribute(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function hasTelegram(): bool
    {
        return $this->telegram_enabled
            && filled($this->telegram_bot_token)
            && filled($this->telegram_chat_id);
    }
}
