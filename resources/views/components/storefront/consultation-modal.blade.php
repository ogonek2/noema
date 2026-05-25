@props(['schema' => []])

@php
    $formKey = $schema['form_key'] ?? 'consultation';
    $submitUrl = route('forms.submit', $formKey);
@endphp

<div class="consultation-modal" data-consultation-modal hidden aria-hidden="true" role="dialog" aria-modal="true"
    aria-labelledby="consultation-modal-title">
    <div class="consultation-modal__backdrop" data-consultation-close></div>

    <div class="consultation-modal__panel" data-consultation-panel>
        <button type="button" class="consultation-modal__close" data-consultation-close aria-label="Закрити">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" class="h-5 w-5">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>

        <div class="consultation-modal__head">
            <p class="consultation-modal__eyebrow">[ NOEMA ]</p>
            <h2 id="consultation-modal-title" class="consultation-modal__title">{{ $schema['title'] ?? 'Консультація' }}</h2>
            @if (filled($schema['subtitle'] ?? null))
                <p class="consultation-modal__subtitle">{{ $schema['subtitle'] }}</p>
            @endif
        </div>

        <form class="consultation-modal__form" data-ajax-form data-control-selector=".form-control"
            data-submit-url="{{ $submitUrl }}"
            data-success-message="{{ $schema['success_message'] ?? 'Дякуємо! Ми звʼяжемося з вами найближчим часом.' }}"
            novalidate>
            @csrf
            <x-storefront.consultation-fields :fields="$schema['fields'] ?? []" id-prefix="cm" />

            <div class="consultation-modal__footer">
                <button type="submit" class="consultation-modal__submit" data-form-submit>
                    Надіслати заявку
                </button>
                <p class="consultation-modal__message" data-form-message hidden></p>
            </div>
        </form>
    </div>
</div>
