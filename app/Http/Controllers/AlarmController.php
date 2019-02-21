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
        $data = new Alarm;
        $data->organisation_id = auth()->user()->organisation_id;
        $data->name = $request->name;
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
