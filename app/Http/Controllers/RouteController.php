<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Route;
use Validator;

class RouteController extends Controller
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'has_route' => 'Rota bulunamadı.'
        ];
    }

    /**
     * Link Yarat
     *
     * @return array
     */
    public static function generateById(Request $request)
    {
        Validator::extend('has_route', function($attribute, $value) use ($request) {
            return Route::has($value) ? true : false;
        });

        $request->validate([
            'name' => 'required|string|max:64|has_route',
            'id' => 'required|integer|max:10000000'
        ]);

    	return [
            'status' => 'ok',
            'route' => route($request->name, $request->id)
        ];
    }
}
