import { mountProductGallery } from './product-gallery';

const formatUah = (amount) => {
    const value = Number(amount) || 0;

    return `${new Intl.NumberFormat('uk-UA', { maximumFractionDigits: 0 }).format(value)} ₴`;
};

const setText = (id, value, fallback = '') => {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = value ?? fallback;
    }
};

const setHtml = (id, html) => {
    const el = document.getElementById(id);
    if (el) {
        el.innerHTML = html;
    }
};

const nl2br = (text) => {
    if (!text) {
        return '—';
    }

    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\n/g, '<br>');
};

/** Trusted HTML from admin RichEditor; plain fallback is escaped. */
const renderRichContent = (html, plainFallback = '') => {
    const content = String(html ?? '').trim();

    if (content) {
        return content;
    }

    const fallback = String(plainFallback ?? '').trim();

    if (!fallback) {
        return '—';
    }

    return nl2br(fallback);
};

const syncCartPreselectedSize = () => {
    const addBtn = document.getElementById('product-add-to-cart');
    const activeSize = document.querySelector('#product-sizes .product-size-btn[aria-pressed="true"]')?.dataset.size;

    if (addBtn) {
        if (activeSize) {
            addBtn.dataset.preselectedSize = activeSize;
        } else {
            delete addBtn.dataset.preselectedSize;
        }
    }
};

const renderSizes = (sizes, variants, basePrice) => {
    const wrap = document.getElementById('product-sizes');
    if (!wrap) {
        return;
    }

    if (!sizes?.length) {
        wrap.innerHTML = '';

        return;
    }

    wrap.innerHTML = sizes
        .map(
            (size, index) => `
        <button type="button"
            class="product-size-btn min-w-[3rem] border px-3 py-2.5 text-[0.68rem] uppercase tracking-[0.14em] transition ${index === 0 ? 'border-black-brand bg-black-brand text-white-brand' : 'border-black-brand/15 hover:border-black-brand/40'}"
            data-size="${size}"
            aria-pressed="${index === 0 ? 'true' : 'false'}">${size}</button>`,
        )
        .join('');

    const syncSku = () => {
        const activeSize = wrap.querySelector('.product-size-btn[aria-pressed="true"]')?.dataset.size;
        const match =
            variants.find((v) => v.size === activeSize) ||
            variants[0];
        if (match) {
            setText('product-variant-sku', `SKU: ${match.sku}`);
            setText('product-price', formatUah(match.price || basePrice));
        }
    };

    wrap.querySelectorAll('.product-size-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            wrap.querySelectorAll('.product-size-btn').forEach((b) => {
                b.classList.remove('border-black-brand', 'bg-black-brand', 'text-white-brand');
                b.classList.add('border-black-brand/15');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('border-black-brand', 'bg-black-brand', 'text-white-brand');
            btn.classList.remove('border-black-brand/15');
            btn.setAttribute('aria-pressed', 'true');
            syncSku();
            syncCartPreselectedSize();
        });
    });

    syncSku();
    syncCartPreselectedSize();
};

const renderDetails = (items) => {
    const wrap = document.getElementById('product-detail-items');
    if (!wrap) {
        return;
    }

    if (!items?.length) {
        wrap.innerHTML = '';

        return;
    }

    wrap.innerHTML = items
        .map(
            (item) => `
        <div class="border-t border-black-brand/10 pt-5">
            <dt class="text-[0.68rem] uppercase tracking-[0.18em] text-black-brand/50">${item.label}</dt>
            <dd class="mt-2 text-[0.92rem] leading-relaxed text-black-brand/75">${item.content ?? ''}</dd>
        </div>`,
        )
        .join('');
};

const syncProductExtras = () => {
    const wrap = document.querySelector('.product-page-extras');
    const chart = document.getElementById('product-size-chart-section');
    const colors = document.getElementById('product-model-colors');

    if (!wrap) {
        return;
    }

    const chartVisible = chart && !chart.classList.contains('hidden');
    const colorsVisible = colors && !colors.classList.contains('hidden');

    wrap.classList.toggle('hidden', !chartVisible && !colorsVisible);
};

