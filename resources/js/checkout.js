const debounce = (fn, ms = 400) => {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), ms);
    };
};

const escapeHtml = (value) =>
    String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

const MANUAL_METHODS = ['nova_poshta_courier', 'ukrposhta', 'meest'];

const MANUAL_ADDRESS_LABELS = {
    nova_poshta_courier: 'Адреса доставки (вулиця, будинок, підʼїзд)',
    ukrposhta: 'Адреса / відділення Укрпошти',
    meest: 'Адреса / відділення Meest',
};

const initShippingPanels = (form) => {
    const npPanel = form.querySelector('[data-checkout-shipping-panel="nova_poshta_warehouse"]');
    const manualPanel = form.querySelector('[data-checkout-shipping-panel-manual]');
    const addressLabel = form.querySelector('[data-checkout-manual-address-label]');
    const cityRef = document.getElementById('shipping_city_ref');
    const cityNameHidden = document.getElementById('shipping_city_name');
    const manualCity = document.getElementById('manual_city');
    const manualAddress = document.getElementById('manual_address');

    const syncPanels = () => {
        const method = form.querySelector('[data-checkout-shipping-option]:checked')?.value;

        npPanel?.classList.toggle('hidden', method !== 'nova_poshta_warehouse');
        manualPanel?.classList.toggle('hidden', !MANUAL_METHODS.includes(method));

        if (addressLabel && method && MANUAL_ADDRESS_LABELS[method]) {
            addressLabel.innerHTML = `${MANUAL_ADDRESS_LABELS[method]} <span class="text-black-brand">*</span>`;
        }

        if (method === 'nova_poshta_warehouse') {
            manualCity?.removeAttribute('name');
            manualAddress?.removeAttribute('name');
            cityNameHidden?.setAttribute('name', 'shipping_city_name');
        } else if (MANUAL_METHODS.includes(method)) {
            cityRef && (cityRef.value = '');
            document.getElementById('shipping_warehouse_ref') && (document.getElementById('shipping_warehouse_ref').value = '');
            document.getElementById('shipping_warehouse_name') && (document.getElementById('shipping_warehouse_name').value = '');
            cityNameHidden?.removeAttribute('name');
            manualCity?.setAttribute('name', 'shipping_city_name');
            manualAddress?.setAttribute('name', 'shipping_address');
        }
    };

    form.querySelectorAll('[data-checkout-shipping-option]').forEach((radio) => {
        radio.addEventListener('change', syncPanels);
    });

    syncPanels();
};

