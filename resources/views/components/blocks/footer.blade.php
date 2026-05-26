@props([
    'catalogs' => collect(),
    'content' => [],
])

@php
    $footerGroups = collect($content['footer_groups'] ?? [])
        ->filter(fn ($group) => filled($group['title'] ?? null))
        ->values()
        ->all();
    $footerBottomItems = collect($content['footer_bottom_items'] ?? [])
        ->filter(fn ($item) => filled($item['label'] ?? null))
        ->values()
        ->all();

    $description = $content['description'] ?? null;

    $ctaPrimary = $content['cta_primary'] ?? null;
    $ctaPrimaryHref = $content['cta_primary_href'] ?? null;

    $ctaSecondary = $content['cta_secondary'] ?? null;
    $ctaSecondaryHref = $content['cta_secondary_href'] ?? null;

    $madeWith = $content['made_with'] ?? null;

    $phone1 = $content['phone_1'] ?? null;
    $phone2 = $content['phone_2'] ?? null;

    $email = $content['email'] ?? null;

    $officeTitle = $content['office_title'] ?? null;
    $officeAddress = $content['office_address'] ?? null;

    $partnersTitle = $content['partners_title'] ?? null;
    $partnersAddress = $content['partners_address'] ?? null;

    $copyright = $content['copyright'] ?? null;

    if ($footerGroups === []) {
        $navigatorLinks = collect($content['navigator_links'] ?? [])
            ->filter(fn ($item) => filled($item['label'] ?? null))
            ->values()
            ->all();
        $catalogLinks = $catalogs->map(fn ($catalog) => [
            'label' => $catalog->name,
            'href' => route('catalog.show', $catalog),
            'type' => 'link',
            'new_tab' => false,
        ])->all();
        $contactsGroupItems = collect([
            ['type' => 'link', 'label' => $phone1, 'href' => $phone1 ? 'tel:'.preg_replace('/\D+/', '', $phone1) : null],
            ['type' => 'link', 'label' => $phone2, 'href' => $phone2 ? 'tel:'.preg_replace('/\D+/', '', $phone2) : null],
            ['type' => 'link', 'label' => $email, 'href' => $email ? 'mailto:'.$email : null],
            ['type' => 'text', 'label' => $officeTitle],
            ['type' => 'text', 'label' => $officeAddress],
            ['type' => 'text', 'label' => $partnersTitle],
            ['type' => 'text', 'label' => $partnersAddress],
        ])->filter(fn ($item) => filled($item['label'] ?? null))->values()->all();

        $footerGroups = array_values(array_filter([
            ! empty($navigatorLinks) ? ['title' => 'Навігатор', 'items' => $navigatorLinks] : null,
            ! empty($catalogLinks) ? ['title' => 'Каталог', 'items' => $catalogLinks] : null,
            ! empty($contactsGroupItems) ? ['title' => 'Контакти', 'items' => $contactsGroupItems] : null,
        ]));
    }

    if ($footerBottomItems === []) {
        $footerBottomItems = collect($content['legal_links'] ?? [])
            ->map(fn ($link) => [
                'type' => 'link',
                'label' => $link['label'] ?? null,
                'href' => $link['href'] ?? null,
                'new_tab' => false,
            ])
            ->filter(fn ($item) => filled($item['label'] ?? null))
            ->values()
            ->all();
    }
@endphp

