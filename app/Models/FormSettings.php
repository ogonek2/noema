<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class FormSettings extends Model
{
    protected $fillable = [
        'telegram_enabled',
        'telegram_bot_token',
        'telegram_chat_id',
        'notes',
        'consultation_enabled',
        'consultation_form_key',
        'consultation_title',
        'consultation_subtitle',
        'consultation_success_message',
        'consultation_fields',
    ];

    protected function casts(): array
    {
        return [
            'telegram_enabled' => 'boolean',
            'consultation_enabled' => 'boolean',
            'consultation_fields' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'consultation_enabled' => true,
            'consultation_form_key' => 'consultation',
            'consultation_title' => 'Консультація',
            'consultation_subtitle' => 'Залиште контакти — менеджер NOEMA звʼяжеться з вами найближчим часом.',
            'consultation_success_message' => 'Дякуємо! Ми звʼяжемося з вами найближчим часом.',
            'consultation_fields' => self::defaultConsultationFields(),
        ]);
    }

    /** @return list<array<string, mixed>> */
    public static function defaultConsultationFields(): array
    {
        return [
            [
                'key' => 'name',
                'label' => 'Імʼя',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Ваше імʼя',
                'width' => 'half',
                'sort_order' => 0,
            ],
            [
                'key' => 'phone',
                'label' => 'Телефон',
                'type' => 'tel',
                'required' => true,
                'placeholder' => '+380 (99) 999-99-99',
                'mask' => '+380 (99) 999-99-99',
                'width' => 'half',
                'sort_order' => 1,
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => false,
                'placeholder' => 'email@example.com',
                'width' => 'full',
                'sort_order' => 2,
            ],
            [
                'key' => 'message',
                'label' => 'Повідомлення',
                'type' => 'textarea',
                'required' => true,
                'placeholder' => 'Опишіть запит або питання',
                'width' => 'full',
                'sort_order' => 3,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function consultationSchema(): array
    {
        return app(\App\Services\LandingFormService::class)->normalizeFormContent([
            'form_key' => $this->consultation_form_key ?: 'consultation',
            'title' => $this->consultation_title ?: 'Консультація',
            'subtitle' => $this->consultation_subtitle,
            'success_message' => $this->consultation_success_message,
            'fields' => filled($this->consultation_fields) ? $this->consultation_fields : self::defaultConsultationFields(),
        ]);
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
        } catch (\Throwable $exception) {
            Log::error('form_settings.telegram_bot_token decrypt failed', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function hasTelegram(): bool
    {
        return $this->telegram_enabled
            && $this->hasValidTelegramBotToken()
            && filled($this->telegram_chat_id);
    }

    public function hasValidTelegramBotToken(): bool
    {
        $token = $this->telegram_bot_token;

        return is_string($token) && preg_match('/^\d+:[A-Za-z0-9_-]{20,}$/', $token) === 1;
    }
}
