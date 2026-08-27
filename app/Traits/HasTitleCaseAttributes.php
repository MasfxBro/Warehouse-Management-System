<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasTitleCaseAttributes
{
    /**
     * Konversi string menjadi format Title Case (Huruf Kapital di Setiap Awal Kata).
     * Contoh: "jl. pahlawan raya no 12" -> "Jl. Pahlawan Raya No 12"
     */
    public static function formatTitleCase(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        return Str::title(trim($value));
    }
}
