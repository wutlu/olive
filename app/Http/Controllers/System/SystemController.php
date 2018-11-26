<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\SetRequest;

use App\Models\Option;

use App\Http\Controllers\MonitorController;

class SystemController extends Controller
{
    # set
    public static function set(SetRequest $request)
    {
        Option::updateOrCreate(
            [
                'key' => $request->key
            ],
            [
                'value' => $request->value
            ]
        );

        return [
            'status' => 'ok'
        ];
    }

    # alarm
    public static function alarmControl()
    {
        MonitorController::alarmControl();
    }
}