<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Services\HomepageContentService;
use App\Services\LandingPageService;
use App\Services\SiteSeoService;
use App\Services\StorefrontService;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __construct(
        private readonly LandingPageService $landings,
        private readonly StorefrontService $storefront,
        private readonly HomepageContentService $homepage,
        private readonly SiteSeoService $seo,
    ) {}

    public function show(LandingPage $landingPage): View
    {
        $sections = $this->landings->activeSections($landingPage);
        $catalogs = $this->storefront->activeCatalogs();

        return view('landing.show', [
            'page' => $landingPage,
            'sections' => $sections,
            'catalogs' => $catalogs,
            'showNavigator' => $landingPage->show_navigator,
            'footerContent' => $landingPage->show_footer
                ? $this->homepage->blockContent(\App\Enums\HomepageBlockSlug::Footer)
                : [],
            'seo' => $this->seo->forLanding($landingPage),
        ]);
    }
}
