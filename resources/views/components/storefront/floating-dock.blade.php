@props(['enabled' => true])

@if ($enabled)
    <div class="floating-dock" data-floating-dock aria-label="Швидкі дії">
        <button type="button" class="floating-dock__btn floating-dock__btn--consult" data-consultation-open
            aria-label="Замовити консультацію" title="Консультація">
            <span class="floating-dock__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                    <path d="M4.5 5.5h15v11h-4.2l-3.3 2.6-3.3-2.6h-4.2v-11z" />
                    <path d="M8.5 9.5h7M8.5 12.5h4.5" stroke-linecap="round" />
                </svg>
            </span>
            <span class="floating-dock__label">Консультація</span>
        </button>
    </div>
@endif
