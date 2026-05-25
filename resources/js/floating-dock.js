import { initFormSubmit } from './form-submit';

const lockScroll = (locked) => {
    document.documentElement.classList.toggle('overflow-hidden', locked);
    document.body.classList.toggle('overflow-hidden', locked);
};

const initConsultationModal = () => {
    const modal = document.querySelector('[data-consultation-modal]');

    if (!modal) {
        return;
    }

    const panel = modal.querySelector('[data-consultation-panel]');
    const openers = document.querySelectorAll('[data-consultation-open]');
    const closers = modal.querySelectorAll('[data-consultation-close]');

    const open = () => {
        modal.hidden = false;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        lockScroll(true);
        window.setTimeout(() => panel?.classList.add('is-visible'), 16);
    };

    const close = () => {
        panel?.classList.remove('is-visible');
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        lockScroll(false);
        window.setTimeout(() => {
            modal.hidden = true;
        }, 320);
    };

    openers.forEach((btn) => btn.addEventListener('click', open));
    closers.forEach((btn) => btn.addEventListener('click', close));

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            close();
        }
    });

    modal.querySelector('[data-ajax-form]')?.addEventListener('form:success', () => {
        window.setTimeout(close, 2200);
    });
};

export const initFloatingDock = () => {
    initConsultationModal();
    initFormSubmit('[data-ajax-form]');
};
