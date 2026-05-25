<?php

return [
    'shipping_cost' => (float) env('CHECKOUT_SHIPPING_COST', 0),

    'iban' => [
        'recipient' => env('CHECKOUT_IBAN_RECIPIENT', ''),
        'iban' => env('CHECKOUT_IBAN_NUMBER', ''),
        'bank' => env('CHECKOUT_IBAN_BANK', ''),
        'purpose' => env('CHECKOUT_IBAN_PURPOSE', 'Оплата замовлення'),
    ],
];
