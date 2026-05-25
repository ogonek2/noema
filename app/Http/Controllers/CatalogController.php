<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Services\StorefrontService;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private readonly StorefrontService $storefront) {}

    public function index(): View
    {
        return view('catalog.index', [
            'catalogs' => $this->storefront->activeCatalogs(),
        ]);
    }

    public function show(Catalog $catalog): View
    {
        $catalog = $this->storefront->findCatalog($catalog->slug);

        return view('catalog.show', [
            'catalog' => $catalog,
            'products' => $this->storefront->catalogProducts($catalog),
        ]);
    }
}
