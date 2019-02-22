<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\Alarm\CreateRequest;
use App\Http\Requests\Alarm\UpdateRequest;

use App\Models\Alarm;

class AlarmController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         * - Organizasyon
         */
        $this->middleware([ 'auth', 'organisation:have', 'can:organisation-status' ]);
    }

    /**
     * Alarmlar Ana Sayfa
     *
     * @return view
     */
    public function dashboard()
    {
        return view('alarm.dashboard');
    }

    /**
     * Alarmlar Data
     *
     * @return view
     */
    public function data()
    {
        $query = Alarm::where('organisation_id', auth()->user()->organisation_id)->orderBy('hit', 'ASC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    /**
     * Alarm Bilgisi
     *
     * @return array
     */
    public function get(IdRequest $request)
    {
        $data = Alarm::where([
            'id' => $request->id,
            'organisation_id' => auth()->user()->organisation_id
        ])->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     * Alarm Oluştur
     *
     * @return array
     */
    public function create(CreateRequest $request)
    {
        $weekdays = [];

        if ($request->day_1) $weekdays['day_1'] = 'on';
        if ($request->day_2) $weekdays['day_2'] = 'on';
        if ($request->day_3) $weekdays['day_3'] = 'on';
        if ($request->day_4) $weekdays['day_4'] = 'on';
        if ($request->day_5) $weekdays['day_5'] = 'on';
        if ($request->day_6) $weekdays['day_6'] = 'on';
        if ($request->day_7) $weekdays['day_7'] = 'on';

        $modules = [];

        foreach (config('system.modules') as $key => $module)
        {
            if ($request->{implode('_', [ 'module', $key ])}) $modules[$key] = 'on';
        }

        $emails = [];

        foreach (explode(PHP_EOL, $request->emails) as $email)
        {
            $emails[$email] = $email;
        }

        $data = new Alarm;
        $data->name = $request->name;
        $data->query = $request->text;

        $data->hit = $request->hit;
        $data->interval = $request->interval;

        $data->start_time = $request->start_time;
        $data->end_time = $request->end_time;

        $data->weekdays = $weekdays;
        $data->modules = $modules;
        $data->emails = $emails;

        $data->organisation_id = auth()->user()->organisation_id;
        $data->save();

        return [
            'status' => 'ok',
            'type' => 'created'
        ];
    }

    /**
     * Alarm Güncelle
     *
     * @return array
     */
    public function update(UpdateRequest $request)
    {
        $data = Alarm::where([
            'id' => $request->id,
            'organisation_id' => auth()->user()->organisation_id
        ])->firstOrFail();

        $data->name = $request->name;
        $data->save();

        return [
            'status' => 'ok',
            'type' => 'updated',
            'data' => [
                'id' => $data->id,
                'name' => $data->name
            ]
        ];
    }

    /**
     * Alarm Sil
     *
     * @return array
     */
    public function delete(IdRequest $request)
    {
        $data = Alarm::where([
            'id' => $request->id,
            'organisation_id' => auth()->user()->organisation_id
        ])->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $data->id
            ]
        ];

        $data->delete();

        return $arr;
    }
}
