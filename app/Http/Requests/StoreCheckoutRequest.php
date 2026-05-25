<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $manualShipping = [
            ShippingMethod::NovaPoshtaCourier->value,
            ShippingMethod::Ukrposhta->value,
            ShippingMethod::Meest->value,
        ];

        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:32', 'regex:/^[\d\s\+\-\(\)]{10,32}$/'],
            'customer_email' => ['nullable', 'email', 'max:120'],
            'shipping_method' => ['required', Rule::enum(ShippingMethod::class)],
            'shipping_city_ref' => ['required_if:shipping_method,nova_poshta_warehouse', 'nullable', 'string', 'max:64'],
            'shipping_city_name' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('shipping_method'), [
                    'nova_poshta_warehouse',
                    ...$manualShipping,
                ], true)),
                'nullable',
                'string',
                'max:255',
            ],
            'shipping_warehouse_ref' => ['required_if:shipping_method,nova_poshta_warehouse', 'nullable', 'string', 'max:64'],
            'shipping_warehouse_name' => ['required_if:shipping_method,nova_poshta_warehouse', 'nullable', 'string', 'max:500'],
            'shipping_address' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('shipping_method'), $manualShipping, true)),
                'nullable',
                'string',
                'max:1000',
            ],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'agree' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Вкажіть імʼя та прізвище.',
            'customer_phone.required' => 'Вкажіть номер телефону.',
            'customer_phone.regex' => 'Невірний формат телефону.',
            'shipping_city_name.required' => 'Вкажіть місто доставки.',
            'shipping_city_ref.required_if' => 'Оберіть місто зі списку Нової Пошти.',
            'shipping_warehouse_ref.required_if' => 'Оберіть відділення Нової Пошти.',
            'shipping_address.required' => 'Вкажіть адресу доставки.',
            'agree.accepted' => 'Потрібна згода з умовами оформлення.',
        ];
    }
}
