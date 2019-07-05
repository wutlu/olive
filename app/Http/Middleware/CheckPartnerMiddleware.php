<?php

namespace App\Http\Middleware;

use Closure;

class CheckPartnerMiddleware
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
        if (auth()->check())
        {
            if (!auth()->user()->partner)
            {
                return $request->expectsJson()
                    ? response()->json([
                        'view' => view('user.permission')->render()
                    ], 403)
                    : redirect()->route('dashboard');
            }
        }
        else
        {
            return $request->expectsJson()
                    ? response()->json([
                        'redirect' => route('user.login')
                    ], 401)
                    : redirect()->guest(route('user.login'));
        }

        return $next($request);
    }
}
