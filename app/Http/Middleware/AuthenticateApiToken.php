<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();
        $token = $plainToken
            ? ApiToken::with('user')->where('token_hash', hash('sha256', $plainToken))->first()
            : null;

        if (! $token || ($token->expires_at && $token->expires_at->isPast())) {
            return response()->json(['success' => false, 'message' => 'Sesi habis. Silakan login ulang.'], 401);
        }

        $request->setUserResolver(fn () => $token->user);
        $request->attributes->set('api_token', $token);
        Auth::setUser($token->user);
        if ($token->hasStudentIdentity()) {
            session(['student_identity' => [
                'name' => $token->student_name,
                'class' => $token->student_class,
                'nis' => $token->student_nis,
            ]]);
        }
        $token->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }
}
