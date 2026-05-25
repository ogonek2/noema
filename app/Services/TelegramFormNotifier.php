<?php

namespace App\Services;

use App\Models\FormSettings;
use App\Models\FormSubmission;
use App\Support\TelegramHttp;
use Illuminate\Support\Facades\Log;

class TelegramFormNotifier
{
    private ?string $lastError = null;

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $payload
     */
    public function notify(FormSubmission $submission, array $schema, array $payload): bool
    {
        $this->lastError = null;
        $settings = FormSettings::current();

        if (! $settings->hasTelegram()) {
            $this->lastError = $this->disabledReason($settings);
            Log::info('Telegram form notification skipped', [
                'submission_id' => $submission->id,
                'reason' => $this->lastError,
            ]);

            return false;
        }

        $token = $settings->telegram_bot_token;

        if (! $this->isValidBotToken($token)) {
            $this->lastError = 'Невірний токен бота. Відкрийте «Форми → Telegram», вставте токен з @BotFather і збережіть. Якщо токен уже збережений — перевірте APP_KEY на сервері (після зміни ключа треба ввести токен заново).';
            Log::warning('Telegram form notification: invalid bot token', [
                'submission_id' => $submission->id,
            ]);

            return false;
        }

        $lines = [
            '📩 <b>Нова заявка з форми</b>',
            '',
            '<b>'.e($schema['title'] ?? 'Форма').'</b>',
            'Сторінка: '.e($submission->landing_page_slug ?? 'Глобальна форма'),
            'ID: #'.$submission->id,
            '',
        ];

        $fields = $schema['fields'] ?? [];

        if ($fields !== []) {
            foreach ($fields as $field) {
                $key = $field['key'];
                $label = e($field['label']);
                $value = $payload[$key] ?? null;
                $lines[] = '<b>'.$label.':</b> '.e($this->formatTelegramValue($value));
            }
        } else {
            foreach ($payload as $key => $value) {
                $lines[] = '<b>'.e((string) $key).':</b> '.e($this->formatTelegramValue($value));
            }
        }

        $lines[] = '';
        $lines[] = '🕐 '.e($submission->created_at?->timezone(config('app.timezone'))->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i'));

        $text = implode("\n", $lines);

        try {
            $response = TelegramHttp::client()->post(
                'https://api.telegram.org/bot'.$token.'/sendMessage',
                [
                    'chat_id' => $settings->telegram_chat_id,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ],
            );

            if ($response->successful()) {
                return true;
            }

            $this->lastError = (string) ($response->json('description') ?? $response->body() ?: 'Telegram API error');
            Log::warning('Telegram form notification failed', [
                'submission_id' => $submission->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $exception) {
            $this->lastError = $exception->getMessage();
            Log::warning('Telegram form notification failed', [
                'submission_id' => $submission->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    private function disabledReason(FormSettings $settings): string
    {
        if (! $settings->telegram_enabled) {
            return 'Telegram вимкнено в налаштуваннях.';
        }

        if (blank($settings->telegram_chat_id)) {
            return 'Не вказано Chat ID.';
        }

        if (blank($settings->telegram_bot_token)) {
            return 'Токен бота відсутній або не розшифровується (перевірте APP_KEY на сервері та збережіть токен заново).';
        }

        return 'Telegram не налаштовано.';
    }

    private function isValidBotToken(?string $token): bool
    {
        return is_string($token) && preg_match('/^\d+:[A-Za-z0-9_-]{20,}$/', $token) === 1;
    }

    private function formatTelegramValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Так' : 'Ні';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—';
        }

        return (string) ($value ?? '—');
    }
}
