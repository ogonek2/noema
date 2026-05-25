<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @isset($seo)
            <x-seo.meta :seo="$seo" />
        @else
            <title>@yield('title', config('app.name', 'NOEMA'))</title>
        @endisset
        @stack('head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body class="bg-black-brand text-white-brand overflow-x-hidden">
        @if ($showNavigator ?? true)
            <x-blocks.navigator />
        @endif

        <main>
            @yield('content')
        </main>

        <x-product.lightbox />
        <x-cart.modal />

        <p class="cart-toast pointer-events-none fixed bottom-6 left-1/2 z-[120] -translate-x-1/2 translate-y-4 border border-black-brand/10 bg-white-brand px-5 py-3 text-[0.72rem] uppercase tracking-[0.14em] text-black-brand opacity-0 shadow-lg transition duration-300"
            data-cart-toast role="status" aria-live="polite"></p>

        @stack('scripts')
    </body>
</html>
