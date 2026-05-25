@props([
    'catalogs' => collect(),
    'content' => [],
])

@php
    $leadCatalog = $catalogs->first();
    $badge = $content['badge'] ?? '[ NOEMA ]';
    $titleLine1 = $content['title_line1'] ?? 'Про';
    $titleLine2 = $content['title_line2'] ?? 'бренд';
    $paragraph1 = $leadCatalog?->description ?? ($content['paragraph_1'] ?? 'NOEMA — преміальний бренд медичного одягу для лікарів, які працюють довгі зміни і цінують посадку, тканину та стриманий професійний вигляд.');
    $paragraph2 = $catalogs->count() > 1
        ? 'Колекції '.$catalogs->pluck('name')->join(', ', ' та ').' — створені для реальних вимог клінік та операційних.'
        : ($content['paragraph_2'] ?? 'Ми поєднуємо еластичні тканини, міцні шви та мінімалістичний дизайн, щоб форма працювала стільки ж, скільки й ви.');
    $ctaPrimary = $content['cta_primary'] ?? 'Обрати костюм';
    $ctaPrimaryHref = filled($content['cta_primary_href'] ?? null) ? $content['cta_primary_href'] : route('catalog.index');
    $ctaSecondary = $content['cta_secondary'] ?? 'Каталог';
    $ctaSecondaryHref = filled($content['cta_secondary_href'] ?? null) ? $content['cta_secondary_href'] : route('catalog.index');
    $footerNote = $content['footer_note'] ?? 'Преміум костюми для медиків — від комплектів до аксесуарів. Доставка по Україні.';
@endphp

<section id="about-us" class="w-full bg-white-brand py-16 text-black-brand lg:py-24" data-nav-theme="light"
    data-aos="fade-up">
    <div class="mb-8 mx-auto flex max-w-layout flex-col-reverse items-center justify-between gap-4 px-5 text-center lg:px-8"
        data-aos="fade-up" data-aos-delay="120">
        <h2 class="text-[5.75rem] font-thin uppercase leading-[0.9] tracking-[0.06em] text-black-brand lg:text-[7.4rem]">
            {{ $titleLine1 }}<br>{{ $titleLine2 }}
        </h2>
        <p class="text-[0.9rem] tracking-[0.28em] text-black-brand">{{ $badge }}</p>
    </div>

    <div class="relative mx-auto flex w-full max-w-layout justify-center gap-12 px-5 lg:gap-16 lg:px-8"
        data-aos="fade-up" data-aos-delay="180">
        <div class="flex w-full max-w-[980px] flex-col items-center justify-center text-center" data-aos="fade-up"
            data-aos-delay="220">
            <div class="w-full space-y-8 text-[1rem] leading-relaxed text-black-brand lg:text-[1.25rem]"
                data-aos="fade-up" data-aos-delay="260">
                <p>{{ $paragraph1 }}</p>
                <p>{{ $paragraph2 }}</p>
            </div>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3" data-aos="fade-up" data-aos-delay="300">
                <a href="{{ $ctaPrimaryHref }}"
                    class="min-w-[210px] cursor-pointer border border-blue-brand bg-blue-brand px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] text-white-brand transition-colors duration-300 hover:bg-white-brand hover:text-blue-brand">
                    {{ $ctaPrimary }}
                </a>
                <a href="{{ $ctaSecondaryHref }}"
                    class="min-w-[210px] cursor-pointer border border-black-brand bg-white-brand px-8 py-4 text-xs font-medium uppercase tracking-[0.16em] text-black-brand transition-colors duration-300 hover:bg-black-brand hover:text-white-brand">
                    {{ $ctaSecondary }}
                </a>
            </div>

            <p class="mt-10 max-w-[420px] text-[0.7rem] leading-relaxed tracking-[0.14em] text-black-brand"
                data-aos="fade-up" data-aos-delay="340">
                {{ $footerNote }}
            </p>
        </div>
    </div>
</section>
