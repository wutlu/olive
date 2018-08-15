<?php

namespace App\Http\Middleware;

use Closure;

class CheckRootMiddleware
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
            if (!auth()->user()->root())
            {
                return redirect()->route('dashboard');
            }
        }
        else
        {
            return redirect()->route('user.login');
        }

        return $next($request);
    }
}
