<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: NoPrefetch
 *
 * Menambahkan HTTP response header untuk mencegah browser (Chrome, Edge)
 * melakukan speculative prefetching pada halaman-halaman app.
 * Ini mencegah request duplikat setiap detik yang muncul di request log
 * ketika user hover di atas sidebar links atau browser idle prefetch aktif.
 */
class NoPrefetch
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika request ini adalah hasil prefetch dari browser, tolak langsung
        // Chrome mengirim header "Purpose: prefetch" atau "Sec-Purpose: prefetch"
        $purpose = $request->header('Purpose') ?? $request->header('Sec-Purpose') ?? '';
        if (str_contains(strtolower($purpose), 'prefetch')) {
            return response('', 204); // No Content — jangan proses request
        }

        $response = $next($request);

        // Header standar untuk disable caching dan prefetch
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');

        return $response;
    }
}
