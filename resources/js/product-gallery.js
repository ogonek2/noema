const ZOOM_SCALE = 2.25;

const escapeHtml = (value) =>
    String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

let lightboxEl = null;

const mountLightboxToBody = () => {
    const lb = document.querySelector('[data-product-lightbox]');
    if (!lb) {
        return null;
    }

    if (lb.parentElement !== document.body) {
        document.body.appendChild(lb);
    }

    return lb;
};

const getLightbox = () => {
    if (!lightboxEl || !document.body.contains(lightboxEl)) {
        lightboxEl = mountLightboxToBody();
    }

    return lightboxEl;
};

export class ProductGallery {
    constructor(root) {
        this.root = root;
        this.stage = root.querySelector('[data-gallery-stage]');
        this.mainImg = root.querySelector('[data-gallery-main]');
        this.thumbsWrap = root.querySelector('#product-gallery-thumbs');
        this.openBtn = root.querySelector('[data-gallery-open]');
        this.images = [];
        this.alt = root.dataset.galleryAlt || '';
        this.activeIndex = 0;
        this._onStageMove = this._onStageMove.bind(this);
        this._onStageLeave = this._onStageLeave.bind(this);
        this._onStageClick = this._onStageClick.bind(this);
        this._onKeyDown = this._onKeyDown.bind(this);

        try {
            this.images = JSON.parse(root.dataset.galleryImages || '[]');
        } catch {
            this.images = [];
        }

        this._bind();
        this._bindLightboxGlobal();
    }

    _bind() {
        this.stage?.addEventListener('mousemove', this._onStageMove);
        this.stage?.addEventListener('mouseleave', this._onStageLeave);
        this.stage?.addEventListener('click', this._onStageClick);
        this.openBtn?.addEventListener('click', (event) => {
            event.stopPropagation();
            this.openLightbox(this.activeIndex);
        });

        this.root.querySelectorAll('.product-gallery-thumb').forEach((thumb) => {
            thumb.addEventListener('click', (event) => {
                event.stopPropagation();
                this.setIndex(Number(thumb.dataset.galleryIndex) || 0);
            });
        });
    }

    _onStageMove(event) {
        if (!this.mainImg || window.matchMedia('(max-width: 1023px)').matches) {
            return;
        }

        const rect = this.stage.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;

        this.mainImg.style.transformOrigin = `${x}% ${y}%`;
        this.mainImg.style.transform = `scale(${ZOOM_SCALE})`;
        this.stage.classList.add('is-zooming');
    }

    _onStageLeave() {
        if (!this.mainImg) {
            return;
        }

        this.mainImg.style.transform = '';
        this.mainImg.style.transformOrigin = 'center center';
        this.stage?.classList.remove('is-zooming');
    }

    _onStageClick() {
        this.openLightbox(this.activeIndex);
    }

    setImages(images, alt = null) {
        this.images = Array.isArray(images) ? images.filter(Boolean) : [];
        if (alt) {
            this.alt = alt;
            this.root.dataset.galleryAlt = alt;
        }
        this.activeIndex = 0;
        this._render();
    }

    setIndex(index) {
        if (!this.images.length) {
            return;
        }

        this.activeIndex = ((index % this.images.length) + this.images.length) % this.images.length;
        const url = this.images[this.activeIndex];

        if (this.mainImg && url) {
            this._onStageLeave();
            this.mainImg.src = url;
            this.mainImg.alt = this.alt;
        }

        this.root.querySelectorAll('.product-gallery-thumb').forEach((thumb, i) => {
            const active = i === this.activeIndex;
            thumb.classList.toggle('is-active', active);
            thumb.classList.toggle('border-black-brand', active);
            thumb.classList.toggle('border-black-brand/10', !active);
            thumb.setAttribute('aria-selected', active ? 'true' : 'false');
        });
    }

    _render() {
        const wrap = this.root.querySelector('#product-gallery-stage-wrap');
        const layout = this.root.querySelector('.product-gallery-layout');

        if (!this.images.length) {
            return;
        }

        if (this.mainImg) {
            this.mainImg.src = this.images[0];
            this.mainImg.alt = this.alt;
        }

        let thumbs = this.thumbsWrap;

        if (this.images.length <= 1) {
            thumbs?.remove();
            this.thumbsWrap = null;

            return;
        }

        if (!thumbs && layout && wrap) {
            const el = document.createElement('div');
            el.id = 'product-gallery-thumbs';
            el.className =
                'product-gallery-thumbs order-2 flex w-full min-w-0 max-w-full touch-pan-x gap-2 overflow-x-auto overscroll-x-contain pb-1 [-webkit-overflow-scrolling:touch] lg:order-1 lg:max-h-[min(72vh,720px)] lg:w-20 lg:flex-col lg:overflow-x-hidden lg:overflow-y-auto lg:pb-0';
            el.setAttribute('role', 'tablist');
            el.setAttribute('aria-label', 'Мініатюри');
            layout.insertBefore(el, wrap);
            thumbs = el;
            this.thumbsWrap = el;
        }

        if (!thumbs) {
            return;
        }

        thumbs.innerHTML = this.images
            .map(
                (url, index) => `
            <button type="button"
                class="product-gallery-thumb relative shrink-0 overflow-hidden border bg-black-brand/5 transition ${index === 0 ? 'is-active border-black-brand' : 'border-black-brand/10 hover:border-black-brand/40'}"
                style="aspect-ratio:1;width:4.5rem;"
                data-gallery-index="${index}"
                role="tab"
                aria-selected="${index === 0 ? 'true' : 'false'}"
                aria-label="Фото ${index + 1}">
                <img src="${escapeHtml(url)}" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
            </button>`,
            )
            .join('');

        thumbs.querySelectorAll('.product-gallery-thumb').forEach((thumb) => {
            thumb.addEventListener('click', (event) => {
                event.stopPropagation();
                this.setIndex(Number(thumb.dataset.galleryIndex) || 0);
            });
        });

        this.setIndex(0);
    }

