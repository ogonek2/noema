<?php

namespace App\Support;

use App\Services\BunnyStorageService;

class MediaUrl
{
    public static function normalizePath(mixed $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (is_string($path)) {
            $trimmed = trim($path);

            if ($trimmed === '' || $trimmed === '[]') {
                return null;
            }

            if (str_starts_with($trimmed, 'livewire-file:')) {
                return null;
            }

            if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                $decoded = json_decode($trimmed, true);

                if (is_array($decoded)) {
                    return self::normalizePath($decoded);
                }
            }

            return $trimmed;
        }

        if (! is_array($path)) {
            return null;
        }

        if (isset($path['path']) && is_string($path['path'])) {
            return self::normalizePath($path['path']);
        }

        foreach ($path as $key => $value) {
            if ($key === 's' || $value === 'arr') {
                continue;
            }

            if (is_string($value) && str_starts_with($value, 'livewire-file:')) {
                continue;
            }

            $normalized = self::normalizePath($value);

            if (filled($normalized)) {
                return $normalized;
            }
        }

        return null;
    }

    public static function resolve(mixed $path, string $fallback = 'images/mask/m1.png'): string
    {
        $path = self::normalizePath($path);

        if (blank($path)) {
            return self::local($fallback);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cdnUrl = config('services.bunny.cdn_url');

        if (filled($cdnUrl)) {
            try {
                return app(BunnyStorageService::class)->publicUrl($path);
            } catch (\Throwable) {
                return self::local($fallback);
            }
        }

        return self::local($path);
    }

    public static function local(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return asset('storage/'.$normalized);
    }
}
