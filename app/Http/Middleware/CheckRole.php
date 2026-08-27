<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role->value ?? null;

        if (in_array($userRole, $roles, true)) {
            return $next($request);
        }

        // Jika tidak berhak, redirect ke dashboard dengan notice
        return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki hak untuk mengakses halaman ini.');
    }
}
