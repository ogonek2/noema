<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingFormController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NovaPoshtaController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/p/{landingPage:slug}', [LandingPageController::class, 'show'])
    ->name('landing.show');

Route::post('/forms/{formKey}', [LandingFormController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('forms.submit');

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{catalog:slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/product/{product:slug}/data', [ProductController::class, 'data'])->name('product.data');
Route::get('/product/{product:slug}/cart-config', [ProductController::class, 'cartConfig'])->name('product.cart-config');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/group/{groupId}', [CartController::class, 'destroyGroup'])->name('cart.destroy-group');
Route::delete('/cart/{key}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order:number}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/pay/{order:number}', [CheckoutController::class, 'pay'])->name('checkout.pay');
Route::post('/checkout/liqpay/callback', [CheckoutController::class, 'liqPayCallback'])->name('checkout.liqpay.callback');

Route::get('/api/nova-poshta/cities', [NovaPoshtaController::class, 'cities'])->name('nova-poshta.cities');
Route::get('/api/nova-poshta/warehouses', [NovaPoshtaController::class, 'warehouses'])->name('nova-poshta.warehouses');