const initNovaPoshta = (root) => {
    const citiesUrl = root.dataset.citiesUrl;
    const warehousesUrl = root.dataset.warehousesUrl;
    const cityInput = root.querySelector('[data-np-city-input]');
    const warehouseInput = root.querySelector('[data-np-warehouse-input]');
    const cityResults = root.querySelector('[data-np-city-results]');
    const warehouseResults = root.querySelector('[data-np-warehouse-results]');
    const cityRefField = document.getElementById('shipping_city_ref');
    const cityNameField = document.getElementById('shipping_city_name');
    const warehouseRefField = document.getElementById('shipping_warehouse_ref');
    const warehouseNameField = document.getElementById('shipping_warehouse_name');
    const configWarning = root.querySelector('[data-np-config-warning]');
    const cityHint = root.querySelector('[data-np-city-hint]');
    const warehouseHint = root.querySelector('[data-np-warehouse-hint]');

    if (!cityInput || !warehouseInput) {
        return;
    }

    let cityConfigured = true;
    let cityAbort = null;
    let warehouseAbort = null;
    let cityLoading = false;
    let warehouseLoading = false;

    const hideList = (list) => list?.classList.add('hidden');
    const showList = (list) => list?.classList.remove('hidden');

    const setHint = (el, text) => {
        if (!el) {
            return;
        }

        if (text) {
            el.textContent = text;
            el.classList.remove('hidden');
        } else {
            el.textContent = '';
            el.classList.add('hidden');
        }
    };

    const renderListState = (list, state, items, onPick) => {
        if (!list) {
            return;
        }

        if (state === 'loading') {
            list.innerHTML = '<li class="px-3 py-3 text-[0.78rem] text-black-brand/45">Завантаження…</li>';
            showList(list);

            return;
        }

        if (state === 'hint') {
            list.innerHTML = `<li class="px-3 py-2 text-[0.78rem] text-black-brand/45">${escapeHtml(items)}</li>`;
            showList(list);

            return;
        }

        if (!items.length) {
            list.innerHTML = '<li class="px-3 py-2 text-[0.78rem] text-black-brand/45">Нічого не знайдено</li>';
            showList(list);

            return;
        }

        list.innerHTML = items
            .map(
                (item, index) => `
            <li>
                <button type="button" class="checkout-np-option w-full px-3 py-2.5 text-left text-[0.82rem] transition hover:bg-black-brand/[0.04]"
                    data-np-index="${index}">
                    ${escapeHtml(item.name)}${item.area ? `<span class="text-black-brand/45"> · ${escapeHtml(item.area)}</span>` : ''}
                    ${item.number ? `<span class="block text-[0.72rem] text-black-brand/45">№${escapeHtml(item.number)}${item.address ? ` — ${escapeHtml(item.address)}` : ''}</span>` : ''}
                    ${!item.number && item.address ? `<span class="block text-[0.72rem] text-black-brand/45">${escapeHtml(item.address)}</span>` : ''}
                </button>
            </li>`,
            )
            .join('');

        list.querySelectorAll('[data-np-index]').forEach((btn) => {
            btn.addEventListener('click', () => {
                onPick(items[Number(btn.dataset.npIndex)]);
                hideList(list);
            });
        });

        showList(list);
    };

    const fetchJson = async (url, signal) => {
        const response = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'Помилка зʼєднання з API Нової Пошти');
        }

        if (data.configured === false) {
            throw new Error(data.message || 'API Нової Пошти не налаштовано');
        }

        return data;
    };

    const resetWarehouse = () => {
        warehouseRefField.value = '';
        warehouseNameField.value = '';
        warehouseInput.value = '';
        warehouseInput.disabled = true;
        warehouseInput.placeholder = 'Спочатку оберіть місто';
        hideList(warehouseResults);
        setHint(warehouseHint, 'Після вибору міста введіть мінімум 2 символи для пошуку відділення');
    };

    const enableWarehouse = () => {
        warehouseInput.disabled = false;
        warehouseInput.placeholder = '№ відділення, вулиця або назва';
    };

    const searchCities = debounce(async () => {
        if (!cityConfigured) {
            return;
        }

        const query = cityInput.value.trim();

        if (query.length < 2) {
            hideList(cityResults);
            setHint(cityHint, query.length ? 'Мінімум 2 символи' : '');

            return;
        }

        cityAbort?.abort();
        cityAbort = new AbortController();
        cityLoading = true;
        renderListState(cityResults, 'loading');
        setHint(cityHint, '');

        try {
            const data = await fetchJson(`${citiesUrl}?q=${encodeURIComponent(query)}`, cityAbort.signal);

            if (data.configured === false) {
                cityConfigured = false;
                configWarning?.classList.remove('hidden');
                hideList(cityResults);

                return;
            }

            renderListState(cityResults, 'ok', data.items || [], (item) => {
                cityRefField.value = item.ref;
                cityNameField.value = item.name;
                cityInput.value = item.name;
                resetWarehouse();
                enableWarehouse();
                warehouseInput.focus();
            });

            if (!data.items?.length) {
                const emptyMsg = data.message || data.hint || 'Нічого не знайдено';
                setHint(cityHint, emptyMsg);
                renderListState(cityResults, 'hint', emptyMsg, () => {});
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                hideList(cityResults);
                setHint(cityHint, error.message || 'Помилка пошуку');
                renderListState(cityResults, 'hint', error.message || 'Помилка', () => {});
            }
        } finally {
            cityLoading = false;
        }
    }, 350);

    const searchWarehouses = debounce(async () => {
        const cityRef = cityRefField.value;

        if (!cityRef || warehouseInput.disabled) {
            return;
        }

        const query = warehouseInput.value.trim();

        if (query.length < 2) {
            hideList(warehouseResults);
            setHint(warehouseHint, 'Введіть мінімум 2 символи (№, вулиця, назва)');

            return;
        }

        warehouseAbort?.abort();
        warehouseAbort = new AbortController();
        warehouseLoading = true;
        renderListState(warehouseResults, 'loading');
        setHint(warehouseHint, '');

        const url = new URL(warehousesUrl, window.location.origin);
        url.searchParams.set('city_ref', cityRef);
        url.searchParams.set('q', query);

        try {
            const data = await fetchJson(url.toString(), warehouseAbort.signal);

            renderListState(warehouseResults, 'ok', data.items || [], (item) => {
                const label = item.number ? `${item.name} (№${item.number})` : item.name;

                warehouseRefField.value = item.ref;
                warehouseNameField.value = label;
                warehouseInput.value = label;
            });

            if (!data.items?.length) {
                setHint(warehouseHint, data.message || data.hint || 'Нічого не знайдено');
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                hideList(warehouseResults);
            }
        } finally {
            warehouseLoading = false;
        }
    }, 450);

    cityInput.addEventListener('input', () => {
        cityRefField.value = '';
        cityNameField.value = '';
        resetWarehouse();
        searchCities();
    });

    cityInput.addEventListener('focus', () => {
        if (cityInput.value.trim().length >= 2 && !cityLoading) {
            searchCities();
        }
    });

    warehouseInput.addEventListener('input', () => {
        warehouseRefField.value = '';
        warehouseNameField.value = '';
        searchWarehouses();
    });

    warehouseInput.addEventListener('focus', () => {
        if (cityRefField.value && warehouseInput.value.trim().length >= 2 && !warehouseLoading) {
            searchWarehouses();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            hideList(cityResults);
            hideList(warehouseResults);
        }
    });

    if (cityRefField.value) {
        enableWarehouse();
    }

    if (configWarning && !configWarning.classList.contains('hidden')) {
        cityConfigured = false;
    }
};

export const initCheckout = () => {
    const form = document.querySelector('[data-checkout-form]');

    if (form) {
        initShippingPanels(form);
    }

    const npRoot = document.querySelector('[data-np-root]');

    if (npRoot) {
        initNovaPoshta(npRoot);
    }
};

document.addEventListener('DOMContentLoaded', initCheckout);
