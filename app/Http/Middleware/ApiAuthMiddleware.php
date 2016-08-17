<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        // проверка авторизации для API
        return $next($request);
    }
}
