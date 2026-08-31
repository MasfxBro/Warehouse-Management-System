<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * API Controller: Autentikasi
 *
 * Menangani login dan logout untuk aplikasi Flutter via Sanctum Token.
 * - POST /api/login  → Kembalikan token + data user
 * - POST /api/logout → Hapus token aktif (requires auth:sanctum)
 */
class AuthApiController extends Controller
{
    /**
     * Login — verifikasi kredensial dan kembalikan Sanctum token.
     *
     * Request JSON:
     * {
     *   "login":    "admin" | "siswa" | "user@email.com",
     *   "password": "password"
     * }
     *
     * Response JSON (200):
     * {
     *   "success": true,
     *   "token":   "1|abc...",
     *   "user": {
     *     "id": 1, "name": "...", "email": "...", "role": "admin"
     *   }
     * }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->login);

        // Dukung shorthand "admin" / "siswa" seperti di web
        $email = $loginInput;
        if (!filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            if (strtolower($loginInput) === 'admin') {
                $email = 'admin@wms.local';
            } elseif (strtolower($loginInput) === 'siswa') {
                $email = 'siswa@wms.local';
            }
        }

        if (!Auth::attempt(['email' => $email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'login' => ['Kredensial login tidak cocok.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Hapus token lama (optional — 1 device 1 token)
        $user->tokens()->delete();

        // Buat token baru dengan nama perangkat
        $token = $user->createToken('wms-flutter')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role->value,
                'role_label' => $user->role->label(),
            ],
        ]);
    }

    /**
     * Logout — hapus token aktif yang digunakan saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        // Hapus token yang sedang dipakai (token yang dikirim via header)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * Me — ambil data user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role->value,
                'role_label' => $user->role->label(),
            ],
        ]);
    }
}
