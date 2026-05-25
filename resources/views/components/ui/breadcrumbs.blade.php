@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 text-[0.68rem] uppercase tracking-[0.18em] text-gray-text']) }} aria-label="Breadcrumb">
    @foreach ($items as $index => $item)
        @if ($index > 0)
            <span class="text-black-brand/25" aria-hidden="true">/</span>
        @endif
        @if (! empty($item['url']) && $index < count($items) - 1)
            <a href="{{ $item['url'] }}" class="transition-colors hover:text-black-brand">{{ $item['label'] }}</a>
        @else
            <span class="{{ $index === count($items) - 1 ? 'text-black-brand' : '' }}">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
