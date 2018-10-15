<?php

namespace App\Http\Middleware;

use Closure;

class Twitter
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
            if (auth()->user()->organisation->twitterAccount)
            {
                session()->flash('twitter', 'have');
                session()->flash('alert', 'Zaten bir Twitter hesabı tanımladınız.');

                return $request->expectsJson()
                    ? response()->json([
                        'status' => 'warn',
                        'errors' => [
                            'global' => [ session('alert') ]
                        ],
                        'twitter' => 'have'
                    ], 422)
                    : redirect()->route('twitter.connect');
            }
            else
            {
                return $next($request);
            }
        }
        elseif ($type == 'have')
        {
            if (auth()->user()->organisation->twitterAccount)
            {
                return $next($request);
            }
            else
            {
                session()->flash('twitter', 'have_not');
                session()->flash('alert', 'Lütfen bir Twitter hesabı tanımlayın.');

                return $request->expectsJson()
                    ? response()->json([
                        'status' => 'warn',
                        'errors' => [
                            'global' => [ session('alert') ]
                        ],
                        'twitter' => 'have_not'
                    ], 422)
                    : redirect()->route('twitter.connect');
            }
        }
        else
        {
            return $next($request);
        }
    }
}
