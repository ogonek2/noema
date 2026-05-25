@props([
    'spotlight' => null,
    'content' => [],
    'items' => [],
])

@php
    use App\Support\MediaUrl;

    /** @var \App\Models\Product|null $spotlight */
    $benefits = ! empty($items) ? $items : [
        ['n' => '1.', 'title' => 'Довговічність', 'text' => 'Служить від 3 до 12 років'],
        ['n' => '2.', 'title' => 'Комфорт', 'text' => 'Понад 12+ годинних змін'],
        ['n' => '3.', 'title' => 'Свобода рухів', 'text' => $spotlight?->fit_summary ?? 'Еластичні тканини'],
        ['n' => '4.', 'title' => 'Мінімалістичний дизайн', 'text' => 'Чистий, професійний вигляд'],
        ['n' => '5.', 'title' => 'Унісекс посадка', 'text' => 'Підходить для різних силуетів'],
        ['n' => '6.', 'title' => 'Tall / Small розміри', 'text' => $spotlight?->length_guide ?? 'Ідеальна посадка для будь-якого зросту'],
    ];
    $titleLine1 = $content['title_line1'] ?? 'Наші';
    $titleLine2 = $content['title_line2'] ?? 'переваги';
    $badge = $content['badge'] ?? '[ NOEMA ]';
    $descriptionFallback = $content['description_fallback'] ?? 'NOEMA створює медичний одяг, який витримує інтенсивні зміни: стійкість до прання, збереження форми та комфорт протягом усього дня.';
    $madeWith = $content['made_with'] ?? 'Made with Noema';
    $fallbackImage = MediaUrl::resolve($content['fallback_image'] ?? null, 'images/cloth.png');
@endphp

<section id="benefits" class="w-full bg-black-brand text-white-brand py-16 lg:py-24 relative" data-nav-theme="dark"
    data-aos="fade-up">
    <div class="mx-auto relative overflow-hidden w-full max-w-layout lg:gap-12 px-5 lg:px-8 lg:py-20">
        <div class="z-20 relative">
            <div class="mb-10 flex items-center justify-between gap-4" data-aos="fade-up">
                <h2 class="text-[2.8rem] font-light uppercase leading-[0.86] tracking-[0.06em] lg:text-[7.2rem]">
                    {{ $titleLine1 }}<br>{{ $titleLine2 }}
                </h2>
                <p class="pt-2 text-[0.9rem] tracking-[0.24em] text-gray-text">{{ $badge }}</p>
            </div>
            <div class="flex justify-between">
                <div>
                    <div class="mb-12 flex flex-wrap gap-6 w-full max-w-[680px]" data-aos="fade-up">
                        @foreach ($benefits as $benefit)
                            <article class="space-y-1">
                                <p class="text-[1.50rem] py-1 font-black leading-none">{{ $benefit['n'] }}</p>
                                <h3 class="text-[0.95rem] uppercase leading-[0.9] tracking-[0.03em]">{{ $benefit['title'] }}</h3>
                                <p class="text-[0.76rem] py-2 tracking-[0.08em] text-gray-text">{{ $benefit['text'] }}</p>
                            </article>
                        @endforeach
                    </div>

                    <div class="max-w-[680px] space-y-4 py-6" data-aos="fade-up">
                        <p class="text-[0.76rem] tracking-[0.08em] leading-relaxed text-gray-text">
                            {{ $spotlight?->description ? \Illuminate\Support\Str::limit(strip_tags($spotlight->description), 480) : $descriptionFallback }}
                        </p>
                        <p class="text-[1rem] font-bold uppercase tracking-[0.08em] text-white-brand">
                            {{ $spotlight?->name ?? 'NOEMA' }}
                        </p>
                    </div>

                    <p class="mt-14 text-[0.8rem] uppercase tracking-[0.28em] text-gray-text/80" data-aos="fade-up">{{ $madeWith }}</p>
                </div>
            </div>
        </div>
        <div class="h-full w-auto absolute z-10 right-0 top-0 overflow-hidden bg-black-brand" data-aos="fade-left">
            @if ($spotlight)
                <x-ui.media-image
                    :src="$spotlight->imageUrl()"
                    :alt="$spotlight->name"
                    wrapper-class="h-full min-h-[320px] w-auto"
                    class="h-full w-auto object-cover opacity-80"
                />
            @else
                <img src="{{ $fallbackImage }}" alt="NOEMA benefits visual"
                    class="h-full w-auto object-cover opacity-80" loading="lazy">
            @endif
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,transparent_42%,var(--brand-black-brand)_100%)]">
            </div>
        </div>
    </div>
</section>
