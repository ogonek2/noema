<?php

namespace App\Services;

use App\Enums\ProductLength;
use App\Models\Product;
use App\Models\SizePreset;
use App\Models\SizePresetVariant;
use Illuminate\Support\Facades\DB;

class SizePresetService
{
    public function applyToProduct(
        SizePreset $preset,
        Product $product,
        bool $replaceChart = true,
        bool $replaceVariants = true,
        bool $applyIntro = true,
        bool $applyLengthGuide = true,
    ): void {
        DB::transaction(function () use (
            $preset,
            $product,
            $replaceChart,
            $replaceVariants,
            $applyIntro,
            $applyLengthGuide,
        ): void {
            $preset->load(['chartRows', 'variants']);

            if ($applyIntro && filled($preset->size_chart_intro)) {
                $product->size_chart_intro = $preset->size_chart_intro;
            }

            if ($applyLengthGuide && filled($preset->length_guide)) {
                $product->length_guide = $preset->length_guide;
            }

            $product->size_preset_id = $preset->id;
            $product->save();

            if ($replaceChart) {
                $product->sizeChartRows()->delete();

                foreach ($preset->chartRows as $row) {
                    $product->sizeChartRows()->create([
                        'size_label' => $row->size_label,
                        'bust' => $row->bust,
                        'waist' => $row->waist,
                        'hip' => $row->hip,
                        'inseam' => $row->inseam,
                        'sort_order' => $row->sort_order,
                    ]);
                }
            }

            if ($replaceVariants) {
                $product->variants()->delete();

                foreach ($preset->variants as $variant) {
                    $product->variants()->create([
                        'sku' => $this->buildVariantSku($product, $variant),
                        'name' => $variant->size,
                        'size' => $variant->size,
                        'length' => $variant->length,
                        'price' => $product->price,
                        'stock_quantity' => 0,
                        'is_active' => true,
                        'sort_order' => $variant->sort_order,
                    ]);
                }
            }
        });
    }

    private function buildVariantSku(Product $product, SizePresetVariant $variant): string
    {
        $suffix = $variant->size;

        if ($variant->length !== ProductLength::Regular) {
            $suffix .= '-'.strtoupper($variant->length->value);
        }

        return $product->sku.'-'.$suffix;
    }
}