<footer id="site-footer"
    class="w-full bg-white-brand text-black-brand"
    data-nav-theme="light"
    data-aos="fade-up">

    <div class="mx-auto w-full max-w-layout px-5 py-14 lg:px-8 lg:py-20">

        <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-4 lg:gap-10 xl:gap-14">

            <div class="space-y-6 sm:col-span-2 lg:col-span-1">

                <a href="{{ route('home') }}" class="inline-block">
                    <img
                        src="{{ asset('storage/logo/BLACK_NOEMA.svg') }}"
                        alt="NOEMA"
                        class="h-auto w-full max-w-[180px] lg:max-w-[200px]">
                </a>

                @if($description)
                    <p class="max-w-[320px] text-[0.78rem] leading-relaxed text-black-brand/65">
                        {{ $description }}
                    </p>
                @endif

                @if(($ctaPrimary && $ctaPrimaryHref) || ($ctaSecondary && $ctaSecondaryHref))
                    <div class="flex flex-wrap items-center gap-4">

                        @if($ctaPrimary && $ctaPrimaryHref)
                            <a href="{{ $ctaPrimaryHref }}"
                                class="inline-flex min-w-[180px] items-center justify-center border border-black-brand bg-black-brand px-6 py-3.5 text-[0.68rem] font-medium uppercase tracking-[0.16em] text-white-brand transition-colors duration-300 hover:bg-white-brand hover:text-black-brand">
                                {{ $ctaPrimary }}
                            </a>
                        @endif

                        @if($ctaSecondary && $ctaSecondaryHref)
                            <a href="{{ $ctaSecondaryHref }}"
                                class="text-[0.68rem] font-medium uppercase tracking-[0.16em] text-black-brand underline underline-offset-4 transition-colors duration-300 hover:text-black-brand/60">
                                {{ $ctaSecondary }}
                            </a>
                        @endif

                    </div>
                @endif

                @if($madeWith)
                    <p class="text-[0.68rem] uppercase tracking-[0.28em] text-black-brand/40">
                        {{ $madeWith }}
                    </p>
                @endif

            </div>

            @foreach ($footerGroups as $group)
                @php
                    $items = collect($group['items'] ?? [])->filter(fn ($item) => filled($item['label'] ?? null));
                @endphp
                @if (filled($group['title'] ?? null) && $items->isNotEmpty())
                    <div>
                        <h3 class="mb-5 text-[0.72rem] font-bold uppercase tracking-[0.2em] text-black-brand">
                            {{ $group['title'] }}
                        </h3>

                        <ul class="space-y-2.5">
                            @foreach ($items as $item)
                                @php
                                    $isLink = ($item['type'] ?? 'link') === 'link' && filled($item['href'] ?? null);
                                    $label = $item['label'] ?? '';
                                    $newTab = (bool) ($item['new_tab'] ?? false);
                                @endphp
                                <li class="text-[0.76rem] leading-snug text-black-brand">
                                    @if ($isLink)
                                        <a href="{{ $item['href'] }}"
                                            @if($newTab) target="_blank" rel="noopener noreferrer" @endif
                                            class="underline underline-offset-[3px] transition-colors duration-300 hover:text-black-brand/55">
                                            {{ $label }}
                                        </a>
                                    @else
                                        <span class="text-black-brand/80">{{ $label }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

        </div>

        @if($copyright || !empty($footerBottomItems))
            <div
                class="mt-14 flex flex-col gap-6 pt-8 text-[0.7rem] leading-relaxed text-black-brand/55 lg:mt-16 lg:flex-row lg:items-center lg:justify-between">

                @if($copyright)
                    <div class="space-y-1">
                        <p>
                            {{ $copyright }} {{ date('Y') }}
                        </p>
                    </div>
                @endif

                @if(!empty($footerBottomItems))
                    <ul class="flex flex-wrap gap-x-6 gap-y-2 lg:justify-end">

                        @foreach ($footerBottomItems as $item)
                            @php
                                $isLink = ($item['type'] ?? 'link') === 'link' && filled($item['href'] ?? null);
                                $newTab = (bool) ($item['new_tab'] ?? false);
                            @endphp
                            <li>
                                @if ($isLink)
                                    <a href="{{ $item['href'] }}"
                                        @if($newTab) target="_blank" rel="noopener noreferrer" @endif
                                        class="underline underline-offset-[3px] transition-colors duration-300 hover:text-black-brand">
                                        {{ $item['label'] ?? '' }}
                                    </a>
                                @else
                                    <span>{{ $item['label'] ?? '' }}</span>
                                @endif
                            </li>
                        @endforeach

                    </ul>
                @endif

            </div>
        @endif

        <div class="relative mt-10 flex items-center justify-center lg:mt-12">
            <div class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-black-brand/15"
                aria-hidden="true"></div>

            <p class="relative bg-white-brand px-5 text-[0.72rem] font-bold uppercase tracking-[0.34em] text-black-brand">
                NOEMA
            </p>
        </div>

    </div>

</footer>
