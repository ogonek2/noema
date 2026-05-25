<?php

namespace App\Support;

final class EnumOptions
{
    /** @param  class-string<\BackedEnum>  $enumClass */
    public static function map(string $enumClass, string $labelMethod = 'label'): array
    {
        return collect($enumClass::cases())
            ->mapWithKeys(fn (\BackedEnum $case): array => [
                $case->value => method_exists($case, $labelMethod) ? $case->{$labelMethod}() : $case->name,
            ])
            ->all();
    }
}
