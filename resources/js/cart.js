const MAX_BATCH = 50;
const MIN_BATCH = 2;

const formatUsd = (amount) => {
    const value = Number(amount) || 0;

    return `$${new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(value)}`;
};

const escapeHtml = (value) =>
    String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

let modalEl = null;
let state = {
    config: null,
    configCache: {},
    mode: 'single',
    selectedSize: null,
    selectedVariantId: null,
    batchCount: 3,
    batchType: 'uniform',
    batchEntries: [],
    colorLoading: false,
};

const qs = (sel, root = document) => root.querySelector(sel);
const qsa = (sel, root = document) => [...root.querySelectorAll(sel)];

const updateNavCount = (count) => {
    const value = Number(count) || 0;

    document.querySelectorAll('[data-cart-count]').forEach((el) => {
        el.textContent = String(value);
    });

    const cartBar = document.querySelector('[data-floating-cart]');

    if (cartBar) {
        cartBar.hidden = value <= 0;
        document.body.classList.toggle('has-mobile-cart-bar', value > 0 && window.matchMedia('(max-width: 1023px)').matches);

        const countEl = cartBar.querySelector('[data-floating-cart-count]');

        if (countEl) {
            countEl.textContent = String(value);
        }

        const labelEl = cartBar.querySelector('[data-floating-cart-label]');

        if (labelEl) {
            const mod10 = value % 10;
            const mod100 = value % 100;
            let word = 'товарів';

            if (mod10 === 1 && mod100 !== 11) {
                word = 'товар';
            } else if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) {
                word = 'товари';
            }

            labelEl.textContent = word;
        }
    }

    document.dispatchEvent(new CustomEvent('cart:count-updated', { detail: { count: value } }));
};

const fetchSummary = async () => {
    try {
        const response = await fetch('/cart/summary', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) {
            return;
        }
        const data = await response.json();
        updateNavCount(data.count ?? 0);
    } catch {
        // ignore
    }
};

const getVariantForSize = (config, size) => {
    if (!config?.variants?.length) {
        return null;
    }

    return config.variants.find((variant) => variant.size === size) || config.variants[0];
};

const getSizes = (config) =>
    config?.sizes?.length
        ? config.sizes
        : [...new Set(config.variants.map((v) => v.size).filter(Boolean))];

const getColorAlternatives = (config = state.config) => config?.color_alternatives ?? [];

const hasColorAlternatives = (config = state.config) => getColorAlternatives(config).length > 1;

const cacheConfig = (config) => {
    if (config?.slug) {
        state.configCache[config.slug] = config;
    }
};

const getEntryConfig = (entry) => state.configCache[entry?.productSlug] || state.config;

