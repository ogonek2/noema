<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Services\SiteSeoService;
use App\Services\StorefrontService;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        private readonly StorefrontService $storefront,
        private readonly SiteSeoService $seo,
    ) {}

    public function index(): View
    {
        return view('catalog.index', [
            'catalogs' => $this->storefront->activeCatalogs(),
            'seo' => $this->seo->forCatalogIndex(),
        ]);
    }

    public function show(Catalog $catalog): View
    {
        $catalog = $this->storefront->findCatalog($catalog->slug);

        return view('catalog.show', [
            'catalog' => $catalog,
            'products' => $this->storefront->catalogProducts($catalog),
            'seo' => $this->seo->forCatalog($catalog),
        ]);
    }
}
