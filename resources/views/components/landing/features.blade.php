@props(['content' => []])

@php
    use App\Support\MediaUrl;

    $navTheme = $content['nav_theme'] ?? 'light';
    $columns = (int) ($content['columns'] ?? 3);
    $gridClass = match ($columns) {
        2 => 'sm:grid-cols-2',
        4 => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-3',
    };
    $items = collect($content['items'] ?? []);
@endphp

<section class="w-full {{ $navTheme === 'dark' ? 'bg-black-brand text-white-brand' : 'bg-white-brand text-black-brand' }}"
    data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-16 lg:px-8 lg:py-24">
        @if (filled($content['title'] ?? null))
            <h2 class="text-center text-[2rem] font-thin uppercase tracking-[0.08em] lg:text-[3rem]">{{ $content['title'] }}</h2>
        @endif
        @if (filled($content['subtitle'] ?? null))
            <p class="mx-auto mt-4 max-w-2xl text-center text-[1rem] leading-relaxed opacity-70">{{ $content['subtitle'] }}</p>
        @endif

        <div class="mt-12 grid gap-8 {{ $gridClass }}">
            @foreach ($items as $item)
                <article class="flex flex-col">
                    @if (filled($item['image'] ?? null))
                        <img src="{{ MediaUrl::resolve($item['image']) }}" alt="{{ $item['title'] ?? '' }}"
                            class="mb-5 aspect-[4/3] w-full object-cover">
                    @endif
                    <h3 class="text-[0.85rem] font-bold uppercase tracking-[0.14em]">{{ $item['title'] ?? '' }}</h3>
                    <p class="mt-3 text-[0.92rem] leading-relaxed opacity-75">{{ $item['text'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
