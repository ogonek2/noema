<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class TelegramHttp
{
    public static function client(int $timeout = 10): PendingRequest
    {
        $client = Http::timeout($timeout);

        if (! config('services.telegram.verify_ssl', true)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }
}
