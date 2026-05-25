@props(['content' => []])

@php
    use App\Enums\LandingFormFieldType;

    $navTheme = $content['nav_theme'] ?? 'light';
    $isDark = $navTheme === 'dark';
    $formKey = $content['form_key'] ?? '';
    $fields = collect($content['fields'] ?? [])->sortBy('sort_order')->values();
    $submitUrl = filled($formKey) ? route('forms.submit', $formKey) : null;
@endphp

<section @class([
    'landing-form-section',
    'landing-form-section--dark' => $isDark,
    'landing-form-section--light' => ! $isDark,
]) data-nav-theme="{{ $navTheme }}" data-aos="fade-up">
    <div class="mx-auto w-full max-w-layout px-5 py-16 lg:px-8 lg:py-24">
        @if (filled($content['title'] ?? null))
            <h2 class="landing-form-section__title">{{ $content['title'] }}</h2>
        @endif
        @if (filled($content['subtitle'] ?? null))
            <p class="landing-form-section__subtitle">{{ $content['subtitle'] }}</p>
        @endif

        @if ($submitUrl && $fields->isNotEmpty())
            <form class="landing-form" data-landing-form data-form-key="{{ $formKey }}"
                data-submit-url="{{ $submitUrl }}"
                data-success-message="{{ $content['success_message'] ?? 'Дякуємо! Ми звʼяжемося з вами найближчим часом.' }}"
                novalidate>
                @csrf
                <div class="landing-form__grid">
                    @foreach ($fields as $field)
                        @php
                            $type = LandingFormFieldType::tryFrom($field['type'] ?? '') ?? LandingFormFieldType::Text;
                            $key = $field['key'];
                            $isHalf = ($field['width'] ?? 'full') === 'half';
                            $inputType = match ($type) {
                                LandingFormFieldType::Email => 'email',
                                LandingFormFieldType::Tel => 'tel',
                                LandingFormFieldType::Number => 'number',
                                LandingFormFieldType::Date => 'date',
                                LandingFormFieldType::Url => 'url',
                                default => 'text',
                            };
                        @endphp

                        <div @class([
                            'landing-form__field',
                            'landing-form__field--half' => $isHalf,
                            'landing-form__field--checkbox' => $type === LandingFormFieldType::Checkbox,
                        ]) data-field-wrap="{{ $key }}">
                            @if ($type === LandingFormFieldType::Checkbox)
                                <label class="landing-form__checkbox">
                                    <input type="checkbox" name="{{ $key }}" value="1"
                                        @checked(old($key))
                                        @required($field['required'] ?? false)
                                        class="landing-form__checkbox-input">
                                    <span class="landing-form__checkbox-label">
                                        {{ $field['label'] }}
                                        @if ($field['required'] ?? false)
                                            <span class="landing-form__required">*</span>
                                        @endif
                                    </span>
                                </label>
                            @elseif ($type === LandingFormFieldType::Textarea)
                                <label class="landing-form__label" for="lf-{{ $key }}">
                                    {{ $field['label'] }}
                                    @if ($field['required'] ?? false)
                                        <span class="landing-form__required">*</span>
                                    @endif
                                </label>
                                <textarea id="lf-{{ $key }}" name="{{ $key }}" rows="4"
                                    class="landing-form__control"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    @required($field['required'] ?? false)>{{ old($key) }}</textarea>
                            @elseif ($type === LandingFormFieldType::Select)
                                <label class="landing-form__label" for="lf-{{ $key }}">
                                    {{ $field['label'] }}
                                    @if ($field['required'] ?? false)
                                        <span class="landing-form__required">*</span>
                                    @endif
                                </label>
                                <select id="lf-{{ $key }}" name="{{ $key }}" class="landing-form__control"
                                    @required($field['required'] ?? false)>
                                    <option value="">{{ $field['placeholder'] ?: 'Оберіть…' }}</option>
                                    @foreach ($field['options'] ?? [] as $option)
                                        <option value="{{ $option }}" @selected(old($key) === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @else
                                <label class="landing-form__label" for="lf-{{ $key }}">
                                    {{ $field['label'] }}
                                    @if ($field['required'] ?? false)
                                        <span class="landing-form__required">*</span>
                                    @endif
                                </label>
                                <input id="lf-{{ $key }}" name="{{ $key }}" type="{{ $inputType }}"
                                    value="{{ old($key) }}"
                                    class="landing-form__control"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    @if (filled($field['mask'] ?? null)) data-mask="{{ $field['mask'] }}" @endif
                                    @required($field['required'] ?? false)>
                            @endif

                            @if (filled($field['help_text'] ?? null))
                                <p class="landing-form__help">{{ $field['help_text'] }}</p>
                            @endif
                            <p class="landing-form__error" data-field-error="{{ $key }}" hidden></p>
                        </div>
                    @endforeach
                </div>

                <div class="landing-form__footer">
                    <button type="submit" class="landing-form__submit" data-form-submit>
                        {{ $content['submit_label'] ?? 'Надіслати' }}
                    </button>
                    <p class="landing-form__message" data-form-message hidden></p>
                </div>
            </form>
        @else
            <p class="landing-form-section__empty">Форму не налаштовано. Додайте поля в адмінці.</p>
        @endif
    </div>
</section>

@push('scripts')
    @vite('resources/js/landing-form.js')
@endpush
