<?php

namespace App\Support;

class RichContent
{
    /**
     * Render trusted HTML from admin (RichEditor). Plain-text fallback is escaped.
     */
    public static function render(?string $html, ?string $plainFallback = null): string
    {
        $content = trim($html ?? '');

        if ($content !== '') {
            return $content;
        }

        $fallback = trim($plainFallback ?? '');

        if ($fallback === '') {
            return '—';
        }

        return nl2br(e($fallback));
    }
}
