<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\SiteSeoService;
use App\Services\StorefrontService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly StorefrontService $storefront,
        private readonly SiteSeoService $seo,
    ) {}

    public function show(Product $product): View
    {
        $product = $this->storefront->findProduct($product->slug);
        $colorAlternatives = $this->storefront->colorAlternatives($product);

        $sizes = $product->variants
            ->pluck('size')
            ->filter()
            ->unique()
            ->values();

        return view('product.show', [
            'product' => $product,
            'colorAlternatives' => $colorAlternatives,
            'modelColorVariants' => $this->storefront->modelColorVariants($product),
            'modelName' => $this->storefront->modelDisplayName($product),
            'relationGroups' => $this->storefront->groupedRelations($product),
            'similarProducts' => $this->storefront->similarProducts($product),
            'sizes' => $sizes,
            'initialPayload' => $this->storefront->productPayload($product),
            'seo' => $this->seo->forProduct($product),
        ]);
    }

    public function data(Product $product): JsonResponse
    {
        $product = $this->storefront->findProduct($product->slug);

        return response()->json($this->storefront->productPayload($product));
    }

    public function cartConfig(Product $product): JsonResponse
    {
        return response()->json($this->storefront->cartConfigPayload($product));
    }
}
