import axios from 'axios';

const root = document.getElementById('homepage-admin');
if (!root) {
    throw new Error('homepage-admin root not found');
}

const apiBase = root.dataset.apiBase;
const csrfToken = root.dataset.csrf;
const cdnBase = root.dataset.cdnBase || '';

const http = axios.create({
    baseURL: apiBase,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
});

http.interceptors.request.use((config) => {
    config.headers['X-CSRF-TOKEN'] = csrfToken;
    return config;
});

const statusEl = document.getElementById('homepage-admin-status');
const tabsEl = document.getElementById('homepage-tabs');
const panelsEl = document.getElementById('homepage-panels');

let state = null;
let activeTab = 'globals';

const tabs = [
    { id: 'globals', label: 'Загальне' },
    { id: 'hero', label: 'Hero', type: 'block' },
    { id: 'about_us', label: 'Про бренд', type: 'block' },
    { id: 'product_box', label: 'Продукт', type: 'block' },
    { id: 'benefits', label: 'Переваги', type: 'block' },
    { id: 'benefits_list', label: 'Список переваг', type: 'benefits' },
    { id: 'audience', label: 'Для кого', type: 'audience' },
    { id: 'reviews', label: 'Відгуки', type: 'reviews' },
    { id: 'ribbon', label: 'Галерея Ribbon', type: 'ribbon' },
    { id: 'statement', label: 'Statement', type: 'block' },
    { id: 'footer', label: 'Футер', type: 'block' },
    { id: 'navigator', label: 'Навігація', type: 'block' },
];

function setStatus(message, type = 'info') {
    statusEl.textContent = message;
    statusEl.className = `text-sm font-medium ${
        type === 'error' ? 'text-danger-600' : type === 'success' ? 'text-success-600' : 'text-gray-600'
    }`;
}

function blockContent(slug) {
    return state?.blocks?.find((b) => b.slug === slug)?.content ?? {};
}

function field(id, label, value = '', type = 'text', options = {}) {
    const rows = type === 'textarea' ? 3 : 1;
    const input =
        type === 'textarea'
            ? `<textarea data-field="${id}" rows="${rows}" class="fi-input block w-full rounded-lg border-gray-300 text-sm">${escapeHtml(value)}</textarea>`
            : type === 'checkbox'
              ? `<input type="checkbox" data-field="${id}" class="rounded border-gray-300" ${value ? 'checked' : ''}>`
              : type === 'select'
                ? `<select data-field="${id}" class="fi-input block w-full rounded-lg border-gray-300 text-sm">${options.choices
                      .map(
                          (c) =>
                              `<option value="${c.value}" ${String(c.value) === String(value) ? 'selected' : ''}>${escapeHtml(c.label)}</option>`,
                      )
                      .join('')}</select>`
                : `<input type="${type}" data-field="${id}" value="${escapeHtml(value)}" class="fi-input block w-full rounded-lg border-gray-300 text-sm">`;

    return `<label class="block space-y-1">
        <span class="text-sm font-medium text-gray-700">${escapeHtml(label)}</span>
        ${input}
    </label>`;
}

