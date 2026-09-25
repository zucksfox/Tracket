<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melarang peramban menyimpan halaman backoffice di cache.
 *
 * Tanpa header ini, tombol Back setelah logout masih menampilkan data servis
 * dan pelanggan dari cache peramban.
 */
class NoStoreAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
