<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class CashierOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && (Auth::user()->isCashier() || Auth::user()->isAdmin())) {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Halaman ini hanya untuk Kasir.');
    }
}
