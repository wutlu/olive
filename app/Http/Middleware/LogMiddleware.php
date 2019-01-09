<?php

namespace App\Http\Middleware;

use Closure;
use Session;

use App\Models\Session as SessionTable;

class LogMiddleware
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
        $session = SessionTable::find(Session::getId());
        $session->ping = 1;
        $session->save();

        return $next($request);
    }
}
