import './bootstrap';
import { initCart } from './cart';
import { initProductPage } from './product-page';
import { initCatalogCardHover } from './catalog-card-hover';
import AOS from 'aos';
import 'aos/dist/aos.css';
import Swiper from 'swiper';
import { Autoplay, Navigation } from 'swiper/modules';
import 'swiper/css';

const initMediaShells = () => {
    document.querySelectorAll('[data-media-shell] img[data-media-img]').forEach((img) => {
        if (img.complete && img.naturalWidth > 0) {
            img.classList.add('is-loaded');
            img.closest('[data-media-shell]')?.setAttribute('data-loaded', 'true');
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initMediaShells();
    initCart();
    initProductPage();
    initCatalogCardHover();

    AOS.init({
        once: true,
        duration: 700,
        easing: 'ease-out-cubic',
        offset: 80,
        startEvent: 'load',
    });

    const productCardsSwiper = document.querySelector('.product-cards-swiper');
    const audienceCardsSwiper = document.querySelector('.audience-cards-swiper');
    const reviewsSwiperEl = document.querySelector('.reviews-swiper');

    if (productCardsSwiper) {
        const productSwiper = new Swiper(productCardsSwiper, {
            modules: [Autoplay],
            slidesPerView: 1.15,
            spaceBetween: 0,
            loop: true,
            speed: 700,
            autoplay: {
                delay: 2600,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2.2,
                    spaceBetween: 0,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 0,
                },
            },
        });

        productSwiper.on('afterInit', () => {
            AOS.refreshHard();
        });
        productSwiper.on('slideChangeTransitionEnd', () => {
            AOS.refresh();
        });
    }

    if (audienceCardsSwiper) {
        const audienceSwiper = new Swiper(audienceCardsSwiper, {
            modules: [Autoplay],
            slidesPerView: 1.35,
            spaceBetween: 10,
            loop: true,
            speed: 700,
            autoplay: {
                delay: 2800,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            breakpoints: {
                640: {
                    slidesPerView: 2.2,
                    spaceBetween: 10,
                },
                1024: {
                    slidesPerView: 5,
                    spaceBetween: 10,
                },
            },
        });

        audienceSwiper.on('afterInit', () => {
            AOS.refreshHard();
        });
        audienceSwiper.on('slideChangeTransitionEnd', () => {
            AOS.refresh();
        });
    }

    if (reviewsSwiperEl) {
        const reviewsPagination = document.querySelector('.reviews-pagination');

        const renderReviewsPagination = (swiper) => {
            if (!reviewsPagination) {
                return;
            }

            const total = swiper.snapGrid.length;
            reviewsPagination.innerHTML = '';

            for (let index = 0; index < total; index += 1) {
                const segment = document.createElement('span');
                segment.className =
                    'reviews-pagination-segment h-px transition-all duration-500 ease-out bg-white-brand/20';
                reviewsPagination.appendChild(segment);
            }
        };

        const updateReviewsPagination = (swiper) => {
            if (!reviewsPagination) {
                return;
            }

            const segments = reviewsPagination.querySelectorAll('.reviews-pagination-segment');
            const activeIndex = swiper.snapIndex;

            segments.forEach((segment, index) => {
                const isActive = index === activeIndex;
                segment.classList.toggle('flex-[3]', isActive);
                segment.classList.toggle('flex-1', !isActive);
                segment.classList.toggle('bg-white-brand', isActive);
                segment.classList.toggle('bg-white-brand/20', !isActive);
            });
        };

        const reviewsSwiper = new Swiper(reviewsSwiperEl, {
            modules: [Navigation],
            slidesPerView: 1.08,
            spaceBetween: 14,
            speed: 650,
            breakpoints: {
                640: {
                    slidesPerView: 1.35,
                    spaceBetween: 16,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                },
            },
            navigation: {
                nextEl: '.reviews-swiper-next',
                prevEl: '.reviews-swiper-prev',
            },
            on: {
                init(swiper) {
                    renderReviewsPagination(swiper);
                    updateReviewsPagination(swiper);
                },
                resize(swiper) {
                    renderReviewsPagination(swiper);
                    updateReviewsPagination(swiper);
                },
                slideChange(swiper) {
                    updateReviewsPagination(swiper);
                },
            },
        });

        reviewsSwiper.on('afterInit', () => {
            AOS.refreshHard();
        });
        reviewsSwiper.on('slideChangeTransitionEnd', () => {
            AOS.refresh();
        });
    }

    const audienceSection = document.getElementById('audience');
    const audienceCards = [...document.querySelectorAll('.audience-card-inner')];

    if (audienceSection && audienceCards.length > 0 && 'IntersectionObserver' in window) {
        const audienceRevealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    audienceCards.forEach((card, index) => {
                        card.style.transitionDelay = '0ms';
                        window.setTimeout(() => {
                            card.classList.remove('opacity-0', 'translate-y-6', 'scale-[0.985]');
                            card.classList.add('opacity-100', 'translate-y-0', 'scale-100');
                        }, index * 260);
                    });

                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.22,
            }
        );

        audienceRevealObserver.observe(audienceSection);
    }

    const ribbonSection = document.getElementById('ribbon');
    const ribbonTrack = document.querySelector('.scroll-ribbon-track');
    const ribbonProgressBar = document.querySelector('.scroll-ribbon-progress-bar');
    const ribbonLogosTrack = document.querySelector('.scroll-ribbon-logos-track');
    const ribbonCells = [...document.querySelectorAll('.scroll-ribbon-cell')];
    let ribbonTicking = false;
    let ribbonLogoLoopWidth = 0;

    const measureRibbonLogoLoop = () => {
        if (!ribbonLogosTrack) {
            ribbonLogoLoopWidth = 0;
            return;
        }

        ribbonLogoLoopWidth = ribbonLogosTrack.scrollWidth / 2;
    };

    const smoothStep = (value) => value * value * (3 - 2 * value);

    const resetRibbonEffects = () => {
        ribbonTrack.style.transform = 'translate3d(0, 0, 0)';
        if (ribbonLogosTrack) {
            ribbonLogosTrack.style.transform = '';
        }

        if (ribbonProgressBar) {
            ribbonProgressBar.style.transform = 'scaleX(0)';
        }

        ribbonCells.forEach((cell) => {
            const inner = cell.querySelector('.scroll-ribbon-cell-inner');
            const media = cell.querySelector('.scroll-ribbon-cell-media');
            const shine = cell.querySelector('.scroll-ribbon-cell-shine');

            cell.style.opacity = '';
            cell.style.zIndex = '';

            if (inner) {
                inner.style.transform = '';
                inner.style.filter = '';
            }

            if (media) {
                media.style.transform = '';
                media.style.filter = '';
            }

            if (shine) {
                shine.style.opacity = '';
            }
        });
    };

    const isRibbonMobile = () => window.matchMedia('(max-width: 767px)').matches;

    const updateRibbonCells = (scrollProgress) => {
        const viewportCenter = window.innerWidth * 0.5;
        const viewportHeight = window.innerHeight;
        const influenceRadius = Math.max(window.innerWidth * 0.62, 360);
        const mobile = isRibbonMobile();

        ribbonCells.forEach((cell) => {
            const inner = cell.querySelector('.scroll-ribbon-cell-inner');
            const media = cell.querySelector('.scroll-ribbon-cell-media');
            const shine = cell.querySelector('.scroll-ribbon-cell-shine');

            if (!inner || !media) {
                return;
            }

            if (mobile) {
                cell.style.opacity = '1';
                cell.style.zIndex = '';
                inner.style.transform = '';
                inner.style.filter = 'none';
                media.style.transform = '';
                media.style.filter = 'none';

                if (shine) {
                    shine.style.opacity = '0';
                }

                return;
            }

            const rect = cell.getBoundingClientRect();
            const cellCenterX = rect.left + rect.width * 0.5;
            const cellCenterY = rect.top + rect.height * 0.5;
            const distanceX = Math.abs(cellCenterX - viewportCenter);
            const proximity = 1 - Math.min(distanceX / influenceRadius, 1);
            const eased = smoothStep(proximity);
            const isActive = proximity > 0.72;

            const parallaxX = ((cellCenterX - viewportCenter) / Math.max(viewportCenter, 1)) * 18;
            const parallaxY = ((cellCenterY - viewportHeight * 0.5) / viewportHeight) * 10;
            const scale = 0.94 + eased * 0.1;
            const rotateY = ((cellCenterX - viewportCenter) / Math.max(viewportCenter, 1)) * 5;
            const blur = (1 - eased) * 2.5;
            const brightness = 0.88 + eased * 0.14;
            const saturate = 0.9 + eased * 0.12;
            const mediaScale = 1 + eased * 0.05;

            cell.style.opacity = `${0.58 + eased * 0.42}`;
            cell.style.zIndex = isActive ? '12' : '1';

            inner.style.transform = `translate3d(${parallaxX * 0.4}px, ${parallaxY * 0.25}px, 0) scale(${scale}) rotateY(${rotateY}deg)`;
            inner.style.filter = `blur(${blur}px)`;

            media.style.transform = `scale(${mediaScale})`;
            media.style.filter = `brightness(${brightness}) saturate(${saturate})`;

            if (shine) {
                shine.style.opacity = `${0.08 + eased * 0.35 + scrollProgress * 0.08}`;
            }
        });
    };

    const updateRibbonLogos = (shift) => {
        if (!ribbonLogosTrack || ribbonLogoLoopWidth <= 0) {
            return;
        }

        const logoOffset = shift % ribbonLogoLoopWidth;
        ribbonLogosTrack.style.transform = `translate3d(${-logoOffset}px, 0, 0)`;
    };

    const updateRibbon = () => {
        if (!ribbonSection || !ribbonTrack) {
            return;
        }

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (prefersReducedMotion) {
            resetRibbonEffects();
            ribbonTicking = false;
            return;
        }

        const scrollable = ribbonSection.offsetHeight - window.innerHeight;

        if (scrollable <= 0) {
            ribbonTicking = false;
            return;
        }

        const progress = Math.min(
            Math.max((window.scrollY - ribbonSection.offsetTop) / scrollable, 0),
            1
        );
        const loopWidth = ribbonTrack.scrollWidth / 2;
        const maxShift = Math.max(loopWidth, 0);
        const easedProgress = smoothStep(progress);
        const shift = easedProgress * maxShift;

        ribbonTrack.style.transform = `translate3d(${-shift}px, 0, 0)`;

        if (ribbonProgressBar) {
            ribbonProgressBar.style.transform = `scaleX(${Math.max(easedProgress, 0.02)})`;
        }

        updateRibbonLogos(shift);
        updateRibbonCells(easedProgress);
        ribbonTicking = false;
    };

    const queueRibbonUpdate = () => {
        if (ribbonTicking) {
            return;
        }

        ribbonTicking = true;
        requestAnimationFrame(updateRibbon);
    };

    if (ribbonSection && ribbonTrack) {
        measureRibbonLogoLoop();
        window.addEventListener('scroll', queueRibbonUpdate, { passive: true });
        window.addEventListener('resize', () => {
            measureRibbonLogoLoop();
            queueRibbonUpdate();
        });
        window.addEventListener('load', () => {
            measureRibbonLogoLoop();
            queueRibbonUpdate();
        }, { once: true });
        queueRibbonUpdate();
    }

    window.addEventListener(
        'load',
        () => {
            AOS.refreshHard();
        },
        { once: true }
    );

    const nav = document.getElementById('site-nav');
    const navShell = document.getElementById('site-nav-shell');
    const navLogo = document.getElementById('site-nav-logo');
    const burger = document.getElementById('nav-burger');
    const mobileClose = document.getElementById('mobile-nav-close');
    const mobileDrawer = document.getElementById('mobile-nav-drawer');
    const mobileOverlay = document.getElementById('mobile-nav-overlay');
    let mobileCloseAnimationTimeout;

    if (!nav || !navShell) {
        return;
    }

    let activeSectionTheme = 'dark';

    const applyNavTheme = () => {
        const compact = window.scrollY > 40;
        const atTop = window.scrollY <= 0;
        const sectionIsLight = activeSectionTheme === 'light';

        // Invert nav colors relative to section background.
        nav.classList.remove('text-white');
        nav.classList.toggle('bg-black/80', sectionIsLight || atTop);
        nav.classList.toggle('text-white-brand', sectionIsLight || atTop);
        nav.classList.toggle('border-white/10', sectionIsLight || atTop);

        nav.classList.toggle('bg-white/85', !sectionIsLight && !atTop);
        nav.classList.toggle('text-black-brand', !sectionIsLight && !atTop);
        nav.classList.toggle('border-black/10', !sectionIsLight && !atTop);

        nav.classList.toggle('backdrop-blur-xl', compact);
        nav.classList.toggle('border-transparent', false);
        navLogo?.classList.toggle('invert', !sectionIsLight && !atTop);
    };

    const setCompactState = () => {
        const compact = window.scrollY > 40;

        navShell.classList.toggle('h-16', compact);
        navShell.classList.toggle('lg:h-20', compact);
        navShell.classList.toggle('h-24', !compact);
        navShell.classList.toggle('lg:h-28', !compact);

        applyNavTheme();
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
        window.clearTimeout(mobileCloseAnimationTimeout);
        mobileClose?.classList.remove('opacity-100', 'scale-100', 'rotate-0');
        mobileClose?.classList.add('opacity-0', 'scale-75', '-rotate-90');

        const closeLines = mobileClose?.querySelectorAll('.mobile-close-line');
        closeLines?.[0]?.classList.remove('rotate-45', 'translate-y-0');
        closeLines?.[0]?.classList.add('rotate-0', 'translate-y-2');
        closeLines?.[1]?.classList.remove('-rotate-45', 'translate-y-0');
        closeLines?.[1]?.classList.add('rotate-0', '-translate-y-2');
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
            window.clearTimeout(mobileCloseAnimationTimeout);

            const closeLines = mobileClose?.querySelectorAll('.mobile-close-line');

            if (open) {
                mobileCloseAnimationTimeout = window.setTimeout(() => {
                    mobileClose?.classList.remove('opacity-0', 'scale-75', '-rotate-90');
                    mobileClose?.classList.add('opacity-100', 'scale-100', 'rotate-0');

                    closeLines?.[0]?.classList.remove('rotate-0', 'translate-y-2');
                    closeLines?.[0]?.classList.add('rotate-45', 'translate-y-0');
                    closeLines?.[1]?.classList.remove('rotate-0', '-translate-y-2');
                    closeLines?.[1]?.classList.add('-rotate-45', 'translate-y-0');
                }, 520);
            } else {
                mobileClose?.classList.remove('opacity-100', 'scale-100', 'rotate-0');
                mobileClose?.classList.add('opacity-0', 'scale-75', '-rotate-90');

                closeLines?.[0]?.classList.remove('rotate-45', 'translate-y-0');
                closeLines?.[0]?.classList.add('rotate-0', 'translate-y-2');
                closeLines?.[1]?.classList.remove('-rotate-45', 'translate-y-0');
                closeLines?.[1]?.classList.add('rotate-0', '-translate-y-2');
            }
        });

        mobileClose?.addEventListener('click', closeMobileMenu);

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

                activeSectionTheme = visibleEntries[0].target.dataset.navTheme ?? 'dark';
                applyNavTheme();
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
    window.addEventListener('resize', () => AOS.refresh());
    setCompactState();
});
