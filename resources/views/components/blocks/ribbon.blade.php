@props([
    'gallery' => collect(),
])

@php
    $galleryItems = $gallery->isNotEmpty()
        ? $gallery
        : collect();

    $imagesPerPanel = 6;
    $ribbonPanels = $galleryItems->isNotEmpty()
        ? $galleryItems->chunk($imagesPerPanel)->values()
        : collect([collect()]);

    $uniquePanelCount = max($ribbonPanels->count(), 1);
    $scrollHeightVh = (int) min(max($uniquePanelCount * 105, 300), 680);
@endphp

<section id="ribbon" class="scroll-ribbon relative w-full bg-white-brand" data-nav-theme="light"
    style="height: {{ $scrollHeightVh }}vh">
    <div class="scroll-ribbon-sticky sticky top-0 flex h-[100svh] w-full flex-col overflow-hidden bg-white-brand">
        <div class="scroll-ribbon-vignette pointer-events-none absolute inset-0 z-20" aria-hidden="true"></div>
        <div class="scroll-ribbon-grain pointer-events-none absolute inset-0 z-20 opacity-[0.14]" aria-hidden="true"></div>

        <div class="relative z-10 min-h-0 flex-1 overflow-hidden">
            <div class="scroll-ribbon-track flex h-full w-max will-change-transform">
                @foreach ($ribbonPanels->concat($ribbonPanels) as $panelIndex => $panelItems)
                    <div class="scroll-ribbon-panel flex h-full w-max shrink-0 items-stretch" data-panel="{{ $panelIndex }}">
                        @foreach ($panelItems as $cellIndex => $item)
                            @php
                                $imageUrl = $item['url'] ?? asset('storage/'.($item['path'] ?? 'images/mask/m1.png'));
                                $imageAlt = $item['alt'] ?? 'NOEMA gallery';
                                $width = (int) ($item['width'] ?? 900);
                                $height = (int) ($item['height'] ?? 1200);
                                $aspectRatio = $width.' / '.$height;
                            @endphp
                            <div class="scroll-ribbon-cell relative h-full shrink-0 overflow-hidden border-r border-white-brand bg-[#f2f2f2] last:border-r-0"
                                style="aspect-ratio: {{ $aspectRatio }};" data-cell="{{ $cellIndex }}"
                                data-aspect="{{ $width / max($height, 1) }}">
                                <div class="scroll-ribbon-cell-inner absolute inset-0 flex items-center justify-center">
                                    <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" width="{{ $width }}"
                                        height="{{ $height }}"
                                        class="scroll-ribbon-cell-media h-full w-full object-contain object-center"
                                        loading="lazy" draggable="false" decoding="async">
                                </div>
                                <div class="scroll-ribbon-cell-shine pointer-events-none absolute inset-0" aria-hidden="true"></div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div
            class="scroll-ribbon-logos relative z-10 w-full shrink-0 overflow-hidden border-t border-black-brand/8 bg-white-brand py-4 sm:py-5 lg:py-6">
            <div class="scroll-ribbon-logos-track flex w-max items-center gap-8 will-change-transform sm:gap-12 lg:gap-16">
                @foreach (range(1, 2) as $logoLoop)
                    @foreach (range(1, 12) as $logoRepeat)
                        <img src="{{ asset('storage/logo/BLACK_NOEMA.svg') }}" alt=""
                            class="scroll-ribbon-logo-solid h-[clamp(44px,8vw,130px)] w-auto shrink-0 select-none"
                            draggable="false" loading="lazy">
                        <img src="{{ asset('storage/logo/BORDER_BLACK_NOEMA.svg') }}" alt=""
                            class="scroll-ribbon-logo-outline h-[clamp(44px,8vw,130px)] w-auto shrink-0 select-none"
                            draggable="false" loading="lazy">
                    @endforeach
                @endforeach
            </div>
        </div>

        <div class="scroll-ribbon-progress relative z-30 px-4 pb-4 sm:px-8" aria-hidden="true">
            <div class="h-px w-full overflow-hidden bg-black-brand/10">
                <div class="scroll-ribbon-progress-bar h-full w-full origin-left scale-x-0 bg-black-brand"></div>
            </div>
        </div>
    </div>
</section>
