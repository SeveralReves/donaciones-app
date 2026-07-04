<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cubre el caso que el login por sí solo no puede: un usuario ya logueado al
 * que un admin desactiva mientras su sesión sigue viva. Corre en cada
 * request (registrado en bootstrap/app.php) en vez de solo en rutas 'auth',
 * así la sesión se cierra en el siguiente request sin importar qué ruta
 * pida. El rechazo en el login mismo (credenciales correctas pero cuenta
 * desactivada) vive en LoginRequest::authenticate().
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->active) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta ha sido desactivada, contacta al administrador.',
            ]);
        }

        return $next($request);
    }
}
