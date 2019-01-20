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
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
		$this->middleware('auth');
	}

    /**
     * Ülkeler
     *
     * @return array
     */
    public static function countries()
    {
        return [
            'status' => 'ok',
            'data' => Countries::select('name', 'id')->orderBy('name', 'ASC')->get()
        ];
    }

    /**
     * Şehirler
     *
     * @return array
     */
    public static function states(StatesRequest $request)
    {
        return [
            'status' => 'ok',
            'data' => States::select('name', 'id')->where('country_id', $request->country_id)->orderBy('name', 'ASC')->get()
        ];
    }
}
