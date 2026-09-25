<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi rute berdasarkan peran pengguna (admin, cashier, technician).
 *
 * Daftar peran diterima sebagai parameter variadic, sehingga satu rute dapat
 * ditulis `middleware('role:admin,cashier')` tanpa membuat kelas middleware
 * baru untuk setiap kombinasi peran.
 */
class RoleMiddleware
{
    /**
     * @param string ...$roles Peran yang diizinkan; kosong berarti semua peran.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (! empty($roles) && ! in_array($user->role, $roles)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk membuka modul ini.');
        }

        return $next($request);
    }
}
