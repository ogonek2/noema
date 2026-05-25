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
            if (str_starts_with($path, 'livewire-file:')) {
                return null;
            }

            return $path;
        }

        if (! is_array($path)) {
            return null;
        }

        if (isset($path['path']) && is_string($path['path'])) {
            return $path['path'];
        }

        foreach ($path as $value) {
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