const fetchCartConfig = async (slug) => {
    if (state.configCache[slug]) {
        return state.configCache[slug];
    }

    const response = await fetch(`/product/${encodeURIComponent(slug)}/cart-config`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (!response.ok) {
        throw new Error('Не вдалося завантажити колір');
    }

    const config = await response.json();
    cacheConfig(config);

    return config;
};

const pickSizeForConfig = (config, preferredSize) => {
    const sizes = getSizes(config);

    if (preferredSize && sizes.includes(preferredSize)) {
        return preferredSize;
    }

    return sizes[0] ?? null;
};

const resolveVariantForEntry = (entry, config = getEntryConfig(entry)) => {
    if (!config) {
        return null;
    }

    return (
        config.variants.find((variant) => variant.id === entry.variantId)
        || getVariantForSize(config, entry.size)
    );
};

const updateProductPreview = (config) => {
    const form = qs('[data-cart-modal-form]', modalEl);
    if (!form || !config) {
        return;
    }

    const displayName = config.model_name
        ? `${config.model_name} — ${config.color_name || config.name}`
        : config.name;

    qs('[data-cart-modal-product-name]', modalEl).textContent = displayName;
    qs('[data-cart-field="product_slug"]', form).value = config.slug;
    qs('[data-cart-field="image"]', form).src = config.image || '';
    qs('[data-cart-field="image"]', form).alt = displayName;
    qs('[data-cart-field="color"]', form).textContent = config.color_name || '';
    qs('[data-cart-field="price"]', form).textContent = config.price_formatted;
    const link = qs('[data-cart-field="url"]', form);
    if (link) {
        link.href = config.url;
    }
};

const renderColorSwatch = (color) => {
    if (color.hex) {
        return `<span class="cart-color-swatch block h-full w-full" style="background-color:${escapeHtml(color.hex)}"></span>`;
    }

    return `<span class="cart-color-swatch flex h-full w-full items-center justify-center bg-black-brand/5 text-[0.5rem] uppercase text-black-brand/45">${escapeHtml((color.name || '?').slice(0, 2))}</span>`;
};

const setColorButtonsActive = (container, activeSlug, { compact = false } = {}) => {
    if (!container) {
        return;
    }

    container.querySelectorAll('[data-color-slug]').forEach((btn) => {
        const active = btn.dataset.colorSlug === activeSlug;

        btn.setAttribute('aria-pressed', active ? 'true' : 'false');

        if (compact) {
            btn.classList.toggle('border-black-brand', active);
            btn.classList.toggle('ring-2', active);
            btn.classList.toggle('ring-black-brand', active);
            btn.classList.toggle('ring-offset-1', active);
            btn.classList.toggle('border-black-brand/15', !active);
            btn.classList.toggle('hover:border-black-brand/40', !active);
        } else {
            btn.classList.toggle('border-black-brand', active);
            btn.classList.toggle('bg-black-brand', active);
            btn.classList.toggle('text-white-brand', active);
            btn.classList.toggle('border-black-brand/15', !active);
            btn.classList.toggle('text-black-brand', !active);
            btn.classList.toggle('hover:border-black-brand/40', !active);

            const swatchWrap = btn.querySelector('.cart-color-swatch-wrap');
            if (swatchWrap) {
                swatchWrap.classList.toggle('border-white-brand/30', active);
            }
        }
    });
};

const renderColorButtonsHtml = (alternatives, activeSlug, { compact = false } = {}) =>
    alternatives
        .map((color) => {
            const active = color.slug === activeSlug;
            const swatch = renderColorSwatch(color);

            if (compact) {
                return `
                <button type="button"
                    class="cart-color-option cart-color-option--compact relative h-7 w-7 overflow-hidden border transition ${active ? 'border-black-brand ring-2 ring-black-brand ring-offset-1' : 'border-black-brand/15 hover:border-black-brand/40'}"
                    data-color-slug="${escapeHtml(color.slug)}"
                    aria-pressed="${active ? 'true' : 'false'}"
                    title="${escapeHtml(color.name)}">${swatch}</button>`;
            }

            return `
            <button type="button"
                class="cart-color-option flex items-center gap-2 border px-3 py-2 text-[0.62rem] uppercase tracking-[0.12em] transition ${active ? 'border-black-brand bg-black-brand text-white-brand' : 'border-black-brand/15 text-black-brand hover:border-black-brand/40'}"
                data-color-slug="${escapeHtml(color.slug)}"
                aria-pressed="${active ? 'true' : 'false'}">
                <span class="cart-color-swatch-wrap relative h-5 w-5 shrink-0 overflow-hidden border border-black-brand/10 ${active ? 'border-white-brand/30' : ''}">${swatch}</span>
                <span>${escapeHtml(color.name)}</span>
            </button>`;
        })
        .join('');

const bindColorButtons = (container, getActiveSlug, onSelect, { compact = false } = {}) => {
    const resolveActiveSlug = () =>
        typeof getActiveSlug === 'function' ? getActiveSlug() : getActiveSlug;

    container.querySelectorAll('[data-color-slug]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const slug = btn.dataset.colorSlug;
            const current = resolveActiveSlug();

            if (!slug || slug === current) {
                return;
            }

            setColorButtonsActive(container, slug, { compact });
            onSelect(slug);
        });
    });
};

const renderTemplateColorOptions = () => {
    const wrap = qs('[data-cart-colors-wrap]', modalEl);
    const container = qs('[data-cart-color-options]', modalEl);

    if (!wrap || !container || !state.config) {
        return;
    }

    const alternatives = getColorAlternatives();

    if (!hasColorAlternatives()) {
        wrap.classList.add('hidden');
        container.innerHTML = '';

        return;
    }

    wrap.classList.remove('hidden');
    container.innerHTML = renderColorButtonsHtml(alternatives, state.config.slug);
    bindColorButtons(container, () => state.config?.slug, selectTemplateColor);
};

const selectTemplateColor = async (slug) => {
    if (!state.config || slug === state.config.slug || state.colorLoading) {
        return;
    }

    state.colorLoading = true;
    showError('');

    try {
        const preferredSize = state.selectedSize;
        const config = await fetchCartConfig(slug);
        state.config = config;
        cacheConfig(config);

        updateProductPreview(config);
        renderTemplateColorOptions();

        const nextSize = pickSizeForConfig(config, preferredSize);
        renderSizeOptions(config, nextSize);
        renderCustomizationFields(config);

        if (state.mode === 'batch' && state.batchType === 'individual') {
            renderBatchEntries();
        }

        updateModalTotal();
    } catch (error) {
        showError(error.message || 'Помилка завантаження кольору');
    } finally {
        state.colorLoading = false;
    }
};