const renderModelColorGallery = (items, currentSlug, modelName) => {
    const section = document.getElementById('product-model-colors');
    const track = document.getElementById('product-model-colors-track');

    if (!section || !track) {
        syncProductExtras();

        return;
    }

    if (!items?.length || items.length <= 1) {
        section.classList.add('hidden');
        syncProductExtras();

        return;
    }

    section.classList.remove('hidden');

    const title = section.querySelector('h2');
    if (title && modelName) {
        title.textContent = modelName;
    }

    track.innerHTML = items
        .map((item) => {
            const isCurrent = item.slug === currentSlug;
            const imageBlock = item.image
                ? `<img src="${item.image}" alt="${item.name}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy" decoding="async">`
                : item.hex
                  ? `<span class="absolute inset-0" style="background-color: ${item.hex}"></span>`
                  : '';
            const badge = isCurrent
                ? '<span class="absolute inset-x-0 bottom-0 bg-black-brand py-1.5 text-center text-[0.58rem] uppercase tracking-[0.16em] text-white-brand">Обрано</span>'
                : '';
            const swatch = item.hex
                ? `<span class="h-3 w-3 shrink-0 rounded-full border border-black-brand/15" style="background-color: ${item.hex}"></span>`
                : '';

            return `
            <button type="button"
                class="product-model-color-card product-color-btn group w-[7.5rem] shrink-0 snap-start text-left transition sm:w-[8.5rem] ${isCurrent ? 'is-active' : ''}"
                data-product-slug="${item.slug}"
                aria-pressed="${isCurrent ? 'true' : 'false'}"
                aria-label="${item.name}">
                <span class="relative block aspect-[3/4] overflow-hidden bg-black-brand/5 ring-1 ring-black-brand/10 transition group-hover:ring-black-brand/30 ${isCurrent ? 'ring-2 ring-black-brand' : ''}">
                    ${imageBlock}
                    ${badge}
                </span>
                <span class="mt-2 flex items-center gap-2">
                    ${swatch}
                    <span class="text-[0.65rem] uppercase tracking-[0.14em] text-black-brand/75 group-hover:text-black-brand">${item.name}</span>
                </span>
            </button>`;
        })
        .join('');

    syncProductExtras();
};

const renderSizeChart = (rows, intro) => {
    const section = document.getElementById('product-size-chart-section');
    const tbody = document.getElementById('product-size-chart-body');
    const introEl = document.getElementById('product-size-chart-intro');

    if (!section || !tbody) {
        return;
    }

    if (!rows?.length) {
        section.classList.add('hidden');
        syncProductExtras();

        return;
    }

    section.classList.remove('hidden');
    if (introEl) {
        introEl.textContent = intro || '';
        introEl.classList.toggle('hidden', !intro);
    }

    tbody.innerHTML = rows
        .map(
            (row) => `
        <tr class="border-b border-black-brand/8">
            <td class="py-3 pr-4 font-medium">${row.size_label}</td>
            <td class="py-3 pr-4 text-black-brand/70">${row.bust ?? '—'}</td>
            <td class="py-3 pr-4 text-black-brand/70">${row.waist ?? '—'}</td>
            <td class="py-3 pr-4 text-black-brand/70">${row.hip ?? '—'}</td>
            <td class="py-3 text-black-brand/70">${row.inseam ?? '—'}</td>
        </tr>`,
        )
        .join('');

    syncProductExtras();
};

const applyProductPayload = (data) => {
    document.title = data.meta_title;
    setText('product-title', data.name);
    setText('product-subtitle', data.subtitle || '');
    setText('product-short-description', data.short_description || '');
    setText('product-price', data.price);
    const compareEl = document.getElementById('product-compare-price');
    if (compareEl) {
        if (data.compare_at_price) {
            compareEl.textContent = data.compare_at_price;
            compareEl.classList.remove('hidden');
        } else {
            compareEl.classList.add('hidden');
        }
    }

    setHtml('product-panel-description', renderRichContent(data.description, data.short_description));
    setHtml('product-panel-fit', renderRichContent(data.fit_details));
    setHtml('product-panel-fabric', renderRichContent(data.fabric_details));
    setHtml('product-panel-care', renderRichContent(data.care_instructions));
    const fitSummary = document.getElementById('product-fit-summary');
    if (fitSummary) {
        fitSummary.textContent = data.fit_summary || '';
        fitSummary.classList.toggle('hidden', !data.fit_summary);
    }
    const fabricSummary = document.getElementById('product-fabric-summary');
    if (fabricSummary) {
        fabricSummary.textContent = data.fabric_summary || '';
        fabricSummary.classList.toggle('hidden', !data.fabric_summary);
    }

    if (window.__productGallery) {
        window.__productGallery.setImages(data.gallery, data.name);
    }
    renderSizes(data.sizes, data.variants, data.price_raw);
    renderDetails(data.detail_items);
    renderSizeChart(data.size_chart, data.size_chart_intro);
    renderModelColorGallery(data.color_alternatives, data.slug, data.model_name);

    const picker = document.getElementById('product-variant-picker');
    if (picker) {
        picker.dataset.basePrice = String(data.price_raw);
        picker.dataset.variants = JSON.stringify(data.variants);
    }
};

