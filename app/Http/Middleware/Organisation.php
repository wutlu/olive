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
    public function handle($request, Closure $next, $type, $modules = '')
    {
        if ($type == 'have_not')
        {
            if (auth()->user()->organisation_id)
            {
                session()->flash('organisation', 'have');
                session()->flash('alert', 'Zaten bir organizasyona dahilsiniz.');

                return $request->expectsJson()
                    ? response()->json([
                        'status' => 'warn',
                        'errors' => [
                            'global' => [ session('alert') ]
                        ],
                        'organisation' => 'have'
                    ], 422)
                    : redirect()->route('alert');
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
                if ($modules)
                {
                    $modules = explode('|', $modules);

                    $organisation = auth()->user()->organisation;
                    $invoice      = $organisation->lastInvoice;
                    $plan         = $invoice->plan();

                    $names = [];

                    foreach ($modules as $module)
                    {
                        if (!$plan->properties->{$module}->value)
                        {
                            $names[] = $plan->properties->{$module}->text;
                        }
                    }

                    if (count($names))
                    {
                        session()->flash('alert', 'Bu işlemi yapmak için gerekli plana sahip değilsiniz. Plan gereksinimleri: '.implode(',', $names));

                        return $request->expectsJson() ?
                            response()->json([
                                'status' => 'warn',
                                'errors' => [
                                    'global' => [ session('alert') ]
                                ],
                                'organisation' => 'have_not'
                            ], 422)
                            : redirect()->route('alert');
                    }
                }

                return $next($request);
            }
            else
            {
                session()->flash('alert', 'Bu modülü kullanabilmek için bir organizasyona dahil olmanız gerekiyor.');

                return $request->expectsJson() ?
                    response()->json([
                        'status' => 'warn',
                        'errors' => [
                            'global' => [ session('alert') ]
                        ],
                        'organisation' => 'have_not'
                    ], 422)
                    : redirect()->route('alert');
            }
        }
        else
        {
            return $next($request);
        }
    }
}
