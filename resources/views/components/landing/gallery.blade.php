@props(['content' => []])

@php
    use App\Support\MediaUrl;

    $navTheme = $content['nav_theme'] ?? 'light';
    $columns = (int) ($content['columns'] ?? 3);
    $gridClass = match ($columns) {
        2 => 'grid-cols-1 sm:grid-cols-2',
        4 => 'grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-2 lg:grid-cols-3',
    };
    $images = collect($content['images'] ?? [])->filter(fn ($img) => filled($img['path'] ?? null));
@endphp

<section class="w-full {{ $navTheme === 'dark' ? 'bg-black-brand text-white-brand' : 'bg-white-brand text-black-brand' }}"
    data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-16 lg:px-8 lg:py-24">
        @if (filled($content['title'] ?? null))
            <h2 class="mb-10 text-center text-[2rem] font-thin uppercase tracking-[0.08em]">{{ $content['title'] }}</h2>
        @endif

        <div class="grid gap-4 {{ $gridClass }}">
            @foreach ($images as $image)
                <figure>
                    <img src="{{ MediaUrl::resolve($image['path']) }}" alt="{{ $image['alt'] ?? '' }}"
                        class="aspect-[4/5] w-full object-cover">
                    @if (filled($image['caption'] ?? null))
                        <figcaption class="mt-2 text-[0.72rem] uppercase tracking-[0.14em] opacity-55">
                            {{ $image['caption'] }}
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
</section>
