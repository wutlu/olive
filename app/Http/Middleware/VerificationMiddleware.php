<?php

namespace App\Http\Middleware;

use Closure;

class VerificationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->user()->verified)
        {
            return $request->expectsJson()
                ? response()->json([
                    'verification' => 'email'
                ])
                : redirect()->route('dashboard');
        }

        return $next($request);
    }
}
