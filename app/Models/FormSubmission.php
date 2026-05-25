<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_key',
        'landing_page_id',
        'landing_page_section_id',
        'landing_page_slug',
        'form_title',
        'payload',
        'ip_address',
        'user_agent',
        'referer',
        'telegram_sent',
        'telegram_error',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'telegram_sent' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<LandingPage, $this> */
    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    /** @return array<string, mixed> */
    public function payloadArray(): array
    {
        $payload = $this->getAttribute('payload');

        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload) && $payload !== '') {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
