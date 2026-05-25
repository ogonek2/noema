<?php

namespace App\Services;

use App\Models\FormSettings;
use App\Models\FormSubmission;
use App\Support\TelegramHttp;
use Illuminate\Support\Facades\Log;

class TelegramFormNotifier
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $payload
     */
    public function notify(FormSubmission $submission, array $schema, array $payload): bool
    {
        $settings = FormSettings::current();

        if (! $settings->hasTelegram()) {
            return false;
        }

        $lines = [
            '📩 <b>Нова заявка з форми</b>',
            '',
            '<b>'.e($schema['title'] ?? 'Форма').'</b>',
            'Сторінка: '.e($submission->landing_page_slug ?? '—'),
            'ID: #'.$submission->id,
            '',
        ];

        foreach ($schema['fields'] as $field) {
            $key = $field['key'];
            $label = e($field['label']);
            $value = $payload[$key] ?? null;

            if (is_bool($value)) {
                $value = $value ? 'Так' : 'Ні';
            }

            $lines[] = "<b>{$label}:</b> ".e((string) ($value ?? '—'));
        }

        $lines[] = '';
        $lines[] = '🕐 '.e($submission->created_at?->timezone(config('app.timezone'))->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i'));

        $text = implode("\n", $lines);

        try {
            $response = TelegramHttp::client()->post(
                'https://api.telegram.org/bot'.$settings->telegram_bot_token.'/sendMessage',
                [
                    'chat_id' => $settings->telegram_chat_id,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ],
            );

            return $response->successful();
        } catch (\Throwable $exception) {
            Log::warning('Telegram form notification failed', [
                'submission_id' => $submission->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
