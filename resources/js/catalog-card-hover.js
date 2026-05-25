const HOVER_INTERVAL_MS = 900;

export const initCatalogCardHover = () => {
    document.querySelectorAll('[data-catalog-card]').forEach((card) => {
        if (card.dataset.hoverBound === 'true') {
            return;
        }

        let images = [];

        try {
            images = JSON.parse(card.dataset.images || '[]');
        } catch {
            images = [];
        }

        images = images.filter(Boolean);

        if (images.length <= 1) {
            return;
        }

        const layers = card.querySelectorAll('[data-card-image-layer]');
        if (layers.length <= 1) {
            return;
        }

        let index = 0;
        let timer = null;

        const dots = card.querySelectorAll('.catalog-card-dot');

        const showIndex = (nextIndex) => {
            index = nextIndex % layers.length;
            layers.forEach((layer, i) => {
                layer.classList.toggle('opacity-100', i === index);
                layer.classList.toggle('opacity-0', i !== index);
                layer.classList.toggle('z-[1]', i === index);
                layer.classList.toggle('z-0', i !== index);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('w-3', i === index);
                dot.classList.toggle('w-1', i !== index);
                dot.classList.toggle('bg-white-brand', i === index);
                dot.classList.toggle('bg-white-brand/40', i !== index);
            });
        };

        const start = () => {
            if (timer) {
                return;
            }
            timer = window.setInterval(() => {
                showIndex(index + 1);
            }, HOVER_INTERVAL_MS);
        };

        const stop = () => {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
            showIndex(0);
        };

        card.addEventListener('mouseenter', start);
        card.addEventListener('mouseleave', stop);
        card.addEventListener('focusin', start);
        card.addEventListener('focusout', stop);
        card.dataset.hoverBound = 'true';
    });
};
