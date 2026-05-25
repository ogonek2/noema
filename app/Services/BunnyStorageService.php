<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BunnyStorageService
{
    public function list(string $path = ''): array
    {
        $response = $this->client()->get($this->buildPath($path));

        if (! $response->successful()) {
            throw new RuntimeException('Bunny list failed: '.$response->status().' '.$response->body());
        }

        return $response->json() ?? [];
    }

    public function upload(string $path, string $contents, string $contentType = 'application/octet-stream'): bool
    {
        $response = $this->client()->send('PUT', $this->buildPath($path), [
            'headers' => ['Content-Type' => $contentType],
            'body' => $contents,
        ]);

        return $response->successful();
    }

    public function download(string $path): string
    {
        $response = $this->client()->get($this->buildPath($path));

        if (! $response->successful()) {
            throw new RuntimeException('Bunny download failed: '.$response->status().' '.$response->body());
        }

        return $response->body();
    }

    public function delete(string $path): bool
    {
        $response = $this->client()->delete($this->buildPath($path));

        return $response->successful();
    }

    public function exists(string $path): bool
    {
        $normalized = ltrim($path, '/');
        $directory = trim(dirname($normalized), '.');
        $fileName = basename($normalized);

        if ($fileName === '' || $fileName === '.') {
            return false;
        }

        $items = $this->list($directory === '' ? '' : $directory.'/');

        foreach ($items as $item) {
            if (($item['ObjectName'] ?? null) === $fileName && ! ($item['IsDirectory'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    public function mimeTypeForPath(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }

    public function publicUrl(string $path): string
    {
        $cdnBase = rtrim((string) config('services.bunny.cdn_url', ''), '/');

        if ($cdnBase === '') {
            throw new RuntimeException('BUNNY_CDN_URL is not configured.');
        }

        return $cdnBase.'/'.ltrim($path, '/');
    }

    private function client(): PendingRequest
    {
        $apiBase = rtrim((string) config('services.bunny.storage_api_url'), '/');
        $storageName = (string) config('services.bunny.storage_name');
        $accessKey = (string) config('services.bunny.storage_password');

        if ($storageName === '' || $accessKey === '') {
            throw new RuntimeException('Bunny credentials are not configured.');
        }

        $request = Http::baseUrl($apiBase.'/'.$storageName)
            ->withHeaders(['AccessKey' => $accessKey, 'Accept' => 'application/json'])
            ->retry(
                (int) config('services.bunny.retry_times', 3),
                (int) config('services.bunny.retry_sleep_ms', 300)
            )
            ->timeout((int) config('services.bunny.timeout', 20));

        if (! config('services.bunny.verify_ssl', true)) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    private function buildPath(string $path): string
    {
        return '/'.ltrim($path, '/');
    }
}
