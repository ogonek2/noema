<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Services\CartService;
use App\Services\SiteSeoService;
use App\Services\StorefrontService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly StorefrontService $storefront,
        private readonly SiteSeoService $seo,
    ) {}

    public function index(): View
    {
        return view('cart.index', [
            'groups' => $this->cart->displayGroups(),
            'subtotal' => $this->cart->subtotal(),
            'subtotalFormatted' => $this->cart->formattedSubtotal(),
            'footerCatalogs' => $this->storefront->activeCatalogs(),
            'seo' => $this->seo->forUtilityPage('Кошик', route('cart.index')),
        ]);
    }

    public function store(AddToCartRequest $request): JsonResponse|RedirectResponse
    {
        $batchCount = 0;

        try {
            $validated = $request->validated();

            if (isset($validated['lines'])) {
                $keys = $this->cart->addBatch(
                    $validated['product_slug'],
                    $validated['lines'],
                );
                $key = $keys[0] ?? null;
                $batchCount = count($keys);
            } else {
                $key = $this->cart->add($validated);
            }
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => collect($exception->errors())->flatten()->first(),
                    'errors' => $exception->errors(),
                ], 422);
            }

            throw $exception;
        }

        if ($request->expectsJson()) {
            $message = $batchCount > 1
                ? "Набір із {$batchCount} позицій додано до кошика."
                : 'Товар додано до кошика.';

            return response()->json([
                'ok' => true,
                'key' => $key,
                'count' => $this->cart->count(),
                'message' => $message,
            ]);
        }

        $success = $batchCount > 1
            ? "Набір із {$batchCount} позицій додано до кошика."
            : 'Товар додано до кошика.';

        return redirect()
            ->route('cart.index')
            ->with('success', $success);
    }

    public function update(UpdateCartItemRequest $request, string $key): RedirectResponse
    {
        $this->cart->updateQuantity($key, $request->integer('quantity'));

        return redirect()
            ->route('cart.index')
            ->with('success', 'Кошик оновлено.');
    }

    public function destroyGroup(string $groupId): RedirectResponse
    {
        $this->cart->removeGroup($groupId);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Набір видалено з кошика.');
    }

    public function destroy(string $key): RedirectResponse|JsonResponse
    {
        $this->cart->remove($key);

        if (request()->expectsJson()) {
            return response()->json([
                'ok' => true,
                'count' => $this->cart->count(),
                'subtotal' => $this->cart->formattedSubtotal(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Позицію видалено.');
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'count' => $this->cart->count(),
            'subtotal' => $this->cart->formattedSubtotal(),
        ]);
    }
}
