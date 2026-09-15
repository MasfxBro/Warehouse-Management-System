<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireApiStudentIdentity
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->attributes->get('api_token');
        if ($request->user()?->isUser() && ! $token?->hasStudentIdentity()) {
            return response()->json([
                'success' => false,
                'code' => 'STUDENT_IDENTITY_REQUIRED',
                'message' => 'Isi identitas siswa sebelum menjalankan transaksi.',
            ], 403);
        }

        return $next($request);
    }
}
