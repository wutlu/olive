<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\Alarm\CreateRequest;
use App\Http\Requests\Alarm\UpdateRequest;

use App\Models\Alarm;
use App\Models\User\User;

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
        $this->middleware([ 'auth', 'organisation:have' ]);
        $this->middleware([ 'can:organisation-status' ])->except([ 'dashboard', 'data', 'get', 'delete' ]);
        $this->middleware('organisation:have,module_alarm')->only([
            'create',
            'update'
        ]);
    }

    /**
     * Alarmlar Ana Sayfa
     *
     * @return view
     */
    public function dashboard()
    {
        $members = User::where('organisation_id', auth()->user()->organisation_id)->where('verified', true)->get();

        return view('alarm', compact('members'));
    }

    /**
     * Alarmlar Data
     *
     * @return view
     */
    public function data()
    {
        $query = Alarm::where('organisation_id', auth()->user()->organisation_id)->orderBy('id', 'ASC')->get();

        return [
            'status' => 'ok',
            'hits' => $query,
            'total' => count($query)
        ];
    }

    /**
     * Alarm Bilgisi
     *
     * @return array
     */
    public function get(IdRequest $request)
    {
        $data = Alarm::where([ 'id' => $request->id, 'organisation_id' => auth()->user()->organisation_id ])->firstOrFail();

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

        $data->fill($request->all());

        $data->query = $request->text;
        $data->modules = $request->sources;
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

        $data->fill($request->all());
        $data->query = $request->text;
        $data->modules = $request->sources;
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
