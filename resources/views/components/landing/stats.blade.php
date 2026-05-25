@props(['content' => []])

@php
    $navTheme = $content['nav_theme'] ?? 'light';
    $items = collect($content['items'] ?? []);
    $count = max($items->count(), 1);
    $gridClass = match (true) {
        $count >= 4 => 'sm:grid-cols-2 xl:grid-cols-4',
        $count === 3 => 'sm:grid-cols-3',
        $count === 2 => 'sm:grid-cols-2',
        default => 'grid-cols-1',
    };
@endphp

<section class="w-full {{ $navTheme === 'dark' ? 'bg-black-brand text-white-brand' : 'bg-white-brand text-black-brand' }}"
    data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-14 lg:px-8 lg:py-20">
        <div class="grid gap-10 {{ $gridClass }}">
            @foreach ($items as $item)
                <div class="text-center">
                    <p class="text-[clamp(2rem,5vw,3.5rem)] font-thin leading-none tracking-[0.04em]">{{ $item['value'] ?? '' }}</p>
                    <p class="mt-3 text-[0.72rem] uppercase tracking-[0.2em] opacity-55">{{ $item['label'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
