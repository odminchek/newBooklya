<?php

namespace App\Http\Middleware;

use Closure;

class JsonMiddleware
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
