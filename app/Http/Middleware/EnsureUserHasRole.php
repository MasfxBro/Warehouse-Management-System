<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: EnsureUserHasRole
 * 
 * Memvalidasi bahwa user yang sedang login memiliki role yang sesuai.
 * Role di-pass sebagai parameter middleware.
 * 
 * Usage:
 * - Route::get('/admin', ...)->middleware('role:admin');
 * - Route::get('/manager', ...)->middleware('role:admin,manager');
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = auth()->user();

        // Jika tidak ada role yang di-specify, izinkan (hanya butuh authenticated)
        if (empty($roles)) {
            return $next($request);
        }

        // Convert string role ke array jika perlu
        $allowedRoles = is_array($roles) ? $roles : [$roles];

        // Cek apakah user memiliki salah satu role yang diizinkan
        foreach ($allowedRoles as $role) {
            // Handle UserRole enum atau string
            if ($user->role->value === $role || $user->role->name === $role) {
                return $next($request);
            }
        }

        // User tidak memiliki akses
        abort(403, 'Anda tidak memiliki akses ke halaman ini. Role Anda: ' . $user->role->label());
    }
}
