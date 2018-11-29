<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RealTime\Group;

use App\Http\Requests\RealTime\Group\CreateRequest as GroupCreateRequest;

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

        $data = Group::select('name', 'id')->where('organisation_id', $organisation->id)->get();

        return [
            'status' => 'ok',
            'hits' => $data,
            'limit' => $organisation->capacity
        ];
    }

    # 
    # grup bilgileri
    # 
    public function groupGet()
    {
        return [
            'status' => 'ok'
        ];
    }

    # 
    # grup oluştur
    # 
    public function groupCreate(GroupCreateRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = new Group;
        $data->organisation_id = $organisation->id;
        $data->name = $request->name;
        $data->module_youtube = $request->module_youtube ? true : false;
        $data->module_twitter = $request->module_twitter ? true : false;
        $data->module_sozluk = $request->module_sozluk ? true : false;
        $data->module_news = $request->module_news ? true : false;
        $data->module_shopping = $request->module_shopping ? true : false;
        $data->save();

        return [
            'status' => 'ok'
        ];
    }

    # 
    # grup güncelle
    # 
    public function groupUpdate()
    {
        return [
            'status' => 'ok'
        ];
    }

    # 
    # grup sil
    # 
    public function groupDelete()
    {
        return [
            'status' => 'ok'
        ];
    }
}
