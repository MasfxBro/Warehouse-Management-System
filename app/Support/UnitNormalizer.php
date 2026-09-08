<?php

namespace App\Support;

use Illuminate\Support\Str;

class UnitNormalizer
{
    /**
     * Singkatan satuan yang secara umum ditulis dengan huruf kapital.
     * Istilah lain tetap boleh digunakan dan akan disimpan dalam Title Case.
     */
    private const ACRONYMS = [
        'cc', 'cm', 'dus', 'gr', 'kg', 'km', 'l', 'm', 'ml', 'mm', 'pcs', 'rim',
    ];

    public static function normalize(?string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) $value));

        if ($value === '') {
            return 'PCS';
        }

        return collect(explode(' ', Str::lower($value)))
            ->map(fn (string $word) => in_array($word, self::ACRONYMS, true)
                ? Str::upper($word)
                : Str::ucfirst($word))
            ->implode(' ');
    }
}
