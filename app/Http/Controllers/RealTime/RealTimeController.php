<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RealTime\PinGroup;

class RealTimeController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
    }

    # 
    # gerçek zamanlı ekranı.
    # 
    public function dashboard(int $id = 0)
    {
        $organisation = auth()->user()->organisation;

        if ($id)
        {
            $pin_group = PinGroup::where([
                'id' => $id,
                'organisation_id' => $organisation->id
            ])->firstOrFail();
        }
        else
        {
            $pin_group = null;
        }

    	return view('real-time.dashboard', compact('id', 'pin_group'));
    }
}
