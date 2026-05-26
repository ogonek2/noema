@props([
    'catalogs' => collect(),
    'content' => [],
])

@php
    $navigatorLinks = $content['navigator_links'] ?? [];

    $catalogLinks = $catalogs->map(fn ($catalog) => [
        'label' => $catalog->name,
        'href' => route('catalog.show', $catalog),
    ])->all();

    $legalLinks = $content['legal_links'] ?? [];

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

            @if(!empty($navigatorLinks))
                <div>
                    <h3 class="mb-5 text-[0.72rem] font-bold uppercase tracking-[0.2em] text-black-brand">
                        Навігатор
                    </h3>

                    <ul class="space-y-2.5">
                        @foreach ($navigatorLinks as $link)
                            <li>
                                <a href="{{ $link['href'] ?? '#' }}"
                                    class="text-[0.76rem] leading-snug text-black-brand underline underline-offset-[3px] transition-colors duration-300 hover:text-black-brand/55">
                                    {{ $link['label'] ?? '' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($catalogLinks))
                <div>
                    <h3 class="mb-5 text-[0.72rem] font-bold uppercase tracking-[0.2em] text-black-brand">
                        Каталог
                    </h3>

                    <ul class="space-y-2.5">
                        @foreach ($catalogLinks as $link)
                            <li>
                                <a href="{{ $link['href'] ?? '#' }}"
                                    class="text-[0.76rem] leading-snug text-black-brand underline underline-offset-[3px] transition-colors duration-300 hover:text-black-brand/55">
                                    {{ $link['label'] ?? '' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(
                $phone1 ||
                $phone2 ||
                $email ||
                $officeTitle ||
                $officeAddress ||
                $partnersTitle ||
                $partnersAddress
            )
                <div class="space-y-5">

                    <h3 class="text-[0.72rem] font-bold uppercase tracking-[0.2em] text-black-brand">
                        Контакти
                    </h3>

                    @if($phone1 || $phone2 || $email)
                        <div class="space-y-1 text-[0.76rem] leading-relaxed text-black-brand">

                            @if($phone1)
                                <p>
                                    <a href="tel:{{ preg_replace('/\D+/', '', $phone1) }}"
                                        class="hover:text-black-brand/60">
                                        {{ $phone1 }}
                                    </a>
                                </p>
                            @endif

                            @if($phone2)
                                <p>
                                    <a href="tel:{{ preg_replace('/\D+/', '', $phone2) }}"
                                        class="hover:text-black-brand/60">
                                        {{ $phone2 }}
                                    </a>
                                </p>
                            @endif

                            @if($email)
                                <p class="pt-1">
                                    <a href="mailto:{{ $email }}"
                                        class="underline underline-offset-[3px] hover:text-black-brand/60">
                                        {{ $email }}
                                    </a>
                                </p>
                            @endif

                        </div>
                    @endif

                    @if($officeTitle || $officeAddress)
                        <div class="space-y-1 text-[0.76rem] leading-relaxed text-black-brand/80">

                            @if($officeTitle)
                                <p class="font-bold text-black-brand">
                                    {{ $officeTitle }}
                                </p>
                            @endif

                            @if($officeAddress)
                                <p>
                                    {{ $officeAddress }}
                                </p>
                            @endif

                        </div>
                    @endif

                    @if($partnersTitle || $partnersAddress)
                        <div class="space-y-1 text-[0.76rem] leading-relaxed text-black-brand/80">

                            @if($partnersTitle)
                                <p class="font-bold text-black-brand">
                                    {{ $partnersTitle }}
                                </p>
                            @endif

                            @if($partnersAddress)
                                <p>
                                    {{ $partnersAddress }}
                                </p>
                            @endif

                        </div>
                    @endif

                </div>
            @endif

        </div>

        @if($copyright || !empty($legalLinks))
            <div
                class="mt-14 flex flex-col gap-6 pt-8 text-[0.7rem] leading-relaxed text-black-brand/55 lg:mt-16 lg:flex-row lg:items-center lg:justify-between">

                @if($copyright)
                    <div class="space-y-1">
                        <p>
                            {{ $copyright }} {{ date('Y') }}
                        </p>
                    </div>
                @endif

                @if(!empty($legalLinks))
                    <ul class="flex flex-wrap gap-x-6 gap-y-2 lg:justify-end">

                        @foreach ($legalLinks as $link)
                            <li>
                                <a href="{{ $link['href'] ?? '#' }}"
                                    class="underline underline-offset-[3px] transition-colors duration-300 hover:text-black-brand">
                                    {{ $link['label'] ?? '' }}
                                </a>
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
