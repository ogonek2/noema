<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Services\SiteSeoService;
use App\Services\StorefrontService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        private readonly StorefrontService $storefront,
        private readonly SiteSeoService $seo,
    ) {}

    public function index(): View
    {
        $catalogs = $this->storefront->activeCatalogs();

        if ($catalogs->count() === 1) {
            $catalog = $catalogs->first();

            return view('catalog.show', [
                'catalog' => $catalog,
                'products' => $this->storefront->catalogProducts($catalog),
                'seo' => $this->seo->forCatalog($catalog),
                'singleCatalogMode' => true,
            ]);
        }

        return view('catalog.index', [
            'catalogs' => $catalogs,
            'seo' => $this->seo->forCatalogIndex(),
        ]);
    }

    public function show(Catalog $catalog): View|RedirectResponse
    {
        if ($this->storefront->activeCatalogs()->count() === 1) {
            return redirect()->route('catalog.index');
        }

        $catalog = $this->storefront->findCatalog($catalog->slug);

        return view('catalog.show', [
            'catalog' => $catalog,
            'products' => $this->storefront->catalogProducts($catalog),
            'seo' => $this->seo->forCatalog($catalog),
        ]);
    }
}
