@props([
    'html' => null,
    'theme' => 'light',
    'align' => 'left',
    'size' => 'default',
])

@php
    use App\Support\LandingProse;
@endphp

<div @class([
    'landing-prose',
    'landing-prose--' . $theme,
    'landing-prose--align-' . $align,
    'landing-prose--size-' . $size,
    $attributes->get('class'),
])>
    {!! LandingProse::render($html) !!}
</div>
