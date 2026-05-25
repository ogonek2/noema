@props([
    'class' => '',
])

<span {{ $attributes->merge(['class' => 'skeleton block '.$class]) }} aria-hidden="true"></span>
