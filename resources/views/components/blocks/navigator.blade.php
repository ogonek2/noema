<header id="site-nav"
    class="fixed inset-x-0 top-0 z-50 border-b border-transparent text-white transition-all duration-500">
    <div id="site-nav-shell"
        class="mx-auto flex h-24 w-full max-w-layout items-center justify-between px-5 transition-all duration-500 lg:h-28 lg:px-8">
        <div>
            <a href="{{ route('home') }}">
                <img src="{{ asset('storage/logo/WHITE_NOEMA.svg') }}" alt="NOEMA" class="w-full max-w-[100px]">
            </a>
        </div>

        <div>
            <nav class="hidden items-center gap-10 text-[0.58rem] px-4 tracking-[0.2em] lg:flex" aria-label="Main navigation">
                <a href="#about" class="hover:text-gray-text transition-all duration-300">ПРО БРЕНД</a>
                <a href="#product" class="hover:text-gray-text transition-all duration-300">ПРОДУКТ</a>
                <a href="#benefits" class="hover:text-gray-text transition-all duration-300">ПЕРЕВАГИ</a>
                <a href="#audience" class="hover:text-gray-text transition-all duration-300">ДЛЯ КОГО</a>
                <a href="#reviews" class="hover:text-gray-text transition-all duration-300">ВІДГУКИ</a>
            </nav>
        </div>

        <div class="flex items-center gap-4">
            <a href="#cart" class="hidden text-[0.58rem] tracking-[0.2em] lg:block">
                КОШИК (0)
            </a>

            <button id="nav-burger" type="button"
                class="inline-flex h-11 w-11 flex-col items-center justify-center gap-[5px] rounded-full border border-current lg:hidden"
                aria-label="Відкрити меню" aria-expanded="false" aria-controls="mobile-nav-drawer">
                <span class="nav-burger-line block h-px w-4 bg-current transition-all duration-300"></span>
                <span class="nav-burger-line block h-px w-4 bg-current transition-all duration-300"></span>
            </button>
        </div>
    </div>

    <div id="mobile-nav-overlay"
        class="pointer-events-none fixed inset-0 z-[60] bg-black/35 opacity-0 backdrop-blur-[2px] transition-opacity duration-300 lg:hidden"
        aria-hidden="true"></div>

    <aside id="mobile-nav-drawer"
        class="pointer-events-none fixed right-0 top-0 z-[70] flex h-screen w-[min(86vw,360px)] translate-x-full flex-col gap-6 border-l border-white/15 bg-black/90 px-6 pb-8 pt-24 text-[0.7rem] tracking-[0.2em] text-white opacity-0 backdrop-blur-xl transition-all duration-300 lg:hidden"
        aria-hidden="true">
        <a href="#hero">NOEMA</a>
        <a href="#about">ПРО БРЕНД</a>
        <a href="#product">ПРОДУКТ</a>
        <a href="#benefits">ПЕРЕВАГИ</a>
        <a href="#audience">ДЛЯ КОГО</a>
        <a href="#reviews">ВІДГУКИ</a>
        <a href="#cart">КОШИК (0)</a>
    </aside>
</header>
