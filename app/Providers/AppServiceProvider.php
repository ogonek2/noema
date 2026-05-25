<?php

namespace App\Providers;

use App\Enums\HomepageBlockSlug;
use App\Filesystem\BunnyFilesystemAdapter;
use App\Services\BunnyStorageService;
use App\Services\CartService;
use App\Services\HomepageContentService;
use App\Models\FormSettings;
use App\Services\SiteSeoService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BunnyStorageService::class);
        $this->app->singleton(SiteSeoService::class);
    }

    public function boot(): void
    {
        View::composer('components.blocks.navigator', function ($view): void {
            $homepage = app(HomepageContentService::class);

            $view->with([
                'cartCount' => app(CartService::class)->count(),
                'navContent' => $homepage->blockContent(HomepageBlockSlug::Navigator),
            ]);
        });

        View::composer('layouts.app', function ($view): void {
            $consultationEnabled = false;
            $consultationSchema = [];

            if (Schema::hasTable('form_settings')) {
                $settings = FormSettings::current();
                $consultationEnabled = (bool) $settings->consultation_enabled;
                $consultationSchema = $consultationEnabled
                    ? $settings->consultationSchema()
                    : [];
            }

            $view->with([
                'cartCount' => app(CartService::class)->count(),
                'consultationEnabled' => $consultationEnabled,
                'consultationSchema' => $consultationSchema,
            ]);
        });

        Storage::extend('bunny', function ($app, array $config): FilesystemAdapter {
            $adapter = new BunnyFilesystemAdapter($app->make(BunnyStorageService::class));

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config,
            );
        });
    }
}