const selectEntryColor = async (index, slug) => {
    const entry = state.batchEntries[index];
    if (!entry || entry.productSlug === slug) {
        return;
    }

    try {
        const config = await fetchCartConfig(slug);
        entry.productSlug = slug;
        entry.size = pickSizeForConfig(config, entry.size);
        const variant = getVariantForSize(config, entry.size);
        entry.variantId = variant?.id ?? null;

        const entryEl = qs(`[data-cart-batch-entry][data-index="${index}"]`, modalEl);
        if (entryEl) {
            const sizesWrap = qs('[data-cart-batch-entry-sizes]', entryEl);
            if (sizesWrap) {
                sizesWrap.innerHTML = renderSizeButtonsHtml(getSizes(config), entry.size);
                bindEntrySizeButtons(entryEl, index);
            }

            const colorsWrap = qs('[data-cart-batch-entry-colors]', entryEl);
            if (colorsWrap) {
                setColorButtonsActive(colorsWrap, slug, { compact: true });
            }

            const details = qs('[data-cart-batch-entry-details]', entryEl);
            if (details) {
                const options = config.customizations ?? [];
                if (options.length) {
                    details.innerHTML = renderCustomizationFieldsHtml(options);
                    bindCustomizationFields(details);
                } else {
                    details.innerHTML = '';
                }
            }
        }

        updateBatchEntrySummaries();
        updateModalTotal();
    } catch (error) {
        const entryEl = qs(`[data-cart-batch-entry][data-index="${index}"]`, modalEl);
        const colorsWrap = qs('[data-cart-batch-entry-colors]', entryEl);
        if (colorsWrap) {
            setColorButtonsActive(colorsWrap, entry.productSlug || state.config.slug, { compact: true });
        }
        showError(error.message || 'Помилка завантаження кольору');
    }
};

