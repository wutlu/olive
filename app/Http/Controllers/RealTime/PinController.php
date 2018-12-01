<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;

use App\Http\Requests\RealTime\PinGroup\CreateRequest as GroupCreateRequest;
use App\Http\Requests\RealTime\PinGroup\UpdateRequest as GroupUpdateRequest;

use App\Models\RealTime\PinGroup;

class PinController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
    }

    # 
    # pinler (json)
    # 
    public function groups(SearchRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $take = $request->take;
        $skip = $request->skip;

        $query = PinGroup::with('pins');
        $query = $query->where('organisation_id', $organisation->id);
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # 
    # grup bilgileri
    # 
    public function groupGet(IdRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = PinGroup::select(
            'id',
            'name'
        )->where([
            'id' => $request->id,
            'organisation_id' => $organisation->id
        ])->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    # 
    # grup oluştur
    # 
    public function groupCreate(GroupCreateRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = new PinGroup;

        $data->organisation_id = $organisation->id;

        $data->name = $request->name;
        $data->save();

        return [
            'status' => 'ok',
            'type' => 'created'
        ];
    }

    # 
    # grup güncelle
    # 
    public function groupUpdate(GroupUpdateRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = PinGroup::where([
            'id' => $request->id,
            'organisation_id' => $organisation->id
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

    # 
    # grup sil
    # 
    public function groupDelete(IdRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = PinGroup::where([
            'id' => $request->id,
            'organisation_id' => $organisation->id
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
