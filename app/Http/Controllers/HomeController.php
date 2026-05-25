<?php

namespace App\Http\Controllers;

use App\Enums\HomepageBlockSlug;
use App\Services\HomepageContentService;
use App\Services\SiteSeoService;
use App\Services\StorefrontService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly StorefrontService $storefront,
        private readonly HomepageContentService $homepage,
        private readonly SiteSeoService $seo,
    ) {}

    public function index(): View
    {
        $featuredProducts = $this->homepage->featuredProducts();
        $spotlightProduct = $this->homepage->spotlightProduct();
        $catalogs = $this->storefront->activeCatalogs();

        return view('welcome', [
            'featuredProducts' => $featuredProducts,
            'spotlightProduct' => $spotlightProduct,
            'catalogs' => $catalogs,
            'audienceCards' => $this->homepage->audienceCards(),
            'ribbonGallery' => $this->homepage->ribbonGalleryItems(),
            'reviews' => $this->homepage->reviews(),
            'benefits' => $this->homepage->benefits($spotlightProduct),
            'hero' => $this->homepage->blockContent(HomepageBlockSlug::Hero),
            'aboutUs' => $this->homepage->blockContent(HomepageBlockSlug::AboutUs),
            'productBox' => $this->homepage->blockContent(HomepageBlockSlug::ProductBox),
            'benefitsBlock' => $this->homepage->blockContent(HomepageBlockSlug::Benefits),
            'statement' => $this->homepage->blockContent(HomepageBlockSlug::Statement),
            'footerContent' => $this->homepage->blockContent(HomepageBlockSlug::Footer),
            'seo' => $this->seo->forHome(),
        ]);
    }
}