function imageField(id, label, path, directory = 'homepage') {
    const url = path ? mediaUrl(path) : '';
    return `<div class="space-y-2 rounded-lg border border-gray-200 p-4" data-image-field="${id}" data-directory="${directory}">
        <span class="text-sm font-medium text-gray-700">${escapeHtml(label)}</span>
        <input type="hidden" data-field="${id}" value="${escapeHtml(path || '')}">
        ${url ? `<img src="${url}" alt="" class="max-h-40 rounded object-cover">` : ''}
        <input type="file" accept="image/*" data-upload-for="${id}" class="text-sm">
        <p class="text-xs text-gray-500">${escapeHtml(path || 'Файл не обрано')}</p>
    </div>`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function mediaUrl(path) {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    const normalized = path.replace(/^\/+/, '');
    if (cdnBase) {
        return `${cdnBase}/${normalized}`;
    }
    return `/storage/${normalized}`;
}

function collectFields(container) {
    const data = {};
    container.querySelectorAll('[data-field]').forEach((el) => {
        const key = el.dataset.field;
        if (el.type === 'checkbox') {
            data[key] = el.checked;
        } else {
            data[key] = el.value;
        }
    });
    return data;
}

async function uploadImage(file, directory) {
    const form = new FormData();
    form.append('file', file);
    form.append('directory', directory);
    const { data } = await http.post('/upload', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data;
}

async function saveBlock(slug, container) {
    const content = collectFields(container);
    await http.put(`/blocks/${slug}`, { content });
    setStatus('Блок збережено', 'success');
    await load();
}

async function saveGlobals(container) {
    const fields = collectFields(container);
    await http.put('/globals', {
        spotlight_product_id: fields.spotlight_product_id ? Number(fields.spotlight_product_id) : null,
        featured_product_ids: fields.featured_product_ids
            ? fields.featured_product_ids.split(',').map((v) => Number(v.trim())).filter(Boolean)
            : [],
        use_catalog_audience: fields.use_catalog_audience,
    });
    setStatus('Збережено', 'success');
    await load();
}

function productOptions(selectedId = null) {
    const products = state?.products ?? [];
    return [
        { value: '', label: '— Автовибір —' },
        ...products.map((p) => ({
            value: p.id,
            label: p.name,
            selected: p.id === selectedId,
        })),
    ];
}

function renderGlobals() {
    const g = state.globals;
    const productChoices = productOptions(g.spotlight_product_id);
    const featuredIds = (g.featured_product_ids ?? []).join(', ');

    return `<div class="space-y-4" data-panel="globals">
        ${field('spotlight_product_id', 'Spotlight товар', g.spotlight_product_id ?? '', 'select', { choices: productChoices })}
        ${field('featured_product_ids', 'ID товарів для слайдера (через кому)', featuredIds)}
        ${field('use_catalog_audience', 'Картки «Для кого» з каталогів', g.use_catalog_audience, 'checkbox')}
        <button type="button" data-save-globals class="fi-btn fi-btn-size-md fi-color-primary inline-flex items-center justify-center gap-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Зберегти</button>
    </div>`;
}

function renderHero() {
    const c = blockContent('hero');
    return `<div class="grid gap-4 md:grid-cols-2" data-panel="hero" data-block-slug="hero">
        ${field('tagline', 'Підзаголовок', c.tagline)}
        ${imageField('hero_image', 'Фонове зображення', c.hero_image, 'homepage/hero')}
        ${field('side_link_label', 'Бокове посилання (текст)', c.side_link_label, 'textarea')}
        ${field('side_link_href', 'Бокове посилання (URL)', c.side_link_href)}
        ${field('footer_tagline', 'Нижній слоган', c.footer_tagline)}
        ${field('scroll_hint', 'Підказка прокрутки', c.scroll_hint)}
        ${field('instagram_url', 'Instagram', c.instagram_url)}
        ${field('facebook_url', 'Facebook', c.facebook_url)}
        ${field('tiktok_url', 'TikTok', c.tiktok_url)}
        <div class="md:col-span-2"><button type="button" data-save-block class="fi-btn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Зберегти Hero</button></div>
    </div>`;
}

function renderAboutUs() {
    const c = blockContent('about_us');
    return `<div class="grid gap-4 md:grid-cols-2" data-panel="about_us" data-block-slug="about_us">
        ${field('badge', 'Бейдж', c.badge)}
        ${field('title_line1', 'Заголовок рядок 1', c.title_line1)}
        ${field('title_line2', 'Заголовок рядок 2', c.title_line2)}
        ${field('paragraph_1', 'Абзац 1 (fallback)', c.paragraph_1, 'textarea')}
        ${field('paragraph_2', 'Абзац 2 (fallback)', c.paragraph_2, 'textarea')}
        ${field('cta_primary', 'Кнопка 1', c.cta_primary)}
        ${field('cta_secondary', 'Кнопка 2', c.cta_secondary)}
        ${field('footer_note', 'Примітка внизу', c.footer_note, 'textarea')}
        <div class="md:col-span-2"><button type="button" data-save-block class="fi-btn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Зберегти</button></div>
    </div>`;
}

function renderProductBox() {
    const c = blockContent('product_box');
    return `<div class="grid gap-4 md:grid-cols-2" data-panel="product_box" data-block-slug="product_box">
        ${field('title', 'Заголовок секції', c.title)}
        ${field('catalog_label', 'Посилання каталог', c.catalog_label)}
        ${field('made_with', 'Made with', c.made_with)}
        <div class="md:col-span-2"><button type="button" data-save-block class="fi-btn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Зберегти</button></div>
    </div>`;
}

function renderBenefitsHeader() {
    const c = blockContent('benefits');
    return `<div class="grid gap-4 md:grid-cols-2" data-panel="benefits" data-block-slug="benefits">
        ${field('title_line1', 'Заголовок рядок 1', c.title_line1)}
        ${field('title_line2', 'Заголовок рядок 2', c.title_line2)}
        ${field('badge', 'Бейдж', c.badge)}
        ${field('description_fallback', 'Опис (fallback)', c.description_fallback, 'textarea')}
        ${field('made_with', 'Made with', c.made_with)}
        ${imageField('fallback_image', 'Зображення (без spotlight)', c.fallback_image, 'homepage/benefits')}
        <div class="md:col-span-2"><button type="button" data-save-block class="fi-btn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Зберегти</button></div>
    </div>`;
}

function renderStatement() {
    const c = blockContent('statement');
    return `<div class="grid gap-4 md:grid-cols-2" data-panel="statement" data-block-slug="statement">
        ${field('brand_title', 'Заголовок бренду', c.brand_title)}
        ${field('quote_fallback', 'Цитата (fallback)', c.quote_fallback, 'textarea')}
        ${field('made_with', 'Made with', c.made_with)}
        <div class="md:col-span-2"><button type="button" data-save-block class="fi-btn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Зберегти</button></div>
    </div>`;
}

function renderFooter() {
    const c = blockContent('footer');
    return `<div class="grid gap-4 md:grid-cols-2" data-panel="footer" data-block-slug="footer">
        ${field('description', 'Опис', c.description, 'textarea')}
        ${field('cta_primary', 'Кнопка 1', c.cta_primary)}
        ${field('cta_secondary', 'Кнопка 2', c.cta_secondary)}
        ${field('phone_1', 'Телефон 1', c.phone_1)}
        ${field('phone_2', 'Телефон 2', c.phone_2)}
        ${field('email', 'Email', c.email)}
        ${field('office_title', 'Офіс — заголовок', c.office_title)}
        ${field('office_address', 'Офіс — адреса', c.office_address, 'textarea')}
        ${field('partners_title', 'Партнери — заголовок', c.partners_title)}
        ${field('partners_address', 'Партнери — адреса', c.partners_address, 'textarea')}
        ${field('copyright', 'Копірайт', c.copyright)}
        <div class="md:col-span-2"><button type="button" data-save-block class="fi-btn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Зберегти</button></div>
    </div>`;
}

function renderNavigator() {
    const c = blockContent('navigator');
    const links = (c.links ?? []).map((l, i) => `
        <div class="grid gap-2 rounded border p-3 md:grid-cols-2" data-nav-link="${i}">
            ${field(`nav_label_${i}`, 'Текст', l.label)}
            ${field(`nav_href_${i}`, 'URL', l.href)}
        </div>
    `).join('');

    return `<div class="space-y-4" data-panel="navigator" data-block-slug="navigator" data-nav-count="${(c.links ?? []).length}">
        <p class="text-sm text-gray-500">Посилання в шапці сайту</p>
        ${links}
        <button type="button" data-save-navigator class="fi-btn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Зберегти навігацію</button>
    </div>`;
}

function renderListItem(type, item, index) {
    if (type === 'reviews') {
        return `<div class="rounded-lg border p-4 space-y-3" data-item-id="${item.id}" data-item-type="reviews">
            ${field('quote', 'Відгук', item.quote, 'textarea')}
            ${field('author_name', "Ім'я", item.author_name)}
            ${field('author_role', 'Роль', item.author_role)}
            ${field('sort_order', 'Сортування', item.sort_order)}
            ${field('is_active', 'Активний', item.is_active, 'checkbox')}
            <div class="flex gap-2">
                <button type="button" data-save-item class="rounded bg-primary-600 px-3 py-1.5 text-xs text-white">Зберегти</button>
                <button type="button" data-delete-item class="rounded bg-danger-600 px-3 py-1.5 text-xs text-white">Видалити</button>
            </div>
        </div>`;
    }
    if (type === 'benefits') {
        return `<div class="rounded-lg border p-4 space-y-3" data-item-id="${item.id}" data-item-type="benefits">
            ${field('number_label', 'Номер', item.number_label)}
            ${field('title', 'Заголовок', item.title)}
            ${field('text', 'Текст', item.text)}
            ${field('sort_order', 'Сортування', item.sort_order)}
            ${field('is_active', 'Активний', item.is_active, 'checkbox')}
            <div class="flex gap-2">
                <button type="button" data-save-item class="rounded bg-primary-600 px-3 py-1.5 text-xs text-white">Зберегти</button>
                <button type="button" data-delete-item class="rounded bg-danger-600 px-3 py-1.5 text-xs text-white">Видалити</button>
            </div>
        </div>`;
    }
    if (type === 'audience') {
        return `<div class="rounded-lg border p-4 space-y-3" data-item-id="${item.id}" data-item-type="audience">
            ${field('name', 'Назва', item.name)}
            ${imageField('image_path', 'Зображення', item.image_path, 'homepage/audience')}
            ${field('href', 'Посилання', item.href)}
            ${field('sort_order', 'Сортування', item.sort_order)}
            ${field('is_active', 'Активний', item.is_active, 'checkbox')}
            <div class="flex gap-2">
                <button type="button" data-save-item class="rounded bg-primary-600 px-3 py-1.5 text-xs text-white">Зберегти</button>
                <button type="button" data-delete-item class="rounded bg-danger-600 px-3 py-1.5 text-xs text-white">Видалити</button>
            </div>
        </div>`;
    }
    if (type === 'ribbon') {
        const url = item.url || mediaUrl(item.path);
        return `<div class="rounded-lg border p-4 space-y-3" data-item-id="${item.id}" data-item-type="ribbon">
            ${imageField('path', 'Зображення', item.path, 'homepage/ribbon')}
            ${url ? `<img src="${url}" class="max-h-32 rounded" alt="">` : ''}
            ${field('alt_text', 'Alt', item.alt_text)}
            ${field('width', 'Ширина', item.width)}
            ${field('height', 'Висота', item.height)}
            ${field('sort_order', 'Сортування', item.sort_order)}
            ${field('is_active', 'Активний', item.is_active, 'checkbox')}
            <div class="flex gap-2">
                <button type="button" data-save-item class="rounded bg-primary-600 px-3 py-1.5 text-xs text-white">Зберегти</button>
                <button type="button" data-delete-item class="rounded bg-danger-600 px-3 py-1.5 text-xs text-white">Видалити</button>
            </div>
        </div>`;
    }
    return '';
}

function renderList(type, items) {
    const labels = {
        reviews: 'відгук',
        benefits: 'перевагу',
        audience: 'картку',
        ribbon: 'зображення',
    };
    return `<div class="space-y-4" data-panel="${type}_list">
        <button type="button" data-add-item data-item-type="${type}" class="rounded-lg border border-dashed px-4 py-2 text-sm">+ Додати ${labels[type]}</button>
        <div class="grid gap-4">${items.map((item, i) => renderListItem(type, item, i)).join('')}</div>
    </div>`;
}

function renderPanel() {
    const renderers = {
        globals: renderGlobals,
        hero: renderHero,
        about_us: renderAboutUs,
        product_box: renderProductBox,
        benefits: renderBenefitsHeader,
        benefits_list: () => renderList('benefits', state.benefits),
        audience: () => renderList('audience', state.audience_cards),
        reviews: () => renderList('reviews', state.reviews),
        ribbon: () => renderList('ribbon', state.ribbon_images.map((i) => ({ ...i, url: mediaUrl(i.path) }))),
        statement: renderStatement,
        footer: renderFooter,
        navigator: renderNavigator,
    };

    panelsEl.innerHTML = renderers[activeTab]?.() ?? '';
    bindPanelEvents();
}

function renderTabs() {
    tabsEl.innerHTML = tabs
        .map(
            (tab) =>
                `<button type="button" role="tab" data-tab="${tab.id}" class="rounded-lg px-3 py-2 text-sm font-medium ${
                    activeTab === tab.id ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }">${tab.label}</button>`,
        )
        .join('');

    tabsEl.querySelectorAll('[data-tab]').forEach((btn) => {
        btn.addEventListener('click', () => {
            activeTab = btn.dataset.tab;
            renderTabs();
            renderPanel();
        });
    });
}

async function load() {
    setStatus('Завантаження…');
    const { data } = await http.get('/');
    state = data;
    setStatus('');
    renderTabs();
    renderPanel();
}

function bindPanelEvents() {
    panelsEl.querySelectorAll('[data-save-block]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const panel = btn.closest('[data-block-slug]');
            try {
                await saveBlock(panel.dataset.blockSlug, panel);
            } catch (e) {
                setStatus(e.response?.data?.message ?? 'Помилка збереження', 'error');
            }
        });
    });

    panelsEl.querySelector('[data-save-globals]')?.addEventListener('click', async () => {
        try {
            await saveGlobals(panelsEl.querySelector('[data-panel="globals"]'));
        } catch (e) {
            setStatus(e.response?.data?.message ?? 'Помилка', 'error');
        }
    });

    panelsEl.querySelector('[data-save-navigator]')?.addEventListener('click', async () => {
        const panel = panelsEl.querySelector('[data-panel="navigator"]');
        const count = Number(panel.dataset.navCount);
        const links = [];
        for (let i = 0; i < count; i += 1) {
            links.push({
                label: panel.querySelector(`[data-field="nav_label_${i}"]`)?.value ?? '',
                href: panel.querySelector(`[data-field="nav_href_${i}"]`)?.value ?? '',
            });
        }
        try {
            await http.put('/blocks/navigator', { content: { links } });
            setStatus('Навігацію збережено', 'success');
            await load();
        } catch (e) {
            setStatus(e.response?.data?.message ?? 'Помилка', 'error');
        }
    });

    panelsEl.querySelectorAll('[data-upload-for]').forEach((input) => {
        input.addEventListener('change', async () => {
            const file = input.files?.[0];
            if (!file) return;
            const wrap = input.closest('[data-image-field]');
            const fieldId = input.dataset.uploadFor;
            const directory = wrap?.dataset.directory ?? 'homepage';
            try {
                setStatus('Завантаження…');
                const uploaded = await uploadImage(file, directory);
                const hidden = wrap.querySelector(`[data-field="${fieldId}"]`);
                if (hidden) hidden.value = uploaded.path;
                setStatus('Зображення завантажено', 'success');
                renderPanel();
            } catch (e) {
                setStatus(e.response?.data?.message ?? 'Помилка завантаження', 'error');
            }
        });
    });

    panelsEl.querySelectorAll('[data-save-item]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const card = btn.closest('[data-item-id]');
            const type = card.dataset.itemType;
            const id = card.dataset.itemId;
            const payload = collectFields(card);
            const routes = {
                reviews: `/reviews/${id}`,
                benefits: `/benefits/${id}`,
                audience: `/audience-cards/${id}`,
                ribbon: `/ribbon-images/${id}`,
            };
            try {
                await http.put(routes[type], payload);
                setStatus('Збережено', 'success');
                await load();
            } catch (e) {
                setStatus(e.response?.data?.message ?? 'Помилка', 'error');
            }
        });
    });

    panelsEl.querySelectorAll('[data-delete-item]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const card = btn.closest('[data-item-id]');
            const type = card.dataset.itemType;
            const id = card.dataset.itemId;
            if (!confirm('Видалити запис?')) return;
            const routes = {
                reviews: `/reviews/${id}`,
                benefits: `/benefits/${id}`,
                audience: `/audience-cards/${id}`,
                ribbon: `/ribbon-images/${id}`,
            };
            try {
                await http.delete(routes[type]);
                setStatus('Видалено', 'success');
                await load();
            } catch (e) {
                setStatus(e.response?.data?.message ?? 'Помилка', 'error');
            }
        });
    });

    panelsEl.querySelectorAll('[data-add-item]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const type = btn.dataset.itemType;
            const defaults = {
                reviews: { quote: '', author_name: 'Новий відгук', author_role: '', sort_order: 99, is_active: true },
                benefits: { number_label: '1.', title: 'Нова перевага', text: '', sort_order: 99, is_active: true },
                audience: { name: 'Нова картка', image_path: '', href: '', sort_order: 99, is_active: true },
                ribbon: { path: '', alt_text: 'NOEMA', width: 900, height: 1200, sort_order: 99, is_active: false },
            };
            const routes = {
                reviews: '/reviews',
                benefits: '/benefits',
                audience: '/audience-cards',
                ribbon: '/ribbon-images',
            };
            if (type === 'ribbon') {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.onchange = async () => {
                    const file = input.files?.[0];
                    if (!file) return;
                    try {
                        setStatus('Завантаження…');
                        const uploaded = await uploadImage(file, 'homepage/ribbon');
                        await http.post(routes[type], { ...defaults.ribbon, path: uploaded.path, is_active: true });
                        setStatus('Зображення додано', 'success');
                        await load();
                    } catch (e) {
                        setStatus(e.response?.data?.message ?? 'Помилка', 'error');
                    }
                };
                input.click();
                return;
            }
            try {
                await http.post(routes[type], defaults[type]);
                setStatus('Додано', 'success');
                await load();
            } catch (e) {
                setStatus(e.response?.data?.message ?? 'Помилка', 'error');
            }
        });
    });
}

load().catch((e) => setStatus(e.message, 'error'));
