<?php

namespace App\Filament\Concerns;

use App\Support\MediaUrl;

trait NormalizesBunnyUploads
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    protected function formDataForBunnyUploads(array $data, array $keys): array
    {
        foreach ($keys as $key) {
            $path = MediaUrl::normalizePath($data[$key] ?? null);
            $data[$key] = filled($path) ? $path : null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $incoming
     * @param  list<string>  $keys
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    protected function persistBunnyUploads(array $incoming, array $keys, array $existing = []): array
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $incoming)) {
                continue;
            }

            $normalized = MediaUrl::normalizePath($incoming[$key]);

            $incoming[$key] = filled($normalized)
                ? $normalized
                : MediaUrl::normalizePath($existing[$key] ?? null);
        }

        return $incoming;
    }
}
