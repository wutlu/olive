<?php

namespace App\Http\Middleware;

use Closure;

class Organisation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $type)
    {
        if ($type == 'have_not')
        {
            if (auth()->user()->organisation_id)
            {
                session()->flash('organisation', 'have');

                return $request->expectsJson()
                    ? response()->json([
                        'status' => 'ok',
                        'organisation' => 'have'
                    ])
                    : redirect()->route('organisation.settings');
            }
            else
            {
                return $next($request);
            }
        }
        elseif ($type == 'have')
        {
            if (auth()->user()->organisation_id)
            {
                return $next($request);
            }
            else
            {
                return $request->expectsJson()
                    ? response()->json([
                        'status' => 'ok',
                        'organisation' => 'have_not'
                    ])
                    : redirect()->route('organisation.create.select');
            }
        }
        else
        {
            return $next($request);
        }
    }
}
