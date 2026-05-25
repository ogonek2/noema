{{-- Portal target: must stay a direct child of <body> (see product-gallery.js). --}}
<div class="product-lightbox pointer-events-none fixed inset-0 z-[100] flex h-[100dvh] w-full items-center justify-center bg-black-brand/95 opacity-0 transition-opacity duration-300"
    data-product-lightbox hidden aria-hidden="true" role="dialog" aria-modal="true" aria-label="Галерея товару">
    <button type="button" class="product-lightbox-close absolute right-4 top-4 z-10 p-2 text-white-brand/80 transition hover:text-white-brand lg:right-8 lg:top-8"
        data-lightbox-close aria-label="Закрити">
        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M6 6l12 12M18 6L6 18" />
        </svg>
    </button>
    <button type="button" class="product-lightbox-prev absolute left-2 top-1/2 z-10 -translate-y-1/2 p-3 text-white-brand/70 transition hover:text-white-brand lg:left-6"
        data-lightbox-prev aria-label="Попереднє">
        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M14 6l-6 6 6 6" />
        </svg>
    </button>
    <button type="button" class="product-lightbox-next absolute right-2 top-1/2 z-10 -translate-y-1/2 p-3 text-white-brand/70 transition hover:text-white-brand lg:right-6"
        data-lightbox-next aria-label="Наступне">
        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M10 6l6 6-6 6" />
        </svg>
    </button>
    <figure class="relative mx-auto flex max-h-[90dvh] max-w-[min(96vw,1200px)] flex-col items-center px-12 lg:px-20">
        <img src="" alt="" class="max-h-[85dvh] w-auto max-w-full object-contain" data-lightbox-image>
        <figcaption class="mt-4 text-center text-[0.72rem] tracking-[0.18em] text-white-brand/60" data-lightbox-caption></figcaption>
        <p class="mt-2 text-[0.65rem] tracking-[0.2em] text-white-brand/40" data-lightbox-counter></p>
    </figure>
</div>
