<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarUsuarioActivo
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && ! Auth::user()->activo) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tu cuenta está desactivada.'], 403);
            }
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta está desactivada.']);
        }

        return $next($request);
    }
}