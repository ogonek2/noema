<?php

namespace App\Support;

class LandingProse
{
    public static function render(?string $html, ?string $plainFallback = null): string
    {
        $content = RichContent::render($html, $plainFallback);

        if ($content === '—' || ! str_contains($content, '<table')) {
            return $content;
        }

        return self::wrapTables($content);
    }

    public static function wrapTables(string $html): string
    {
        return (string) preg_replace(
            '/<table\b([^>]*)>(.*?)<\/table>/is',
            '<div class="landing-prose__table-wrap"><table$1>$2</table></div>',
            $html,
        );
    }
}
