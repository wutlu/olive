<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RealTime\KeywordGroup;

use App\Http\Requests\RealTime\KeywordGroup\CreateRequest as GroupCreateRequest;
use App\Http\Requests\RealTime\KeywordGroup\UpdateRequest as GroupUpdateRequest;
use App\Http\Requests\IdRequest;

class KeywordController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
    }

    # 
    # kelime grupları
    # 
    public function groups()
    {
        $organisation = auth()->user()->organisation;

        $data = KeywordGroup::select('name', 'id')->where('organisation_id', $organisation->id)->orderBy('id', 'DESC')->get();

        return [
            'status' => 'ok',
            'hits' => $data,
            'limit' => $organisation->capacity
        ];
    }

    # 
    # grup bilgileri
    # 
    public function groupGet(IdRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = KeywordGroup::select(
            'id',
            'name',
            'keywords',
            'module_youtube',
            'module_twitter',
            'module_sozluk',
            'module_news',
            'module_shopping'
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

        $data = new KeywordGroup;

        $data->organisation_id = $organisation->id;

        $data->name = $request->name;
        $data->keywords = trim($request->keywords);

        $data->module_youtube = $request->module_youtube ? true : false;
        $data->module_twitter = $request->module_twitter ? true : false;
        $data->module_sozluk = $request->module_sozluk ? true : false;
        $data->module_news = $request->module_news ? true : false;
        $data->module_shopping = $request->module_shopping ? true : false;
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

        $data = KeywordGroup::where([
            'id' => $request->id,
            'organisation_id' => $organisation->id
        ])->firstOrFail();

        $data->name = $request->name;
        $data->keywords = trim($request->keywords);

        $data->module_youtube = $request->module_youtube ? true : false;
        $data->module_twitter = $request->module_twitter ? true : false;
        $data->module_sozluk = $request->module_sozluk ? true : false;
        $data->module_news = $request->module_news ? true : false;
        $data->module_shopping = $request->module_shopping ? true : false;
        $data->save();

        return [
            'status' => 'ok',
            'type' => 'updated'
        ];
    }

    # 
    # grup sil
    # 
    public function groupDelete(IdRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = KeywordGroup::where([
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