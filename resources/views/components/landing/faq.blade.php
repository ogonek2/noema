@props(['content' => []])

@php
    use App\Support\LandingProse;

    $navTheme = $content['nav_theme'] ?? 'light';
    $items = collect($content['items'] ?? []);
@endphp

<section class="w-full {{ $navTheme === 'dark' ? 'bg-black-brand text-white-brand' : 'bg-white-brand text-black-brand' }}"
    data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-16 lg:px-8 lg:py-24">
        @if (filled($content['title'] ?? null))
            <h2 class="mb-10 text-center text-[clamp(1.5rem,3.5vw,3rem)] font-thin uppercase tracking-[0.08em]">
                {{ $content['title'] }}
            </h2>
        @endif

        <div class="mx-auto max-w-3xl divide-y divide-current/15">
            @foreach ($items as $index => $item)
                <details class="group py-5" @if ($index === 0) open @endif>
                    <summary class="cursor-pointer list-none text-[0.82rem] font-bold uppercase tracking-[0.12em] marker:content-none">
                        <span class="flex items-center justify-between gap-4">
                            {{ $item['question'] ?? '' }}
                            <span class="shrink-0 text-lg opacity-40 transition-transform group-open:rotate-45">+</span>
                        </span>
                    </summary>
                    <div class="landing-prose landing-prose--{{ $navTheme }} landing-prose--align-left mt-4 text-[0.95rem]">
                        {!! LandingProse::render($item['answer'] ?? null) !!}
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
