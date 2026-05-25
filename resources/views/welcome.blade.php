@extends('layouts.app')

@section('content')
    <x-blocks.hero :content="$hero" />
    <x-blocks.about-us :catalogs="$catalogs" :content="$aboutUs" />
    <x-blocks.product-box :products="$featuredProducts" :spotlight="$spotlightProduct" :content="$productBox" />
    <x-blocks.benefits :spotlight="$spotlightProduct" :content="$benefitsBlock" :items="$benefits" />
    <x-blocks.audience :cards="$audienceCards" />
    <x-blocks.reviews :items="$reviews" />
    <x-blocks.ribbon :gallery="$ribbonGallery" />
    <x-blocks.statement :spotlight="$spotlightProduct" :content="$statement" />
    <x-blocks.footer :catalogs="$catalogs" :content="$footerContent" />
@endsection
