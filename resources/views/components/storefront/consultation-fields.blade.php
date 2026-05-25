@props(['fields' => [], 'idPrefix' => 'cf'])

@php
    use App\Enums\LandingFormFieldType;

    $fields = collect($fields)->sortBy('sort_order')->values();
@endphp

<div class="form-fields-grid">
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
            'form-field',
            'form-field--half' => $isHalf,
            'form-field--checkbox' => $type === LandingFormFieldType::Checkbox,
        ])>
            @if ($type === LandingFormFieldType::Checkbox)
                <label class="form-checkbox">
                    <input type="checkbox" name="{{ $key }}" value="1" class="form-control form-checkbox-input"
                        @required($field['required'] ?? false)>
                    <span class="form-checkbox-label">
                        {{ $field['label'] }}
                        @if ($field['required'] ?? false)
                            <span class="form-required">*</span>
                        @endif
                    </span>
                </label>
            @elseif ($type === LandingFormFieldType::Textarea)
                <label class="form-label" for="{{ $idPrefix }}-{{ $key }}">
                    {{ $field['label'] }}
                    @if ($field['required'] ?? false)
                        <span class="form-required">*</span>
                    @endif
                </label>
                <textarea id="{{ $idPrefix }}-{{ $key }}" name="{{ $key }}" rows="4" class="form-control"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    @required($field['required'] ?? false)></textarea>
            @elseif ($type === LandingFormFieldType::Select)
                <label class="form-label" for="{{ $idPrefix }}-{{ $key }}">
                    {{ $field['label'] }}
                    @if ($field['required'] ?? false)
                        <span class="form-required">*</span>
                    @endif
                </label>
                <select id="{{ $idPrefix }}-{{ $key }}" name="{{ $key }}" class="form-control"
                    @required($field['required'] ?? false)>
                    <option value="">{{ $field['placeholder'] ?: 'Оберіть…' }}</option>
                    @foreach ($field['options'] ?? [] as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            @else
                <label class="form-label" for="{{ $idPrefix }}-{{ $key }}">
                    {{ $field['label'] }}
                    @if ($field['required'] ?? false)
                        <span class="form-required">*</span>
                    @endif
                </label>
                <input id="{{ $idPrefix }}-{{ $key }}" name="{{ $key }}" type="{{ $inputType }}"
                    class="form-control"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    @if (filled($field['mask'] ?? null)) data-mask="{{ $field['mask'] }}" @endif
                    @required($field['required'] ?? false)>
            @endif

            @if (filled($field['help_text'] ?? null))
                <p class="form-help">{{ $field['help_text'] }}</p>
            @endif
            <p class="form-error" data-field-error="{{ $key }}" hidden></p>
        </div>
    @endforeach
</div>
