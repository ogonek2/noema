<?php

namespace App\Http\Controllers;

use App\Services\NovaPoshtaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class NovaPoshtaController extends Controller
{
    public function __construct(private readonly NovaPoshtaService $novaPoshta) {}

    public function cities(Request $request): JsonResponse
    {
        if (! $this->novaPoshta->isConfigured()) {
            return response()->json([
                'configured' => false,
                'items' => [],
                'message' => 'Сервіс Нової Пошти не налаштовано. Додайте NOVA_POSHTA_API_KEY у .env',
            ]);
        }

        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'configured' => true,
                'items' => [],
                'hint' => 'Введіть мінімум 2 символи',
            ]);
        }

        try {
            return response()->json([
                'configured' => true,
                'items' => $this->novaPoshta->searchCities($query),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'configured' => true,
                'items' => [],
                'message' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Помилка пошуку міста.',
            ], 422);
        }
    }

    public function warehouses(Request $request): JsonResponse
    {
        if (! $this->novaPoshta->isConfigured()) {
            return response()->json([
                'configured' => false,
                'items' => [],
                'message' => 'Сервіс Нової Пошти не налаштовано.',
            ]);
        }

        $cityRef = (string) $request->query('city_ref', '');
        $query = trim((string) $request->query('q', ''));

        if ($cityRef === '') {
            return response()->json([
                'configured' => true,
                'items' => [],
                'hint' => 'Спочатку оберіть місто',
            ]);
        }

        if (mb_strlen($query) < 2) {
            return response()->json([
                'configured' => true,
                'items' => [],
                'hint' => 'Введіть мінімум 2 символи для пошуку відділення',
            ]);
        }

        try {
            return response()->json([
                'configured' => true,
                'items' => $this->novaPoshta->searchWarehouses($cityRef, $query),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'configured' => true,
                'items' => [],
                'message' => $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Помилка пошуку відділення.',
            ], 422);
        }
    }
}
