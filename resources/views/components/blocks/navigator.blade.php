@php
    $cartItems = (int) ($cartCount ?? 0);
    $cartItemsMod10 = $cartItems % 10;
    $cartItemsMod100 = $cartItems % 100;
    $cartItemsLabel = match (true) {
        $cartItemsMod10 === 1 && $cartItemsMod100 !== 11 => 'товар',
        $cartItemsMod10 >= 2 && $cartItemsMod10 <= 4 && ($cartItemsMod100 < 12 || $cartItemsMod100 > 14) => 'товари',
        default => 'товарів',
    };

    $navLinks = $navContent['links'] ?? [
        ['label' => 'ПРО БРЕНД', 'href' => route('home').'#about-us'],
        ['label' => 'КАТАЛОГ', 'href' => route('catalog.index')],
        ['label' => 'ПРОДУКТ', 'href' => route('home').'#product'],
        ['label' => 'ПЕРЕВАГИ', 'href' => '#benefits'],
        ['label' => 'ДЛЯ КОГО', 'href' => '#audience'],
        ['label' => 'ВІДГУКИ', 'href' => '#reviews'],
    ];
@endphp

<header id="site-nav"
    class="fixed inset-x-0 top-0 z-50 flex flex-col border-b border-transparent transition-all duration-500">
    <div id="site-nav-shell"
        class="mx-auto flex h-24 w-full max-w-layout items-center justify-between px-5 transition-all duration-500 lg:h-28 lg:px-8">
        <div>
            <a href="{{ route('home') }}">
                <img id="site-nav-logo" src="{{ asset('storage/logo/WHITE_NOEMA.svg') }}" alt="NOEMA"
                    class="w-full max-w-[100px] transition duration-300">
            </a>
        </div>

        <div>
            <nav class="hidden items-center gap-10 text-[0.58rem] px-4 tracking-[0.2em] lg:flex" aria-label="Main navigation">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}" class="hover:text-gray-text transition-all duration-300">{{ $link['label'] }}</a>
                @endforeach
            </nav>
        </div>

        <div class="flex items-center gap-3 lg:gap-4">
            <button id="nav-burger" type="button"
                class="inline-flex h-11 w-11 flex-col items-center justify-center gap-[5px] rounded-full border border-current lg:hidden"
                aria-label="Відкрити меню" aria-expanded="false" aria-controls="mobile-nav-drawer">
                <span class="nav-burger-line block h-px w-4 bg-current transition-all duration-300"></span>
                <span class="nav-burger-line block h-px w-4 bg-current transition-all duration-300"></span>
            </button>
        </div>
    </div>

    <a href="{{ route('cart.index') }}" class="mobile-cart-bar lg:hidden" data-floating-cart
        @if ($cartItems <= 0) hidden @endif
        aria-label="Перейти в кошик">
        <span class="mobile-cart-bar__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                <path d="M7.5 8.5V7a4.5 4.5 0 0 1 9 0v1.5" stroke-linecap="round" />
                <path d="M6 8.5h12l-1.1 10.2H7.1L6 8.5z" stroke-linejoin="round" />
            </svg>
        </span>
        <span class="mobile-cart-bar__text">
            Кошик · <span data-floating-cart-count>{{ $cartItems }}</span>
            <span data-floating-cart-label>{{ $cartItemsLabel }}</span>
        </span>
        <span class="mobile-cart-bar__cta" aria-hidden="true">→</span>
    </a>

    <div id="mobile-nav-overlay"
        class="pointer-events-none fixed inset-0 z-[60] bg-black/35 opacity-0 backdrop-blur-[2px] transition-opacity duration-300 lg:hidden"
        aria-hidden="true"></div>

    <aside id="mobile-nav-drawer"
        class="pointer-events-none fixed inset-0 z-[70] flex h-screen w-screen translate-x-full flex-col gap-8 border-l border-white/15 bg-black/95 px-6 pb-8 pt-6 text-[0.82rem] tracking-[0.2em] text-white opacity-0 backdrop-blur-xl transition-all duration-500 lg:hidden"
        aria-hidden="true">
        <div class="mb-4 flex items-center justify-between">
            <span class="text-[0.72rem] tracking-[0.22em] text-gray-text">[ MENU ]</span>
            <button id="mobile-nav-close" type="button"
                class="group relative inline-flex h-11 w-11 scale-75 -rotate-90 items-center justify-center rounded-full border border-white/25 opacity-0 transition-all duration-400"
                aria-label="Закрити меню">
                <span
                    class="mobile-close-line absolute block h-px w-5 origin-center translate-y-2 rotate-0 bg-white transition-all duration-500"></span>
                <span
                    class="mobile-close-line absolute block h-px w-5 origin-center -translate-y-2 rotate-0 bg-white transition-all duration-500"></span>
            </button>
        </div>
        <a href="{{ route('home') }}">NOEMA</a>
        @foreach ($navLinks as $link)
            <a href="{{ $link['href'] }}">{{ $link['label'] }}</a>
        @endforeach
        <a href="{{ route('cart.index') }}">КОШИК (<span data-cart-count>{{ $cartItems }}</span>)</a>
    </aside>
</header>
