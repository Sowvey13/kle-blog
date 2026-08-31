<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('user_token') || blank(session('user_token'))) {
            return redirect()->route('auth.required');
        }

        return $next($request);
    }
}
