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
                        'status' => 'warn',
                        'errors' => [
                            'global' => [ 'Zaten bir organizasyona dahilsiniz.' ]
                        ],
                        'organisation' => 'have'
                    ], 422)
                    : redirect()->route('settings.organisation');
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
                        'status' => 'warn',
                        'errors' => [
                            'global' => [ 'Henüz bir organizasyona dahil değilsiniz.' ]
                        ],
                        'organisation' => 'have_not'
                    ], 422)
                    : redirect()->route('organisation.create.select');
            }
        }
        else
        {
            return $next($request);
        }
    }
}
