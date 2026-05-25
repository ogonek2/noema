<?php

use App\Http\Controllers\Admin\HomepageContentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('admin/api/homepage')
    ->name('admin.homepage.')
    ->group(function (): void {
        Route::get('/', [HomepageContentController::class, 'show'])->name('show');
        Route::put('/globals', [HomepageContentController::class, 'updateGlobals'])->name('globals.update');
        Route::put('/blocks/{slug}', [HomepageContentController::class, 'updateBlock'])->name('blocks.update');
        Route::post('/upload', [HomepageContentController::class, 'upload'])->name('upload');

        Route::post('/reviews', [HomepageContentController::class, 'storeReview'])->name('reviews.store');
        Route::put('/reviews/{review}', [HomepageContentController::class, 'updateReview'])->name('reviews.update');
        Route::delete('/reviews/{review}', [HomepageContentController::class, 'destroyReview'])->name('reviews.destroy');

        Route::post('/audience-cards', [HomepageContentController::class, 'storeAudienceCard'])->name('audience.store');
        Route::put('/audience-cards/{audienceCard}', [HomepageContentController::class, 'updateAudienceCard'])->name('audience.update');
        Route::delete('/audience-cards/{audienceCard}', [HomepageContentController::class, 'destroyAudienceCard'])->name('audience.destroy');

        Route::post('/benefits', [HomepageContentController::class, 'storeBenefit'])->name('benefits.store');
        Route::put('/benefits/{benefit}', [HomepageContentController::class, 'updateBenefit'])->name('benefits.update');
        Route::delete('/benefits/{benefit}', [HomepageContentController::class, 'destroyBenefit'])->name('benefits.destroy');

        Route::post('/ribbon-images', [HomepageContentController::class, 'storeRibbonImage'])->name('ribbon.store');
        Route::put('/ribbon-images/{ribbonImage}', [HomepageContentController::class, 'updateRibbonImage'])->name('ribbon.update');
        Route::delete('/ribbon-images/{ribbonImage}', [HomepageContentController::class, 'destroyRibbonImage'])->name('ribbon.destroy');
    });
