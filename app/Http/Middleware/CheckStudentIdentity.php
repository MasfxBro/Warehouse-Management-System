<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentIdentity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isUser() && ! session()->has('student_identity')) {
            // Request baca tetap diizinkan agar modal identitas dapat tampil.
            view()->share('require_student_identity_modal', true);

            // Tolak perubahan data sampai identitas siswa dicatat. Pengecualian
            // hanya untuk mengisi identitas, reset sesi, dan logout.
            $allowedRoutes = [
                'student-identity.store',
                'student-identity.reset',
                'logout',
            ];

            if (! $request->isMethodSafe() && ! $request->routeIs($allowedRoutes)) {
                return redirect()->route('dashboard')
                    ->with('error', 'Isi identitas siswa terlebih dahulu sebelum menjalankan transaksi.');
            }
        }

        return $next($request);
    }
}
