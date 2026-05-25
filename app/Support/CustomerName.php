<?php

namespace App\Support;

final class CustomerName
{
    /** @return array{first: string, last: string, full: string} */
    public static function split(string $name): array
    {
        $name = trim($name);
        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return ['first' => 'Клієнт', 'last' => 'NOEMA', 'full' => 'Клієнт NOEMA'];
        }

        if (count($parts) === 1) {
            return ['first' => $parts[0], 'last' => $parts[0], 'full' => $parts[0]];
        }

        $first = array_shift($parts);
        $last = implode(' ', $parts);

        return ['first' => $first, 'last' => $last, 'full' => trim("{$first} {$last}")];
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '380')) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '38'.$digits;
        }

        if (strlen($digits) === 9) {
            return '380'.$digits;
        }

        return $digits;
    }
}
