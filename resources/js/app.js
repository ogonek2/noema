import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('site-nav');
    const navShell = document.getElementById('site-nav-shell');
    const burger = document.getElementById('nav-burger');
    const mobileDrawer = document.getElementById('mobile-nav-drawer');
    const mobileOverlay = document.getElementById('mobile-nav-overlay');

    if (!nav || !navShell) {
        return;
    }

    const setCompactState = () => {
        const compact = window.scrollY > 40;

        nav.classList.toggle('bg-black/70', compact);
        nav.classList.toggle('backdrop-blur-xl', compact);
        nav.classList.toggle('border-white/10', compact);
        nav.classList.toggle('border-transparent', !compact);

        navShell.classList.toggle('h-16', compact);
        navShell.classList.toggle('lg:h-20', compact);
        navShell.classList.toggle('h-24', !compact);
        navShell.classList.toggle('lg:h-28', !compact);
    };

    const closeMobileMenu = () => {
        if (!burger || !mobileDrawer || !mobileOverlay) {
            return;
        }

        burger.classList.remove('is-open');
        burger.setAttribute('aria-expanded', 'false');
        mobileDrawer.classList.add('pointer-events-none', 'opacity-0', 'translate-x-full');
        mobileDrawer.classList.remove('pointer-events-auto', 'opacity-100', 'translate-x-0');
        mobileDrawer.setAttribute('aria-hidden', 'true');
        mobileOverlay.classList.add('pointer-events-none', 'opacity-0');
        mobileOverlay.classList.remove('pointer-events-auto', 'opacity-100');
        mobileOverlay.setAttribute('aria-hidden', 'true');

        const lines = burger.querySelectorAll('.nav-burger-line');
        lines[0]?.classList.remove('translate-y-[6px]', 'rotate-45');
        lines[1]?.classList.remove('-translate-y-[6px]', '-rotate-45');
    };

    if (burger && mobileDrawer && mobileOverlay) {
        burger.addEventListener('click', () => {
            const open = burger.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', open ? 'true' : 'false');
            mobileDrawer.classList.toggle('pointer-events-none', !open);
            mobileDrawer.classList.toggle('opacity-0', !open);
            mobileDrawer.classList.toggle('translate-x-full', !open);
            mobileDrawer.classList.toggle('pointer-events-auto', open);
            mobileDrawer.classList.toggle('opacity-100', open);
            mobileDrawer.classList.toggle('translate-x-0', open);
            mobileDrawer.setAttribute('aria-hidden', open ? 'false' : 'true');
            mobileOverlay.classList.toggle('pointer-events-none', !open);
            mobileOverlay.classList.toggle('opacity-0', !open);
            mobileOverlay.classList.toggle('pointer-events-auto', open);
            mobileOverlay.classList.toggle('opacity-100', open);
            mobileOverlay.setAttribute('aria-hidden', open ? 'false' : 'true');

            const lines = burger.querySelectorAll('.nav-burger-line');
            lines[0]?.classList.toggle('translate-y-[6px]', open);
            lines[0]?.classList.toggle('rotate-45', open);
            lines[1]?.classList.toggle('-translate-y-[6px]', open);
            lines[1]?.classList.toggle('-rotate-45', open);
        });

        mobileDrawer.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMobileMenu);
        });

        mobileOverlay.addEventListener('click', closeMobileMenu);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMobileMenu();
            }
        });
    }

    const sections = [...document.querySelectorAll('[data-nav-theme]')];

    if (sections.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                const visibleEntries = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

                if (visibleEntries.length === 0) {
                    return;
                }

                const theme = visibleEntries[0].target.dataset.navTheme;
                nav.classList.toggle('text-black-brand', theme === 'light');
                nav.classList.toggle('text-white', theme !== 'light');
            },
            {
                root: null,
                threshold: [0.2, 0.5, 0.75],
            }
        );

        sections.forEach((section) => observer.observe(section));
    }

    window.addEventListener('scroll', setCompactState, { passive: true });
    window.addEventListener('resize', closeMobileMenu);
    setCompactState();
});
