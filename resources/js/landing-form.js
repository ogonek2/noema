const getCsrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const applyMask = (input) => {
    const pattern = input.dataset.mask;

    if (!pattern || input.dataset.maskApplied === '1') {
        return;
    }

    input.dataset.maskApplied = '1';
    input.setAttribute('autocomplete', 'tel');

    input.addEventListener('input', () => {
        const digits = input.value.replace(/\D/g, '');

        if (pattern.includes('+380') && digits.length) {
            let normalized = digits;

            if (normalized.startsWith('380')) {
                normalized = normalized.slice(3);
            } else if (normalized.startsWith('80')) {
                normalized = normalized.slice(2);
            } else if (normalized.startsWith('0')) {
                normalized = normalized.slice(1);
            }

            normalized = normalized.slice(0, 9);

            let formatted = '+380';

            if (normalized.length > 0) {
                formatted += ` (${normalized.slice(0, 2)}`;
            }

            if (normalized.length >= 2) {
                formatted += `) ${normalized.slice(2, 5)}`;
            }

            if (normalized.length >= 5) {
                formatted += `-${normalized.slice(5, 7)}`;
            }

            if (normalized.length >= 7) {
                formatted += `-${normalized.slice(7, 9)}`;
            }

            input.value = formatted;

            return;
        }

        input.value = input.value;
    });
};

const collectFields = (form) => {
    const fields = {};

    form.querySelectorAll('[name]').forEach((element) => {
        const name = element.name;

        if (!name || name === '_token') {
            return;
        }

        if (element.type === 'checkbox') {
            fields[name] = element.checked;

            return;
        }

        fields[name] = element.value;
    });

    return fields;
};

const showErrors = (form, errors) => {
    form.querySelectorAll('[data-field-error]').forEach((node) => {
        node.hidden = true;
        node.textContent = '';
    });

    form.querySelectorAll('.landing-form__control, .landing-form__checkbox-input').forEach((el) => {
        el.classList.remove('is-invalid');
    });

    Object.entries(errors ?? {}).forEach(([key, messages]) => {
        const message = Array.isArray(messages) ? messages[0] : messages;
        const errorNode = form.querySelector(`[data-field-error="${key}"]`);
        const control = form.querySelector(`[name="${key}"]`);

        if (errorNode) {
            errorNode.textContent = message;
            errorNode.hidden = false;
        }

        control?.classList.add('is-invalid');
    });
};

document.querySelectorAll('[data-landing-form]').forEach((form) => {
    form.querySelectorAll('[data-mask]').forEach(applyMask);

    const submitBtn = form.querySelector('[data-form-submit]');
    const messageNode = form.querySelector('[data-form-message]');
    const submitUrl = form.dataset.submitUrl;

    if (!submitUrl) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        submitBtn.disabled = true;
        messageNode.hidden = true;
        messageNode.classList.remove('is-success', 'is-error');

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    fields: collectFields(form),
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    showErrors(form, data.errors);
                    messageNode.textContent = data.message ?? 'Перевірте поля форми.';
                    messageNode.classList.add('is-error');
                    messageNode.hidden = false;
                } else {
                    messageNode.textContent = data.message ?? 'Не вдалося надіслати форму.';
                    messageNode.classList.add('is-error');
                    messageNode.hidden = false;
                }

                return;
            }

            form.reset();
            showErrors(form, {});
            messageNode.textContent = data.message ?? form.dataset.successMessage ?? 'Дякуємо!';
            messageNode.classList.add('is-success');
            messageNode.hidden = false;
        } catch {
            messageNode.textContent = 'Помилка мережі. Спробуйте ще раз.';
            messageNode.classList.add('is-error');
            messageNode.hidden = false;
        } finally {
            submitBtn.disabled = false;
        }
    });
});
