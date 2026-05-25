@extends('layouts.app')

@push('head')
    @isset($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endisset
@endpush

@section('content')
    <section class="store-page w-full bg-white-brand pt-28 text-black-brand lg:pt-32" data-nav-theme="light">
        <div class="mx-auto w-full max-w-layout px-5 pb-16 lg:px-8 lg:pb-24">
            @isset($breadcrumbs)
                <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-8 text-black-brand/45" />
            @endisset

            @yield('store')
        </div>
    </section>

    <x-blocks.footer :catalogs="$footerCatalogs ?? collect()" />

    @stack('scripts')
@endsection
