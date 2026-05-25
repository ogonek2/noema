<?php

namespace App\Services;

use App\Enums\CustomizationOptionType;
use App\Models\Product;
use App\Models\ProductCustomizationOption;
use App\Models\ProductVariant;
use App\Support\PriceFormat;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const SESSION_KEY = 'cart.items';

    /** @return list<array<string, mixed>> */
    public function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function count(): int
    {
        return (int) collect($this->items())->sum('quantity');
    }

    public function subtotal(): float
    {
        return (float) collect($this->items())->sum('line_total');
    }

    public function formattedSubtotal(): string
    {
        return PriceFormat::uah($this->subtotal());
    }

    /** @return Collection<int, array<string, mixed>> */
    public function enrichedItems(): Collection
    {
        return collect($this->items())->map(function (array $line): array {
            $line['unit_price_formatted'] = PriceFormat::uah($line['unit_price']);
            $line['line_total_formatted'] = PriceFormat::uah($line['line_total']);

            return $line;
        });
    }

    /** @return Collection<int, array{type: string, group_id?: string, label?: string, subtotal?: float, subtotal_formatted?: string, items: Collection<int, array<string, mixed>>}> */
    public function displayGroups(): Collection
    {
        $groups = [];
        $groupIndex = [];

        foreach ($this->enrichedItems() as $item) {
            $groupId = $item['group_id'] ?? null;

            if ($groupId !== null) {
                if (! isset($groupIndex[$groupId])) {
                    $groupIndex[$groupId] = count($groups);
                    $groups[] = [
                        'type' => 'batch',
                        'group_id' => $groupId,
                        'label' => $item['group_label'] ?? 'Набір',
                        'items' => collect(),
                    ];
                }

                $groups[$groupIndex[$groupId]]['items']->push($item);
            } else {
                $groups[] = [
                    'type' => 'single',
                    'items' => collect([$item]),
                ];
            }
        }

        return collect($groups)->map(function (array $group): array {
            if ($group['type'] === 'batch') {
                $group['items'] = $group['items']->sortBy('group_index')->values();
                $subtotal = (float) $group['items']->sum('line_total');
                $group['subtotal'] = $subtotal;
                $group['subtotal_formatted'] = PriceFormat::uah($subtotal);
            }

            return $group;
        });
    }

    public function removeGroup(string $groupId): void
    {
        $items = collect($this->items())
            ->reject(fn (array $line): bool => ($line['group_id'] ?? null) === $groupId)
            ->all();

        session([self::SESSION_KEY => $items]);
    }

    public const MAX_BATCH_LINES = 50;

    /**
     * @param  list<array{
     *     variant_id: int,
     *     customizations?: list<array{slug: string, value: mixed}>,
     *     notes?: string|null
     * }>  $lines
     * @return list<string>
     */
    public function addBatch(string $productSlug, array $lines): array
    {
        if ($lines === []) {
            throw ValidationException::withMessages([
                'lines' => 'Додайте хоча б один комплект у набір.',
            ]);
        }

        if (count($lines) > self::MAX_BATCH_LINES) {
            throw ValidationException::withMessages([
                'lines' => 'Максимум '.self::MAX_BATCH_LINES.' позицій у одному наборі.',
            ]);
        }

        $groupId = (string) str()->ulid();
        $total = count($lines);
        $keys = [];

        foreach ($lines as $index => $line) {
            $keys[] = $this->add([
                'product_slug' => $productSlug,
                'variant_id' => (int) $line['variant_id'],
                'quantity' => 1,
                'customizations' => $line['customizations'] ?? [],
                'notes' => $line['notes'] ?? null,
                'group_id' => $groupId,
                'group_index' => $index + 1,
                'group_total' => $total,
            ]);
        }

        return $keys;
    }

    /**
     * @param  array{
     *     product_slug: string,
     *     variant_id: int,
     *     quantity: int,
     *     customizations?: list<array{slug: string, value: mixed}>,
     *     notes?: string|null,
     *     group_id?: string|null,
     *     group_index?: int|null,
     *     group_total?: int|null
     * }  $input
     */
    public function add(array $input): string
    {
        $variant = ProductVariant::query()
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->active())
            ->with([
                'product.customizationOptions' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->find($input['variant_id']);

        if (! $variant) {
            throw ValidationException::withMessages([
                'variant_id' => 'Оберіть коректний розмір для цього товару.',
            ]);
        }

        $product = $variant->product;

        $quantity = max(1, min(99, (int) $input['quantity']));
        $resolvedCustomizations = $this->resolveCustomizations(
            $product,
            $input['customizations'] ?? [],
        );

        $notes = trim($input['notes'] ?? '');
        $customizationTotal = collect($resolvedCustomizations)->sum('price_delta');
        $unitPrice = (float) ($variant->price ?? $product->price) + $customizationTotal;
        $lineTotal = round($unitPrice * $quantity, 2);

        $groupId = $input['group_id'] ?? null;
        $groupIndex = isset($input['group_index']) ? (int) $input['group_index'] : null;
        $groupTotal = isset($input['group_total']) ? (int) $input['group_total'] : null;

        $key = $this->lineKey(
            $product->id,
            $variant->id,
            $resolvedCustomizations,
            $notes,
            $groupId,
            $groupIndex,
        );
        $items = $this->items();

        if ($groupId === null && isset($items[$key])) {
            $items[$key]['quantity'] += $quantity;
            $items[$key]['line_total'] = round($items[$key]['unit_price'] * $items[$key]['quantity'], 2);
        } else {
            $groupLabel = $groupTotal > 1
                ? 'Набір ×'.$groupTotal
                : null;

            $items[$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'product_slug' => $product->slug,
                'product_name' => $product->name,
                'variant_id' => $variant->id,
                'size' => $variant->size,
                'sku' => $variant->sku,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'image' => $product->imageUrl(),
                'color_name' => $product->color_name,
                'customizations' => $resolvedCustomizations,
                'notes' => $notes !== '' ? $notes : null,
                'group_id' => $groupId,
                'group_index' => $groupIndex,
                'group_total' => $groupTotal,
                'group_label' => $groupLabel,
            ];
        }

        session([self::SESSION_KEY => $items]);

        return $key;
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $items = $this->items();

        if (! isset($items[$key])) {
            throw ValidationException::withMessages(['key' => 'Позицію не знайдено.']);
        }

        $quantity = max(1, min(99, $quantity));
        $items[$key]['quantity'] = $quantity;
        $items[$key]['line_total'] = round($items[$key]['unit_price'] * $quantity, 2);

        session([self::SESSION_KEY => $items]);
    }

    public function remove(string $key): void
    {
        $items = $this->items();
        unset($items[$key]);
        session([self::SESSION_KEY => $items]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @param  list<array{slug: string, value: mixed}>  $submitted
     * @return list<array{slug: string, name: string, value: string, label: string, price_delta: float}>
     */
    private function resolveCustomizations(Product $product, array $submitted): array
    {
        $options = $product->customizationOptions->keyBy('slug');
        $bySlug = collect($submitted)->keyBy('slug');
        $resolved = [];

        foreach ($options as $slug => $option) {
            if (! $bySlug->has($slug)) {
                if ($option->is_required) {
                    throw ValidationException::withMessages([
                        "customizations.{$slug}" => "Поле «{$option->name}» обовʼязкове.",
                    ]);
                }

                continue;
            }

            $rawValue = $bySlug->get($slug)['value'] ?? null;
            $parsed = $this->parseCustomizationValue($option, $rawValue);

            if ($parsed === null) {
                if ($option->is_required) {
                    throw ValidationException::withMessages([
                        "customizations.{$slug}" => "Поле «{$option->name}» обовʼязкове.",
                    ]);
                }

                continue;
            }

            $resolved[] = $parsed;
        }

        return $resolved;
    }

    /**
     * @return array{slug: string, name: string, value: string, label: string, price_delta: float}|null
     */
    private function parseCustomizationValue(ProductCustomizationOption $option, mixed $rawValue): ?array
    {
        return match ($option->type) {
            CustomizationOptionType::Checkbox => $this->parseCheckbox($option, $rawValue),
            CustomizationOptionType::Select => $this->parseSelect($option, $rawValue),
            CustomizationOptionType::Text => $this->parseText($option, $rawValue),
        };
    }

    /** @return array{slug: string, name: string, value: string, label: string, price_delta: float}|null */
    private function parseCheckbox(ProductCustomizationOption $option, mixed $rawValue): ?array
    {
        $checked = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (! $checked) {
            return null;
        }

        return [
            'slug' => $option->slug,
            'name' => $option->name,
            'value' => '1',
            'label' => 'Так',
            'price_delta' => (float) $option->price_delta,
        ];
    }

    /** @return array{slug: string, name: string, value: string, label: string, price_delta: float}|null */
    private function parseSelect(ProductCustomizationOption $option, mixed $rawValue): ?array
    {
        $value = trim((string) $rawValue);

        if ($value === '') {
            return null;
        }

        $choices = $option->options ?? [];

        if (! array_key_exists($value, $choices)) {
            throw ValidationException::withMessages([
                "customizations.{$option->slug}" => 'Невірне значення опції.',
            ]);
        }

        return [
            'slug' => $option->slug,
            'name' => $option->name,
            'value' => $value,
            'label' => (string) $choices[$value],
            'price_delta' => (float) $option->price_delta,
        ];
    }

    /** @return array{slug: string, name: string, value: string, label: string, price_delta: float}|null */
    private function parseText(ProductCustomizationOption $option, mixed $rawValue): ?array
    {
        $value = trim((string) $rawValue);

        if ($value === '') {
            return null;
        }

        return [
            'slug' => $option->slug,
            'name' => $option->name,
            'value' => $value,
            'label' => $value,
            'price_delta' => (float) $option->price_delta,
        ];
    }

    /**
     * @param  list<array{slug: string, name: string, value: string, label: string, price_delta: float}>  $customizations
     */
    private function lineKey(
        int $productId,
        int $variantId,
        array $customizations,
        string $notes,
        ?string $groupId = null,
        ?int $groupIndex = null,
    ): string {
        $customizationKey = collect($customizations)
            ->sortBy('slug')
            ->map(fn (array $item): string => $item['slug'].':'.$item['value'])
            ->implode('|');

        $parts = [$productId, $variantId, $customizationKey, $notes];

        if ($groupId !== null) {
            $parts[] = $groupId;
            $parts[] = (string) ($groupIndex ?? uniqid('', true));
        }

        return hash('xxh128', implode('|', $parts));
    }
}
