<?php

namespace App\Enums;

/**
 * Enum untuk mendefinisikan peran (role) pengguna dalam sistem WMS.
 * Digunakan pada kolom `role` di tabel `users`.
 */
enum UserRole: string
{
    case Admin    = 'admin';
    case Manager  = 'manager';
    case Operator = 'operator';

    /**
     * Mengembalikan label yang human-readable untuk setiap role.
     */
    public function label(): string
    {
        return match($this) {
            UserRole::Admin    => 'Administrator',
            UserRole::Manager  => 'Manager',
            UserRole::Operator => 'Operator',
        };
    }

    /**
     * Mengembalikan semua nilai role sebagai array string.
     * Berguna untuk validasi dan seeder.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
