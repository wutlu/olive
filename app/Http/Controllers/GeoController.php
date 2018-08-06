<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\GEO\Countries;
use App\Models\GEO\States;

use App\Http\Requests\GEO\StatesRequest;

class GeoController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}

    # ülkeler
    public static function countries()
    {
        return [
            'status' => 'ok',
            'data' => Countries::select('name', 'id')->get()
        ];
    }

    # şehirler
    public static function states(StatesRequest $request)
    {
        return [
            'status' => 'ok',
            'data' => States::select('name', 'id')->where('country_id', $request->country_id)->get()
        ];
    }
}
