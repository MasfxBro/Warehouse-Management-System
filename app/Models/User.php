<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model: User
 *
 * Model bawaan Laravel yang diperluas dengan kolom `role` menggunakan Enum UserRole.
 * User memiliki relasi ke transaksi inbound dan outbound yang diproses.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string      $password
 * @property UserRole    $role
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi secara mass assignment.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi (API response / toArray).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast tipe data kolom.
     * Role di-cast ke Enum UserRole agar dapat digunakan sebagai objek Enum di kode.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
        ];
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    /**
     * Cek apakah user memiliki role admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Cek apakah user memiliki role manager.
     */
    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    /**
     * Cek apakah user memiliki role operator.
     */
    public function isOperator(): bool
    {
        return $this->role === UserRole::Operator;
    }

    // =========================================================
    // RELASI
    // =========================================================

    /**
     * Satu user bisa memproses banyak transaksi inbound.
     */
    public function inboundTransactions(): HasMany
    {
        return $this->hasMany(InboundTransaction::class, 'User_ID', 'id');
    }

    /**
     * Satu user bisa memproses banyak transaksi outbound.
     */
    public function outboundTransactions(): HasMany
    {
        return $this->hasMany(OutboundTransaction::class, 'User_ID', 'id');
    }
}
