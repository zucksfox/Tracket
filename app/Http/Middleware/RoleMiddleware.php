<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!empty($roles) && !in_array($user->role, $roles)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk membuka modul ini.');
        }

        return $next($request);
    }
}
