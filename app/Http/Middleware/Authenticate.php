<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // ⚠️ Importante para API con JWT:
        // Si la petición espera JSON, no redirige, solo devuelve 401
        if (! $request->expectsJson()) {
            return route('login');
        }

        return null;
    }
}