const bindEntrySizeButtons = (entryEl, index) => {
    const entry = state.batchEntries[index];
    const config = getEntryConfig(entry);

    entryEl.querySelectorAll('[data-cart-batch-entry-sizes] .cart-size-option').forEach((btn) => {
        btn.addEventListener('click', () => {
            entryEl.querySelectorAll('[data-cart-batch-entry-sizes] .cart-size-option').forEach((b) => {
                b.classList.remove('border-black-brand', 'bg-black-brand', 'text-white-brand');
                b.classList.add('border-black-brand/15', 'bg-white-brand', 'text-black-brand');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('border-black-brand', 'bg-black-brand', 'text-white-brand');
            btn.classList.remove('border-black-brand/15', 'bg-white-brand', 'text-black-brand');
            btn.setAttribute('aria-pressed', 'true');

            entry.size = btn.dataset.size;
            const match = getVariantForSize(config, entry.size);
            entry.variantId = match?.id ?? null;
            updateBatchEntrySummaries();
            updateModalTotal();
        });
    });
};

const calcUnitPriceFromValues = (config, variant, customizationValues) => {
    let total = variant?.price ?? config?.price ?? 0;

    config?.customizations?.forEach((option) => {
        const value = customizationValues[option.slug];

        if (option.type === 'checkbox') {
            if (value === '1' || value === true) {
                total += Number(option.price_delta) || 0;
            }
        } else if (value) {
            total += Number(option.price_delta) || 0;
        }
    });

    return total;
};

const calcUnitPrice = (config, variant, form) => {
    const values = {};

    form.querySelectorAll('[data-customization-slug]').forEach((field) => {
        const slug = field.dataset.customizationSlug;
        if (field.type === 'checkbox') {
            values[slug] = field.checked ? '1' : '';
        } else {
            values[slug] = field.value;
        }
    });

    return calcUnitPriceFromValues(config, variant, values);
};

const readTemplateCustomizationValues = (form) => {
    const values = {};

    form.querySelectorAll('[data-cart-customizations] [data-customization-slug]').forEach((field) => {
        const slug = field.dataset.customizationSlug;
        if (field.type === 'checkbox') {
            values[slug] = field.checked ? '1' : '';
        } else {
            values[slug] = field.value?.trim?.() ?? field.value;
        }
    });

    return values;
};

const readEntryCustomizationValues = (entryEl) => {
    const values = {};

    entryEl.querySelectorAll('[data-customization-slug]').forEach((field) => {
        const slug = field.dataset.customizationSlug;
        if (field.type === 'checkbox') {
            values[slug] = field.checked ? '1' : '';
        } else {
            values[slug] = field.value?.trim?.() ?? field.value;
        }
    });

    return values;
};

const collectCustomizationsFromValues = (config, values) => {
    const items = [];

    config?.customizations?.forEach((option) => {
        const raw = values[option.slug];
        let value = null;

        if (option.type === 'checkbox') {
            value = raw === '1' || raw === true ? '1' : '';
        } else {
            value = raw ?? '';
        }

        if (value !== '' && value !== null && value !== false) {
            items.push({ slug: option.slug, value });
        }
    });

    return items;
};

const collectCustomizations = (form) => collectCustomizationsFromValues(state.config, readTemplateCustomizationValues(form));

const collectCustomizationsFromEntry = (entryEl) =>
    collectCustomizationsFromValues(state.config, readEntryCustomizationValues(entryEl));

const getTemplateVariant = () => {
    if (!state.config) {
        return null;
    }

    return (
        state.config.variants.find((v) => v.id === state.selectedVariantId)
        || getVariantForSize(state.config, state.selectedSize)
    );
};

const updateModeUi = () => {
    const isBatch = state.mode === 'batch';
    const isIndividual = isBatch && state.batchType === 'individual';

    qs('[data-cart-batch-panel]', modalEl)?.classList.toggle('hidden', !isBatch);
    qs('[data-cart-single-qty]', modalEl)?.classList.toggle('hidden', isBatch);
    qs('[data-cart-batch-entries-wrap]', modalEl)?.classList.toggle('hidden', !isIndividual);

    const templateLabel = qs('[data-cart-template-label]', modalEl);
    if (templateLabel) {
        templateLabel.textContent = isIndividual ? 'Шаблон набору' : 'Розмір та опції';
    }

    const sizeLabel = qs('[data-cart-size-label]', modalEl);
    if (sizeLabel) {
        sizeLabel.textContent = isIndividual ? 'Розмір (шаблон)' : 'Розмір';
    }

    const customLabel = qs('[data-cart-customizations-label]', modalEl);
    if (customLabel) {
        customLabel.textContent = isIndividual ? 'Опції шаблону' : 'Індивідуальні опції';
    }

    const notesLabel = qs('[data-cart-notes-label]', modalEl);
    if (notesLabel) {
        notesLabel.textContent = isIndividual ? 'Побажання шаблону' : 'Додаткові побажання';
    }

    qsa('[data-cart-mode]', modalEl).forEach((btn) => {
        const active = btn.dataset.cartMode === state.mode;
        btn.setAttribute('aria-selected', active ? 'true' : 'false');
        btn.classList.toggle('border-black-brand', active);
        btn.classList.toggle('bg-black-brand', active);
        btn.classList.toggle('text-white-brand', active);
        btn.classList.toggle('border-transparent', !active);
        btn.classList.toggle('text-black-brand/70', !active);
    });

    qsa('[data-cart-batch-type]', modalEl).forEach((btn) => {
        const active = btn.dataset.cartBatchType === state.batchType;
        btn.classList.toggle('border-black-brand', active);
        btn.classList.toggle('bg-black-brand', active);
        btn.classList.toggle('text-white-brand', active);
        btn.classList.toggle('border-black-brand/15', !active);
        btn.classList.toggle('text-black-brand', !active);
    });

    const submit = qs('[data-cart-modal-submit]', modalEl);
    if (submit) {
        if (!isBatch) {
            submit.textContent = 'Додати в кошик';
        } else if (state.batchType === 'uniform') {
            submit.textContent = `Додати набір ×${state.batchCount}`;
        } else {
            submit.textContent = `Додати ${state.batchCount} позицій`;
        }
    }

    updateModalTotal();
};

const syncBatchEntries = () => {
    const count = Math.max(MIN_BATCH, Math.min(MAX_BATCH, state.batchCount));
    state.batchCount = count;

    const input = qs('[data-cart-batch-count]', modalEl);
    if (input) {
        input.value = String(count);
    }

    const prev = state.batchEntries;
    state.batchEntries = Array.from({ length: count }, (_, index) => {
        const existing = prev[index];
        if (existing) {
            return {
                ...existing,
                productSlug: existing.productSlug || state.config?.slug || null,
            };
        }

        return {
            productSlug: state.config?.slug ?? null,
            size: state.selectedSize,
            variantId: state.selectedVariantId,
            expanded: false,
            customizationValues: readTemplateCustomizationValues(qs('[data-cart-modal-form]', modalEl)),
        };
    });
};

const renderCustomizationFieldsHtml = (options, prefix = '') => {
    if (!options?.length) {
        return '';
    }

    return options
        .map((option) => {
            const slug = `${prefix}${option.slug}`;
            const requiredMark = option.is_required
                ? ' <span class="text-black-brand">*</span>'
                : '';
            const delta = option.price_delta_formatted
                ? `<span class="ml-2 text-[0.62rem] tracking-[0.12em] text-black-brand/40">${escapeHtml(option.price_delta_formatted)}</span>`
                : '';
            const description = option.description
                ? `<p class="mt-1 text-[0.72rem] leading-relaxed text-black-brand/50">${escapeHtml(option.description)}</p>`
                : '';

            if (option.type === 'checkbox') {
                return `
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" value="1"
                        data-customization-slug="${escapeHtml(option.slug)}"
                        class="mt-1 h-4 w-4 border-black-brand/25 accent-black-brand">
                    <span>
                        <span class="text-[0.78rem] uppercase tracking-[0.12em] text-black-brand/80">${escapeHtml(option.name)}${requiredMark}</span>${delta}
                        ${description}
                    </span>
                </label>`;
            }

            if (option.type === 'select') {
                const choices = Object.entries(option.options || {})
                    .map(
                        ([value, label]) =>
                            `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`,
                    )
                    .join('');

                return `
                <div>
                    <label class="mb-2 block text-[0.78rem] uppercase tracking-[0.12em] text-black-brand/80">
                        ${escapeHtml(option.name)}${requiredMark}${delta}
                    </label>
                    ${description}
                    <select data-customization-slug="${escapeHtml(option.slug)}"
                        class="w-full border border-black-brand/15 bg-white-brand px-3 py-2.5 text-[0.82rem] tracking-[0.06em] text-black-brand focus:border-black-brand focus:outline-none">
                        <option value="">— Оберіть —</option>
                        ${choices}
                    </select>
                </div>`;
            }

            return `
            <div>
                <label class="mb-2 block text-[0.78rem] uppercase tracking-[0.12em] text-black-brand/80">
                    ${escapeHtml(option.name)}${requiredMark}${delta}
                </label>
                ${description}
                <input type="text" data-customization-slug="${escapeHtml(option.slug)}"
                    class="w-full border border-black-brand/15 bg-white-brand px-3 py-2.5 text-[0.82rem] tracking-[0.06em] text-black-brand focus:border-black-brand focus:outline-none">
            </div>`;
        })
        .join('');
};

const bindCustomizationFields = (root) => {
    root.querySelectorAll('input, select').forEach((field) => {
        field.addEventListener('change', () => {
            updateModalTotal();
            if (state.mode === 'batch' && state.batchType === 'individual') {
                updateBatchEntrySummaries();
            }
        });
        field.addEventListener('input', updateModalTotal);
    });
};

const renderSizeButtonsHtml = (sizes, activeSize, dataAttr = 'data-size') => sizes
    .map((size) => {
        const active = size === activeSize;

        return `
        <button type="button"
            class="cart-size-option min-w-[2.5rem] border px-2.5 py-2 text-[0.62rem] uppercase tracking-[0.12em] transition ${active ? 'border-black-brand bg-black-brand text-white-brand' : 'border-black-brand/15 bg-white-brand text-black-brand hover:border-black-brand/40'}"
            ${dataAttr}="${escapeHtml(size)}"
            aria-pressed="${active ? 'true' : 'false'}">${escapeHtml(size)}</button>`;
    })
    .join('');

const updateBatchEntrySummaries = () => {
    const container = qs('[data-cart-batch-entries]', modalEl);
    if (!container) {
        return;
    }

    container.querySelectorAll('[data-cart-batch-entry]').forEach((entryEl, index) => {
        const summary = qs('[data-cart-batch-entry-summary-text]', entryEl);
        const entry = state.batchEntries[index];
        if (!summary || !entry) {
            return;
        }

        const config = getEntryConfig(entry);
        const variant = resolveVariantForEntry(entry, config);
        const colorText = config?.color_name ? `${config.color_name} · ` : '';
        const sizeText = entry.size || '—';
        const customCount = collectCustomizationsFromEntry(entryEl).length;
        const extra = customCount > 0 ? ` · ${customCount} опц.` : '';
        const values = entryEl
            ? readEntryCustomizationValues(entryEl)
            : entry.customizationValues || {};

        summary.textContent = `${colorText}${sizeText}${extra} · ${formatUsd(calcUnitPriceFromValues(config, variant, values))}`;
    });
};

const renderBatchEntries = () => {
    const container = qs('[data-cart-batch-entries]', modalEl);
    if (!container || !state.config) {
        return;
    }

    const colors = getColorAlternatives();
    const showColors = hasColorAlternatives();

    container.innerHTML = state.batchEntries
        .map((entry, index) => {
            const entryConfig = getEntryConfig(entry);
            const sizes = getSizes(entryConfig);
            const options = entryConfig?.customizations ?? [];
            const detailsHtml = options.length
                ? `<div class="mt-3 space-y-4 border-t border-black-brand/10 pt-3 ${entry.expanded ? '' : 'hidden'}" data-cart-batch-entry-details>
                    ${renderCustomizationFieldsHtml(options)}
                   </div>`
                : '';
            const colorsHtml = showColors
                ? `<div class="mb-2 flex flex-wrap gap-1" data-cart-batch-entry-colors>
                    ${renderColorButtonsHtml(colors, entry.productSlug || state.config.slug, { compact: true })}
                   </div>`
                : '';

            return `
            <article class="cart-batch-entry border border-black-brand/10 bg-white-brand" data-cart-batch-entry data-index="${index}">
                <div class="px-3 py-2.5">
                    <div class="flex items-start gap-2">
                        <span class="w-7 shrink-0 pt-1 text-[0.62rem] uppercase tracking-[0.14em] text-black-brand/45">${index + 1}</span>
                        <div class="min-w-0 flex-1">
                            ${colorsHtml}
                            <div class="flex flex-wrap items-center gap-1" data-cart-batch-entry-sizes>
                                ${renderSizeButtonsHtml(sizes, entry.size)}
                            </div>
                        </div>
                        ${options.length ? `<button type="button" class="shrink-0 px-2 py-1 text-[0.58rem] uppercase tracking-[0.1em] text-black-brand/50 hover:text-black-brand" data-cart-batch-toggle-details aria-expanded="${entry.expanded ? 'true' : 'false'}">${entry.expanded ? 'Згорнути' : 'Опції'}</button>` : ''}
                    </div>
                </div>
                <p class="px-3 pb-2 text-[0.65rem] tracking-[0.06em] text-black-brand/45" data-cart-batch-entry-summary-text></p>
                ${detailsHtml}
            </article>`;
        })
        .join('');

    container.querySelectorAll('[data-cart-batch-entry]').forEach((entryEl) => {
        const index = Number(entryEl.dataset.index);
        const entry = state.batchEntries[index];

        const colorsWrap = qs('[data-cart-batch-entry-colors]', entryEl);
        if (colorsWrap) {
            bindColorButtons(
                colorsWrap,
                () => state.batchEntries[index]?.productSlug || state.config.slug,
                (slug) => selectEntryColor(index, slug),
                { compact: true },
            );
        }

        bindEntrySizeButtons(entryEl, index);

        const details = qs('[data-cart-batch-entry-details]', entryEl);
        if (details) {
            const values = entry.customizationValues || {};
            details.querySelectorAll('[data-customization-slug]').forEach((field) => {
                const slug = field.dataset.customizationSlug;
                if (field.type === 'checkbox') {
                    field.checked = values[slug] === '1';
                } else {
                    field.value = values[slug] ?? '';
                }
            });
            bindCustomizationFields(details);
        }

        qs('[data-cart-batch-toggle-details]', entryEl)?.addEventListener('click', () => {
            entry.expanded = !entry.expanded;
            details?.classList.toggle('hidden', !entry.expanded);
            const toggle = qs('[data-cart-batch-toggle-details]', entryEl);
            if (toggle) {
                toggle.textContent = entry.expanded ? 'Згорнути' : 'Опції';
                toggle.setAttribute('aria-expanded', entry.expanded ? 'true' : 'false');
            }
        });
    });

    updateBatchEntrySummaries();
};

const applyTemplateToAllEntries = () => {
    const form = qs('[data-cart-modal-form]', modalEl);
    if (!form) {
        return;
    }

    const templateValues = readTemplateCustomizationValues(form);
    const notes = qs('[data-cart-field="notes"]', form)?.value || '';

    state.batchEntries = state.batchEntries.map((entry) => ({
        ...entry,
        productSlug: state.config.slug,
        size: state.selectedSize,
        variantId: state.selectedVariantId,
        customizationValues: { ...templateValues },
    }));

    renderBatchEntries();

    const container = qs('[data-cart-batch-entries]', modalEl);
    container?.querySelectorAll('[data-cart-batch-entry]').forEach((entryEl) => {
        const details = qs('[data-cart-batch-entry-details]', entryEl);
        if (!details) {
            return;
        }
        details.querySelectorAll('[data-customization-slug]').forEach((field) => {
            const slug = field.dataset.customizationSlug;
            if (field.type === 'checkbox') {
                field.checked = templateValues[slug] === '1';
            } else {
                field.value = templateValues[slug] ?? '';
            }
        });
    });

    if (notes && state.batchType === 'individual') {
        // notes stay on template; per-entry notes collected on submit from template unless overridden
    }

    updateModalTotal();
};

const updateModalTotal = () => {
    const form = qs('[data-cart-modal-form]', modalEl);
    const totalEl = qs('[data-cart-modal-total]', modalEl);
    if (!form || !totalEl || !state.config) {
        return;
    }

    let total = 0;

    if (state.mode === 'single') {
        const variant = getTemplateVariant();
        const qty = Math.max(1, Number(qs('[data-cart-field="quantity"]', form)?.value) || 1);
        total = calcUnitPrice(state.config, variant, form) * qty;
    } else if (state.batchType === 'uniform') {
        const variant = getTemplateVariant();
        total = calcUnitPrice(state.config, variant, form) * state.batchCount;
    } else {
        const container = qs('[data-cart-batch-entries]', modalEl);
        state.batchEntries.forEach((entry, index) => {
            const entryEl = container?.querySelector(`[data-cart-batch-entry][data-index="${index}"]`);
            const config = getEntryConfig(entry);
            const variant = resolveVariantForEntry(entry, config);
            const values = entryEl
                ? readEntryCustomizationValues(entryEl)
                : entry.customizationValues || readTemplateCustomizationValues(form);
            total += calcUnitPriceFromValues(config, variant, values);
        });
    }

    totalEl.textContent = formatUsd(total);
};

const renderCustomizationFields = (config) => {
    const wrap = qs('[data-cart-customizations-wrap]', modalEl);
    const container = qs('[data-cart-customizations]', modalEl);

    if (!wrap || !container) {
        return;
    }

    const options = config?.customizations ?? [];

    if (!options.length) {
        wrap.classList.add('hidden');
        container.innerHTML = '';

        return;
    }

    wrap.classList.remove('hidden');
    container.innerHTML = renderCustomizationFieldsHtml(options);
    bindCustomizationFields(container);
};

const renderSizeOptions = (config, preselectedSize = null) => {
    const container = qs('[data-cart-size-options]', modalEl);
    if (!container) {
        return;
    }

    const sizes = getSizes(config);

    container.innerHTML = renderSizeButtonsHtml(sizes, preselectedSize || sizes[0]);

    const initial = preselectedSize || sizes[0];
    state.selectedSize = initial;
    const variant = getVariantForSize(config, initial);
    state.selectedVariantId = variant?.id ?? null;

    container.querySelectorAll('.cart-size-option').forEach((btn) => {
        btn.addEventListener('click', () => {
            container.querySelectorAll('.cart-size-option').forEach((b) => {
                b.classList.remove('border-black-brand', 'bg-black-brand', 'text-white-brand');
                b.classList.add('border-black-brand/15', 'bg-white-brand', 'text-black-brand');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('border-black-brand', 'bg-black-brand', 'text-white-brand');
            btn.classList.remove('border-black-brand/15', 'bg-white-brand', 'text-black-brand');
            btn.setAttribute('aria-pressed', 'true');

            state.selectedSize = btn.dataset.size;
            const match = getVariantForSize(state.config, state.selectedSize);
            state.selectedVariantId = match?.id ?? null;
            updateModalTotal();
        });
    });
};

const fillModal = (config, preselectedSize = null) => {
    const form = qs('[data-cart-modal-form]', modalEl);
    if (!form) {
        return;
    }

    state.mode = 'single';
    state.batchCount = 3;
    state.batchType = 'uniform';
    state.batchEntries = [];
    state.configCache = {};
    cacheConfig(config);

    updateProductPreview(config);
    qs('[data-cart-field="quantity"]', form).value = '1';
    qs('[data-cart-field="notes"]', form).value = '';

    const batchInput = qs('[data-cart-batch-count]', modalEl);
    if (batchInput) {
        batchInput.value = '3';
    }

    renderTemplateColorOptions();
    renderSizeOptions(config, preselectedSize);
    renderCustomizationFields(config);
    syncBatchEntries();
    updateModeUi();

    const submit = qs('[data-cart-modal-submit]', modalEl);
    if (submit) {
        submit.disabled = false;
        submit.setAttribute('form', form.id || 'cart-modal-form-el');
    }

    if (!form.id) {
        form.id = 'cart-modal-form-el';
    }
};

const showError = (message) => {
    const el = qs('[data-cart-modal-error]', modalEl);
    if (!el) {
        return;
    }

    if (message) {
        el.textContent = message;
        el.classList.remove('hidden');
    } else {
        el.textContent = '';
        el.classList.add('hidden');
    }
};

const setMode = (mode) => {
    state.mode = mode;
    if (mode === 'batch') {
        syncBatchEntries();
        if (state.batchType === 'individual') {
            renderBatchEntries();
        }
    }
    updateModeUi();
};

const setBatchType = (type) => {
    state.batchType = type;
    syncBatchEntries();
    if (type === 'individual') {
        applyTemplateToAllEntries();
        renderBatchEntries();
    }
    updateModeUi();
};

const setBatchCount = (count) => {
    state.batchCount = Math.max(MIN_BATCH, Math.min(MAX_BATCH, count));
    syncBatchEntries();
    if (state.mode === 'batch' && state.batchType === 'individual') {
        renderBatchEntries();
    }
    updateModeUi();
};

const buildBatchLinesPayload = (form) => {
    const notes = qs('[data-cart-field="notes"]', form)?.value?.trim() || '';
    const container = qs('[data-cart-batch-entries]', modalEl);
    const lines = [];

    state.batchEntries.forEach((entry, index) => {
        const entryEl = container?.querySelector(`[data-cart-batch-entry][data-index="${index}"]`);

        if (!entry.variantId) {
            throw new Error(`Оберіть розмір для позиції №${index + 1}.`);
        }

        lines.push({
            variant_id: entry.variantId,
            notes: notes || null,
            customizations: entryEl
                ? collectCustomizationsFromEntry(entryEl)
                : collectCustomizationsFromValues(getEntryConfig(entry), entry.customizationValues || {}),
        });
    });

    return lines;
};

const openModal = async (productSlug, preselectedSize = null) => {
    if (!modalEl) {
        return;
    }

    state = {
        config: null,
        configCache: {},
        mode: 'single',
        selectedSize: null,
        selectedVariantId: null,
        batchCount: 3,
        batchType: 'uniform',
        batchEntries: [],
        colorLoading: false,
    };
    showError('');

    const loading = qs('[data-cart-modal-loading]', modalEl);
    const form = qs('[data-cart-modal-form]', modalEl);

    loading?.classList.remove('hidden');
    form?.classList.add('hidden');
    modalEl.hidden = false;
    modalEl.setAttribute('aria-hidden', 'false');
    modalEl.classList.add('is-open');
    modalEl.classList.remove('pointer-events-none');
    document.body.classList.add('overflow-hidden');

    try {
        const response = await fetch(`/product/${encodeURIComponent(productSlug)}/cart-config`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            throw new Error('Не вдалося завантажити товар');
        }

        state.config = await response.json();
        cacheConfig(state.config);
        fillModal(state.config, preselectedSize);
        loading?.classList.add('hidden');
        form?.classList.remove('hidden');
    } catch (error) {
        showError(error.message || 'Помилка завантаження');
        loading?.classList.add('hidden');
    }
};

const closeModal = () => {
    if (!modalEl) {
        return;
    }

    modalEl.classList.remove('is-open');
    modalEl.classList.add('pointer-events-none');
    modalEl.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');

    window.setTimeout(() => {
        if (!modalEl.classList.contains('is-open')) {
            modalEl.hidden = true;
        }
    }, 300);
};

const submitModal = async (event) => {
    event.preventDefault();

    const form = qs('[data-cart-modal-form]', modalEl);
    if (!form || !state.config) {
        return;
    }

    if (state.colorLoading) {
        return;
    }

    showError('');
    const submitBtn = qs('[data-cart-modal-submit]', modalEl);
    submitBtn?.setAttribute('disabled', 'true');

    let payload;

    try {
        if (state.mode === 'batch' && state.batchType === 'individual') {
            payload = {
                product_slug: state.config.slug,
                lines: buildBatchLinesPayload(form),
            };
        } else {
            if (!state.selectedVariantId) {
                throw new Error('Оберіть розмір.');
            }

            payload = {
                product_slug: state.config.slug,
                variant_id: state.selectedVariantId,
                quantity:
                    state.mode === 'batch'
                        ? state.batchCount
                        : Number(qs('[data-cart-field="quantity"]', form)?.value) || 1,
                notes: qs('[data-cart-field="notes"]', form)?.value || '',
                customizations: collectCustomizations(form),
            };
        }
    } catch (error) {
        showError(error.message || 'Помилка');
        submitBtn?.removeAttribute('disabled');

        return;
    }

    try {
        const response = await fetch('/cart', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const errors = data?.errors ? Object.values(data.errors).flat() : [];
            const message = data?.message || errors[0] || 'Не вдалося додати в кошик';

            throw new Error(message);
        }

        updateNavCount(data.count ?? 0);
        closeModal();

        document.dispatchEvent(
            new CustomEvent('cart:added', {
                detail: { count: data.count, message: data.message },
            }),
        );
    } catch (error) {
        showError(error.message || 'Помилка');
    } finally {
        submitBtn?.removeAttribute('disabled');
    }
};

const bindModal = () => {
    modalEl = document.querySelector('[data-cart-modal]');
    if (!modalEl) {
        return;
    }

    modalEl.querySelectorAll('[data-cart-modal-close]').forEach((btn) => {
        btn.addEventListener('click', closeModal);
    });

    qs('[data-cart-modal-form]', modalEl)?.addEventListener('submit', submitModal);

    qs('[data-cart-qty-dec]', modalEl)?.addEventListener('click', () => {
        const input = qs('[data-cart-field="quantity"]', modalEl);
        if (input) {
            input.value = String(Math.max(1, Number(input.value) - 1));
            updateModalTotal();
        }
    });

    qs('[data-cart-qty-inc]', modalEl)?.addEventListener('click', () => {
        const input = qs('[data-cart-field="quantity"]', modalEl);
        if (input) {
            input.value = String(Math.min(99, Number(input.value) + 1));
            updateModalTotal();
        }
    });

    qs('[data-cart-field="quantity"]', modalEl)?.addEventListener('input', updateModalTotal);

    qs('[data-cart-batch-dec]', modalEl)?.addEventListener('click', () => {
        setBatchCount(state.batchCount - 1);
    });

    qs('[data-cart-batch-inc]', modalEl)?.addEventListener('click', () => {
        setBatchCount(state.batchCount + 1);
    });

    qs('[data-cart-batch-count]', modalEl)?.addEventListener('input', (event) => {
        setBatchCount(Number(event.target.value) || MIN_BATCH);
    });

    qsa('[data-cart-mode]', modalEl).forEach((btn) => {
        btn.addEventListener('click', () => setMode(btn.dataset.cartMode));
    });

    qsa('[data-cart-batch-type]', modalEl).forEach((btn) => {
        btn.addEventListener('click', () => setBatchType(btn.dataset.cartBatchType));
    });

    qs('[data-cart-batch-apply-template]', modalEl)?.addEventListener('click', applyTemplateToAllEntries);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modalEl?.classList.contains('is-open')) {
            closeModal();
        }
    });
};

const bindTriggers = () => {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-cart-open]');
        if (!trigger) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const slug = trigger.dataset.productSlug || trigger.dataset.cartProductSlug;
        const size = trigger.dataset.preselectedSize || null;

        if (slug) {
            openModal(slug, size);
        }
    });
};

const syncMobileCartBarFromDom = () => {
    const countEl = document.querySelector('[data-floating-cart-count]');

    if (!countEl) {
        return;
    }

    updateNavCount(Number(countEl.textContent) || 0);
};

export const initCart = () => {
    bindModal();
    bindTriggers();
    syncMobileCartBarFromDom();
    fetchSummary();

    window.addEventListener('resize', () => {
        const countEl = document.querySelector('[data-floating-cart-count]');

        if (countEl) {
            updateNavCount(Number(countEl.textContent) || 0);
        }
    });

    document.addEventListener('cart:added', (event) => {
        const message = event.detail?.message;
        if (!message) {
            return;
        }

        const toast = document.querySelector('[data-cart-toast]');
        if (toast) {
            toast.textContent = message;
            toast.classList.add('is-visible');
            window.setTimeout(() => toast.classList.remove('is-visible'), 3200);
        }
    });
};

export { openModal as openCartModal, updateNavCount };
