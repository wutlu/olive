<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\UserActivity;

use App\Http\Requests\PlanRequest;

class OrganisationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    # başla
    public static function start(int $step = 1, PlanRequest $request)
    {
        if ($request->plan)
        {
            session()->flash('plan', $request->plan);

            return redirect()->route('start', [ 'step' => $step ]);
        }
        else
        {
            if ($step != 1 && !session('plan'))
            {
                session()->flash('timeout', true);

                return redirect()->route('start');
            }
            else
            {
                return view('start', compact('step'));
            }
        }
    }
}