export const initProductPage = () => {
    const root = document.getElementById('product-page');
    if (!root) {
        return;
    }

    const galleryRoot = root.querySelector('[data-product-gallery]');
    if (galleryRoot) {
        window.__productGallery = mountProductGallery(galleryRoot);
    }

    const dataUrlTemplate = root.dataset.dataUrlTemplate;
    let currentSlug = root.dataset.currentSlug;
    let loading = false;

    const setLoading = (state) => {
        loading = state;
        root.classList.toggle('is-loading', state);
    };

    const setActiveColor = (slug) => {
        document.querySelectorAll('.product-color-btn').forEach((btn) => {
            const active = btn.dataset.productSlug === slug;
            if (!btn.classList.contains('product-model-color-card')) {
                btn.classList.toggle('border-black-brand', active);
                btn.classList.toggle('bg-black-brand', active);
                btn.classList.toggle('text-white-brand', active);
                btn.classList.toggle('border-black-brand/15', !active);
                btn.classList.toggle('text-black-brand', !active);
            }

            if (btn.classList.contains('product-model-color-card')) {
                btn.classList.toggle('is-active', active);
                const frame = btn.querySelector('span.relative');
                if (frame) {
                    frame.classList.toggle('ring-2', active);
                    frame.classList.toggle('ring-black-brand', active);
                    frame.classList.toggle('ring-1', !active);
                    frame.classList.toggle('ring-black-brand/10', !active);
                }
                const existingBadge = btn.querySelector('.product-model-color-badge');
                if (active && !existingBadge && frame) {
                    const badge = document.createElement('span');
                    badge.className =
                        'product-model-color-badge absolute inset-x-0 bottom-0 bg-black-brand py-1.5 text-center text-[0.58rem] uppercase tracking-[0.16em] text-white-brand';
                    badge.textContent = 'Обрано';
                    frame.appendChild(badge);
                }
                if (!active && existingBadge) {
                    existingBadge.remove();
                }
            }

            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    };

    const loadProduct = async (slug) => {
        if (loading || slug === currentSlug) {
            return;
        }

        const url = dataUrlTemplate.replace('__SLUG__', slug);
        setLoading(true);

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Failed to load product');
            }

            const data = await response.json();
            applyProductPayload(data);
            currentSlug = slug;
            setActiveColor(slug);
            syncCartPreselectedSize();
            const addBtn = document.getElementById('product-add-to-cart');
            if (addBtn) {
                addBtn.dataset.productSlug = slug;
            }
            window.history.replaceState({}, '', data.url);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    root.addEventListener('click', (event) => {
        const btn = event.target.closest('.product-color-btn');
        const slug = btn?.dataset?.productSlug;
        if (slug) {
            event.preventDefault();
            loadProduct(slug);
        }
    });

    try {
        const initial = JSON.parse(root.dataset.initialPayload || '{}');
        if (initial?.slug) {
            applyProductPayload(initial);
            syncCartPreselectedSize();
        }
    } catch {
        // ignore invalid JSON
    }

    const showProductTab = (tabId) => {
        document.querySelectorAll('.product-tab-btn').forEach((b) => {
            const active = b.dataset.tab === tabId;
            b.classList.toggle('text-black-brand', active);
            b.classList.toggle('text-black-brand/40', !active);
            b.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        document.querySelectorAll('.product-tab-panel').forEach((panel) => {
            const active = panel.dataset.panel === tabId;
            panel.classList.toggle('hidden', !active);
            panel.hidden = false;
        });
    };

    document.querySelectorAll('.product-tab-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            showProductTab(btn.dataset.tab);
        });
    });
};
