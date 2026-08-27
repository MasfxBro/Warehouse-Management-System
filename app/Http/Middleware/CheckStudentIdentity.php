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

        // Jika user logged in dan role nya adalah 'user' (Siswa)
        if ($user && $user->isUser()) {
            if (!session()->has('student_identity')) {
                // Tandai request bahwa modal identitas wajib aktif
                view()->share('require_student_identity_modal', true);
            }
        }

        return $next($request);
    }
}
