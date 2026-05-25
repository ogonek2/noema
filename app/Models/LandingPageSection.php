<?php

namespace App\Models;

use App\Enums\LandingSectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageSection extends Model
{
    protected $fillable = [
        'landing_page_id',
        'type',
        'admin_label',
        'content',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => LandingSectionType::class,
            'content' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<LandingPage, $this> */
    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function displayLabel(): string
    {
        if (filled($this->admin_label)) {
            return $this->admin_label;
        }

        return $this->type->label();
    }
}
