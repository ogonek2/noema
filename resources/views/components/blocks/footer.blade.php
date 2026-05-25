@props([
    'catalogs' => collect(),
    'content' => [],
])

@php
    $home = route('home');
    $navigatorLinks = $content['navigator_links'] ?? [
        ['label' => 'Про Нас \\ Хто ми', 'href' => $home.'#about-us'],
        ['label' => 'Каталог', 'href' => route('catalog.index')],
        ['label' => 'Продукт та опис', 'href' => $home.'#product'],
        ['label' => 'Переваги наших костюмів', 'href' => $home.'#benefits'],
        ['label' => 'Для кого ми виготовляємо', 'href' => $home.'#audience'],
        ['label' => 'Відгуки про нас', 'href' => $home.'#reviews'],
        ['label' => 'Каталог та галерея', 'href' => $home.'#ribbon'],
        ['label' => 'FAQ \\ Додаткові питання', 'href' => $home.'#statement'],
    ];

    $catalogLinks = $catalogs->isNotEmpty()
        ? $catalogs->map(fn ($catalog) => [
            'label' => $catalog->name,
            'href' => route('catalog.show', $catalog),
        ])->all()
        : [['label' => 'Каталог', 'href' => route('catalog.index')]];

    $legalLinks = $content['legal_links'] ?? [
        ['label' => 'Публічна оферта', 'href' => '#'],
        ['label' => 'Умови використання', 'href' => '#'],
        ['label' => 'Умови повернення', 'href' => '#'],
        ['label' => 'Політика конфіденційності', 'href' => '#'],
    ];

    $description = $content['description'] ?? '';
    $ctaPrimary = $content['cta_primary'] ?? 'Обрати костюм';
    $ctaPrimaryHref = filled($content['cta_primary_href'] ?? null) ? $content['cta_primary_href'] : route('catalog.index');
    $ctaSecondary = $content['cta_secondary'] ?? 'Консультація';
    $ctaSecondaryHref = filled($content['cta_secondary_href'] ?? null) ? $content['cta_secondary_href'] : route('catalog.index');
    $madeWith = $content['made_with'] ?? 'Made with Noema';
    $phone1 = $content['phone_1'] ?? '+380 (99) 999 99-99';
    $phone2 = $content['phone_2'] ?? '+380 (99) 999 99-99';
    $email = $content['email'] ?? 'office@email.com';
    $officeTitle = $content['office_title'] ?? 'Офіс';
    $officeAddress = $content['office_address'] ?? '';
    $partnersTitle = $content['partners_title'] ?? 'Партнери';
    $partnersAddress = $content['partners_address'] ?? '';
    $copyright = $content['copyright'] ?? 'Всі права захищені NOEMA';
@endphp

<footer id="site-footer" class="w-full bg-white-brand text-black-brand" data-nav-theme="light" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-14 lg:px-8 lg:py-20">
        <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-4 lg:gap-10 xl:gap-14">
            <div class="space-y-6 sm:col-span-2 lg:col-span-1">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('storage/logo/BLACK_NOEMA.svg') }}" alt="NOEMA"
                        class="h-auto w-full max-w-[180px] lg:max-w-[200px]">
                </a>
                <p class="max-w-[320px] text-[0.78rem] leading-relaxed text-black-brand/65">
                    {{ $description }}
                </p>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ $ctaPrimaryHref }}"
                        class="inline-flex min-w-[180px] items-center justify-center border border-black-brand bg-black-brand px-6 py-3.5 text-[0.68rem] font-medium uppercase tracking-[0.16em] text-white-brand transition-colors duration-300 hover:bg-white-brand hover:text-black-brand">
                        {{ $ctaPrimary }}
                    </a>
                    <a href="{{ $ctaSecondaryHref }}"
                        class="text-[0.68rem] font-medium uppercase tracking-[0.16em] text-black-brand underline underline-offset-4 transition-colors duration-300 hover:text-black-brand/60">
                        {{ $ctaSecondary }}
                    </a>
                </div>
                <p class="text-[0.68rem] uppercase tracking-[0.28em] text-black-brand/40">{{ $madeWith }}</p>
            </div>

            <div>
                <h3 class="mb-5 text-[0.72rem] font-bold uppercase tracking-[0.2em] text-black-brand">Навігатор</h3>
                <ul class="space-y-2.5">
                    @foreach ($navigatorLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}"
                                class="text-[0.76rem] leading-snug text-black-brand underline underline-offset-[3px] transition-colors duration-300 hover:text-black-brand/55">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="mb-5 text-[0.72rem] font-bold uppercase tracking-[0.2em] text-black-brand">Каталог</h3>
                <ul class="space-y-2.5">
                    @foreach ($catalogLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}"
                                class="text-[0.76rem] leading-snug text-black-brand underline underline-offset-[3px] transition-colors duration-300 hover:text-black-brand/55">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="space-y-5">
                <h3 class="text-[0.72rem] font-bold uppercase tracking-[0.2em] text-black-brand">Контакти</h3>
                <div class="space-y-1 text-[0.76rem] leading-relaxed text-black-brand">
                    <p><a href="tel:{{ preg_replace('/\D+/', '', $phone1) }}" class="hover:text-black-brand/60">{{ $phone1 }}</a></p>
                    <p><a href="tel:{{ preg_replace('/\D+/', '', $phone2) }}" class="hover:text-black-brand/60">{{ $phone2 }}</a></p>
                    <p class="pt-1">
                        <a href="mailto:{{ $email }}"
                            class="underline underline-offset-[3px] hover:text-black-brand/60">{{ $email }}</a>
                    </p>
                </div>
                <div class="space-y-1 text-[0.76rem] leading-relaxed text-black-brand/80">
                    <p class="font-bold text-black-brand">{{ $officeTitle }}</p>
                    <p>{{ $officeAddress }}</p>
                </div>
                <div class="space-y-1 text-[0.76rem] leading-relaxed text-black-brand/80">
                    <p class="font-bold text-black-brand">{{ $partnersTitle }}</p>
                    <p>{{ $partnersAddress }}</p>
                </div>
            </div>
        </div>

        <div
            class="mt-14 flex flex-col gap-6 pt-8 text-[0.7rem] leading-relaxed text-black-brand/55 lg:mt-16 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <p>{{ $copyright }} {{ date('Y') }}</p>
            </div>
            <ul class="flex flex-wrap gap-x-6 gap-y-2 lg:justify-end">
                @foreach ($legalLinks as $link)
                    <li>
                        <a href="{{ $link['href'] }}"
                            class="underline underline-offset-[3px] transition-colors duration-300 hover:text-black-brand">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="relative mt-10 flex items-center justify-center lg:mt-12">
            <div class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-black-brand/15" aria-hidden="true"></div>
            <p class="relative bg-white-brand px-5 text-[0.72rem] font-bold uppercase tracking-[0.34em] text-black-brand">
                NOEMA
            </p>
        </div>
    </div>
</footer>
