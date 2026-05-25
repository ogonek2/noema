@props(['seo'])

@php
    assert($seo instanceof \App\Support\SeoMeta);
@endphp

<title>{{ $seo->title }}</title>

@if (filled($seo->description))
    <meta name="description" content="{{ $seo->description }}">
@endif

@if (filled($seo->keywords))
    <meta name="keywords" content="{{ $seo->keywords }}">
@endif

@if (filled($seo->robots))
    <meta name="robots" content="{{ $seo->robots }}">
@endif

@if (filled($seo->canonical))
    <link rel="canonical" href="{{ $seo->canonical }}">
@endif

@if (filled($seo->googleSiteVerification))
    <meta name="google-site-verification" content="{{ $seo->googleSiteVerification }}">
@endif

@if (filled($seo->faviconUrl))
    <link rel="icon" href="{{ $seo->faviconUrl }}" type="image/png">
@endif

@if (filled($seo->appleTouchIconUrl))
    <link rel="apple-touch-icon" href="{{ $seo->appleTouchIconUrl }}">
@endif

<meta property="og:locale" content="{{ str_replace('-', '_', $seo->ogLocale ?? 'uk_UA') }}">
<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:title" content="{{ $seo->ogTitle ?? $seo->title }}">

@if (filled($seo->ogDescription))
    <meta property="og:description" content="{{ $seo->ogDescription }}">
@endif

@if (filled($seo->ogUrl))
    <meta property="og:url" content="{{ $seo->ogUrl }}">
@endif

@if (filled($seo->ogSiteName))
    <meta property="og:site_name" content="{{ $seo->ogSiteName }}">
@endif

@if (filled($seo->ogImage))
    <meta property="og:image" content="{{ $seo->ogImage }}">
    <meta property="og:image:secure_url" content="{{ $seo->ogImage }}">
@endif

<meta name="twitter:card" content="{{ $seo->twitterCard ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $seo->ogTitle ?? $seo->title }}">

@if (filled($seo->ogDescription))
    <meta name="twitter:description" content="{{ $seo->ogDescription }}">
@endif

@if (filled($seo->ogImage))
    <meta name="twitter:image" content="{{ $seo->ogImage }}">
@endif

@if (filled($seo->twitterSite))
    <meta name="twitter:site" content="{{ $seo->twitterSite }}">
@endif

@if (! empty($seo->jsonLd))
    @foreach ($seo->jsonLd as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endforeach
@endif
