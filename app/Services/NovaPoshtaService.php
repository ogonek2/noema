<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Models\NovaPoshtaSettings;
use App\Models\Order;
use App\Support\CustomerName;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NovaPoshtaService
{
    public function isConfigured(): bool
    {
        return filled($this->apiKey());
    }

    public function settings(): NovaPoshtaSettings
    {
        return NovaPoshtaSettings::current();
    }

    /**
     * @return list<array{ref: string, name: string, area: ?string}>
     */
    public function searchCities(string $query, int $limit = 25): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return [];
        }

        $cacheKey = 'nova_poshta.cities.'.md5(mb_strtolower($query).'.'.$limit);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $merged = [];
        $lastError = null;

        try {
            foreach ($this->searchSettlements($query, $limit) as $item) {
                $merged[$item['ref']] = $item;
            }
        } catch (RuntimeException $exception) {
            $lastError = $exception;
        }

        try {
            foreach ($this->searchCitiesLegacy($query, $limit) as $item) {
                $merged[$item['ref']] = $item;
            }
        } catch (RuntimeException $exception) {
            $lastError = $exception;
        }

        $results = array_values($merged);

        if ($results === [] && $lastError !== null) {
            throw $lastError;
        }

        if ($results !== []) {
            Cache::put($cacheKey, $results, now()->addHours(12));
        }

        return $results;
    }

    /**
     * @return list<array{ref: string, name: string, number: ?string, address: ?string}>
     */
    public function searchWarehouses(string $cityRef, string $query, int $limit = 30): array
    {
        if ($cityRef === '' || mb_strlen(trim($query)) < 2) {
            return [];
        }

        $query = trim($query);
        $cacheKey = 'nova_poshta.wh.search.'.md5($cityRef.'.'.mb_strtolower($query).'.'.$limit);

        return Cache::remember($cacheKey, now()->addHours(4), function () use ($cityRef, $query, $limit): array {
            return $this->mapWarehouses(
                $this->request('Address', 'getWarehouses', [
                    'CityRef' => $cityRef,
                    'FindByString' => $query,
                    'Limit' => $limit,
                ]),
            );
        });
    }

    /** @return array{ref: string, number: string, cost_on_site: ?float, estimated_delivery: ?string} */
    public function createTtnForOrder(Order $order, ?array $overrides = null): array
    {
        if (! $order->canCreateTtn()) {
            throw new RuntimeException('Для цього замовлення неможливо створити ТТН (перевірте доставку НП та наявність адреси).');
        }

        $settings = $this->settings();

        if (! $settings->hasSenderConfigured()) {
            throw new RuntimeException('Налаштуйте відправника в адмінці: CRM → Нова Пошта (API та відправник).');
        }

        $name = CustomerName::split($order->customer_name);
        $phone = CustomerName::normalizePhone($order->customer_phone);
        $weight = (float) ($overrides['weight'] ?? $order->shipment_weight ?? $settings->default_weight ?? 1);
        $seats = (int) ($overrides['seats'] ?? $order->shipment_seats ?? $settings->default_seats ?? 1);
        $cost = (float) ($overrides['cost'] ?? $order->total);
        $description = (string) ($overrides['description'] ?? $settings->default_description ?? 'Товар NOEMA');

        $serviceType = match ($order->shipping_method) {
            ShippingMethod::NovaPoshtaCourier => 'WarehouseDoors',
            default => 'WarehouseWarehouse',
        };

        $recipientAddress = $order->shipping_method === ShippingMethod::NovaPoshtaWarehouse
            ? $order->shipping_warehouse_ref
            : $order->shipping_address;

        if (blank($recipientAddress)) {
            throw new RuntimeException('Не вказано відділення або адресу доставки.');
        }

        $params = [
            'Sender' => $settings->sender_ref,
            'ContactSender' => $settings->contact_sender_ref,
            'CitySender' => $settings->city_sender_ref,
            'SenderAddress' => $settings->sender_address_ref,
            'SendersPhone' => $settings->sender_phone,
            'RecipientName' => $name['full'],
            'RecipientsPhone' => $phone,
            'CityRecipient' => $order->shipping_city_ref,
            'RecipientAddress' => $recipientAddress,
            'ServiceType' => $serviceType,
            'PaymentMethod' => $settings->payment_method ?? 'NonCash',
            'PayerType' => $settings->payer_type ?? 'Recipient',
            'CargoType' => $settings->cargo_type ?? 'Cargo',
            'Description' => $description,
            'Weight' => max(0.1, $weight),
            'Cost' => max(1, $cost),
            'SeatsAmount' => max(1, $seats),
            'DateTime' => now()->format('d.m.Y'),
        ];

        if ($order->payment_method === PaymentMethod::Cod) {
            $params['AfterpaymentOnGoodsCost'] = (string) (int) round($cost);
            $params['PaymentMethod'] = 'Cash';
        }

        if (is_array($overrides)) {
            $params = array_merge($params, $overrides);
        }

        $data = $this->request('InternetDocument', 'save', $params);
        $document = (array) ($data[0] ?? []);

        $ref = (string) data_get($document, 'Ref', '');
        $number = (string) (data_get($document, 'IntDocNumber') ?: data_get($document, 'CostOnSite', ''));

        if ($ref === '' || $number === '') {
            throw new RuntimeException('Nova Poshta не повернула номер ТТН.');
        }

        return [
            'ref' => $ref,
            'number' => $number,
            'cost_on_site' => data_get($document, 'CostOnSite') ? (float) data_get($document, 'CostOnSite') : null,
            'estimated_delivery' => data_get($document, 'EstimatedDeliveryDate') ? (string) data_get($document, 'EstimatedDeliveryDate') : null,
        ];
    }

    public function printTtnLink(string $documentRef, string $type = 'pdf_link'): string
    {
        $key = $this->apiKey();

        if (! filled($key)) {
            throw new RuntimeException('API key is not configured.');
        }

        $method = $type === 'pdf_link' ? 'printDocument' : 'printDocument';
        $format = str_replace('_link', '', $type);

        return 'https://my.novaposhta.ua/orders/'.$method.'/orders[]/'.$documentRef
            .'/type/'.$format
            .'/apiKey/'.$key;
    }

    /** @return array{success: bool, message: string} */
    public function testConnection(): array
    {
        try {
            $this->request('Address', 'getCities', ['Limit' => 1, 'Page' => 1]);

            return ['success' => true, 'message' => 'Зʼєднання з API Нової Пошти успішне.'];
        } catch (RuntimeException $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    /** @return list<array{ref: string, name: string, area: ?string}> */
    private function searchSettlements(string $query, int $limit): array
    {
        $response = $this->request('Address', 'searchSettlements', [
            'CityName' => $query,
            'Limit' => $limit,
        ]);

        $items = [];

        foreach ($response as $row) {
            $addresses = data_get($row, 'Addresses', []);

            if (! is_array($addresses)) {
                continue;
            }

            foreach ($addresses as $address) {
                $address = (array) $address;
                $ref = (string) (data_get($address, 'DeliveryCity') ?: data_get($address, 'Ref', ''));

                if ($ref === '') {
                    continue;
                }

                $name = (string) (data_get($address, 'Present') ?: data_get($address, 'MainDescription', ''));

                if ($name === '') {
                    continue;
                }

                $items[$ref] = [
                    'ref' => $ref,
                    'name' => $name,
                    'area' => data_get($address, 'Area') ? (string) data_get($address, 'Area') : null,
                ];
            }
        }

        return array_values($items);
    }

    /** @return list<array{ref: string, name: string, area: ?string}> */
    private function searchCitiesLegacy(string $query, int $limit): array
    {
        $response = $this->request('Address', 'getCities', [
            'FindByString' => $query,
            'Limit' => $limit,
            'Page' => 1,
        ]);

        return collect($response)
            ->map(fn (object|array $city): array => [
                'ref' => (string) data_get($city, 'Ref', ''),
                'name' => (string) (data_get($city, 'Description', '') ?: data_get($city, 'DescriptionRu', '')),
                'area' => data_get($city, 'AreaDescription') ? (string) data_get($city, 'AreaDescription') : null,
            ])
            ->filter(fn (array $city): bool => $city['ref'] !== '' && $city['name'] !== '')
            ->unique('ref')
            ->values()
            ->all();
    }

    /**
     * @param  list<mixed>  $response
     * @return list<array{ref: string, name: string, number: ?string, address: ?string}>
     */
    private function mapWarehouses(array $response): array
    {
        return collect($response)
            ->map(fn (object|array $warehouse): array => [
                'ref' => (string) data_get($warehouse, 'Ref', ''),
                'name' => (string) data_get($warehouse, 'Description', ''),
                'number' => data_get($warehouse, 'Number') ? (string) data_get($warehouse, 'Number') : null,
                'address' => data_get($warehouse, 'ShortAddress') ? (string) data_get($warehouse, 'ShortAddress') : null,
            ])
            ->filter(fn (array $warehouse): bool => $warehouse['ref'] !== '' && $warehouse['name'] !== '')
            ->values()
            ->all();
    }

    /** @return list<mixed> */
    private function request(string $modelName, string $calledMethod, array $methodProperties = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Nova Poshta API key is not configured.');
        }

        $settings = $this->settings();

        if (! $settings->is_active) {
            throw new RuntimeException('Інтеграція Нової Пошти вимкнена в налаштуваннях.');
        }

        try {
            $client = Http::timeout($settings->timeout ?: 20)->acceptJson();

            if (! $settings->verify_ssl) {
                $client = $client->withoutVerifying();
            }

            $httpResponse = $client->post($settings->api_url ?: config('services.nova_poshta.api_url'), [
                'apiKey' => $this->apiKey(),
                'modelName' => $modelName,
                'calledMethod' => $calledMethod,
                'methodProperties' => (object) $methodProperties,
            ]);

            $response = $httpResponse->json();
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Не вдалося підключитися до API Нової Пошти. Перевірте інтернет або NOVA_POSHTA_VERIFY_SSL=false для локальної розробки.',
                0,
                $exception,
            );
        } catch (RequestException $exception) {
            throw new RuntimeException('Nova Poshta API request failed.', 0, $exception);
        }

        if (! is_array($response)) {
            throw new RuntimeException('Nova Poshta API returned an invalid response.');
        }

        if (! data_get($response, 'success')) {
            $message = $this->formatApiErrors(data_get($response, 'errors', []));

            throw new RuntimeException($message !== '' ? $message : 'Nova Poshta API returned an error.');
        }

        return data_get($response, 'data', []) ?? [];
    }

    private function apiKey(): ?string
    {
        $settings = NovaPoshtaSettings::current();
        $key = $settings->resolvedApiKey();

        return filled($key) ? $key : null;
    }

    private function formatApiErrors(mixed $errors): string
    {
        $message = collect($errors)->filter()->implode(' ');

        return match (true) {
            str_contains(mb_strtolower($message), 'api key incorrect') => 'Невірний API-ключ Нової Пошти. Оновіть ключ у CRM → Нова Пошта (API та відправник) або в .env.',
            default => $message,
        };
    }
}
