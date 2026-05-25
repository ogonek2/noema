<?php

namespace App\Http\Requests;

use App\Services\CartService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $customizationRules = [
            'customizations' => ['nullable', 'array'],
            'customizations.*.slug' => ['required', 'string', 'max:120'],
            'customizations.*.value' => ['nullable'],
        ];

        if ($this->filled('lines')) {
            return [
                'product_slug' => ['required', 'string', Rule::exists('products', 'slug')->where('is_active', true)],
                'lines' => ['required', 'array', 'min:1', 'max:'.CartService::MAX_BATCH_LINES],
                'lines.*.variant_id' => ['required', 'integer', 'exists:product_variants,id'],
                'lines.*.notes' => ['nullable', 'string', 'max:2000'],
                'lines.*.customizations' => ['nullable', 'array'],
                'lines.*.customizations.*.slug' => ['required', 'string', 'max:120'],
                'lines.*.customizations.*.value' => ['nullable'],
            ];
        }

        return [
            'product_slug' => ['required', 'string', Rule::exists('products', 'slug')->where('is_active', true)],
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:2000'],
            ...$customizationRules,
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('lines') && $this->filled('variant_id')) {
                $validator->errors()->add('lines', 'Вкажіть або один товар, або набір — не обидва одночасно.');
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'product_slug.required' => 'Товар не вказано.',
            'variant_id.required' => 'Оберіть розмір.',
            'quantity.required' => 'Вкажіть кількість.',
            'quantity.min' => 'Мінімальна кількість — 1.',
            'quantity.max' => 'Максимальна кількість — 99.',
            'lines.required' => 'Додайте позиції до набору.',
            'lines.max' => 'Занадто багато позицій у наборі.',
            'lines.*.variant_id.required' => 'Оберіть розмір для кожної позиції набору.',
        ];
    }
}