    openLightbox(index = this.activeIndex) {
        const lb = getLightbox();
        if (!lb || !this.images.length) {
            return;
        }

        this._lightboxIndex = index;
        this._updateLightbox();
        lb.hidden = false;
        lb.setAttribute('aria-hidden', 'false');
        lb.classList.add('is-open');
        lb.classList.remove('pointer-events-none');
        document.body.classList.add('overflow-hidden');
        document.addEventListener('keydown', this._onKeyDown);
    }

    closeLightbox() {
        const lb = getLightbox();
        if (!lb) {
            return;
        }

        lb.classList.remove('is-open');
        lb.classList.add('pointer-events-none');
        lb.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        document.removeEventListener('keydown', this._onKeyDown);
        window.setTimeout(() => {
            if (!lb.classList.contains('is-open')) {
                lb.hidden = true;
            }
        }, 300);
    }

    _updateLightbox() {
        const lb = getLightbox();
        if (!lb) {
            return;
        }

        const img = lb.querySelector('[data-lightbox-image]');
        const caption = lb.querySelector('[data-lightbox-caption]');
        const counter = lb.querySelector('[data-lightbox-counter]');
        const url = this.images[this._lightboxIndex];

        if (img && url) {
            img.src = url;
            img.alt = this.alt;
        }

        if (caption) {
            caption.textContent = this.alt;
        }

        if (counter) {
            counter.textContent = `${this._lightboxIndex + 1} / ${this.images.length}`;
        }

        lb.querySelector('[data-lightbox-prev]')?.classList.toggle('hidden', this.images.length <= 1);
        lb.querySelector('[data-lightbox-next]')?.classList.toggle('hidden', this.images.length <= 1);
    }

    _bindLightboxGlobal() {
        const lb = getLightbox();
        if (!lb || lb.dataset.bound === 'true') {
            return;
        }

        lb.dataset.bound = 'true';
        lb.querySelector('[data-lightbox-close]')?.addEventListener('click', () => this.closeLightbox());
        lb.addEventListener('click', (event) => {
            if (event.target === lb) {
                this.closeLightbox();
            }
        });
        lb.querySelector('[data-lightbox-prev]')?.addEventListener('click', (event) => {
            event.stopPropagation();
            this._lightboxIndex =
                (this._lightboxIndex - 1 + this.images.length) % this.images.length;
            this._updateLightbox();
        });
        lb.querySelector('[data-lightbox-next]')?.addEventListener('click', (event) => {
            event.stopPropagation();
            this._lightboxIndex = (this._lightboxIndex + 1) % this.images.length;
            this._updateLightbox();
        });
    }

    _onKeyDown(event) {
        const lb = getLightbox();
        if (!lb?.classList.contains('is-open')) {
            return;
        }

        if (event.key === 'Escape') {
            this.closeLightbox();
        } else if (event.key === 'ArrowLeft') {
            this._lightboxIndex =
                (this._lightboxIndex - 1 + this.images.length) % this.images.length;
            this._updateLightbox();
        } else if (event.key === 'ArrowRight') {
            this._lightboxIndex = (this._lightboxIndex + 1) % this.images.length;
            this._updateLightbox();
        }
    }

    destroy() {
        this.stage?.removeEventListener('mousemove', this._onStageMove);
        this.stage?.removeEventListener('mouseleave', this._onStageLeave);
        this.stage?.removeEventListener('click', this._onStageClick);
        document.removeEventListener('keydown', this._onKeyDown);
    }
}

const galleryInstances = new WeakMap();

export const mountProductGallery = (root) => {
    if (!root) {
        return null;
    }

    mountLightboxToBody();

    const existing = galleryInstances.get(root);
    if (existing) {
        return existing;
    }

    const instance = new ProductGallery(root);
    galleryInstances.set(root, instance);

    return instance;
};

export const initProductGalleries = () => {
    mountLightboxToBody();

    document.querySelectorAll('[data-product-gallery]').forEach((root) => {
        mountProductGallery(root);
    });
};
