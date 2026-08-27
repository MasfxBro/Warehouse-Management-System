<?php

namespace App\Enums;

/**
 * Enum untuk mendefinisikan peran (role) pengguna dalam sistem WMS Prototipe 2.
 * HANYA ada 2 role:
 * - admin (Guru)
 * - user (Operator / Siswa)
 */
enum UserRole: string
{
    case Admin = 'admin';
    case User  = 'user';

    /**
     * Mengembalikan label yang human-readable untuk setiap role.
     */
    public function label(): string
    {
        return match($this) {
            UserRole::Admin => 'Guru (Admin)',
            UserRole::User  => 'Operator (Siswa)',
        };
    }

    /**
     * Mengembalikan semua nilai role sebagai array string.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

