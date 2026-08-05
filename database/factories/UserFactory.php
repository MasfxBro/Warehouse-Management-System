<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory: UserFactory
 *
 * Menghasilkan data dummy untuk tabel users.
 * Diperluas dari bawaan Laravel dengan penambahan kolom `role`.
 * Default role adalah 'operator' sesuai prinsip least privilege.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Password default untuk semua user dummy — di-hash sekali untuk efisiensi.
     */
    protected static ?string $password;

    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => UserRole::Operator->value,
            'remember_token'    => Str::random(10),
        ];
    }

    // =========================================================
    // STATES
    // =========================================================

    /**
     * State: user dengan role admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin->value,
        ]);
    }

    /**
     * State: user dengan role manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Manager->value,
        ]);
    }

    /**
     * State: user dengan role operator.
     */
    public function operator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Operator->value,
        ]);
    }

    /**
     * State: email belum diverifikasi.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
