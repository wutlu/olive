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
        ### [ üyelik ve organizasyon zorunlu ] ###
        $this->middleware([ 'auth', 'organisation:have' ]);

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware('can:organisation-status')->only([
            'groupCreate'
        ]);
    }

    /**
     * Kelime Grupları
     *
     * @return array
     */
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

    /**
     * Kelime Grubu, detayları.
     *
     * @return array
     */
    public function groupGet(IdRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = KeywordGroup::select(
            'id',
            'name',
            'keywords',
            'module_youtube_video',
            'module_youtube_comment',
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

    /**
     * Kelime Grubu, oluştur.
     *
     * @return array
     */
    public function groupCreate(GroupCreateRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = new KeywordGroup;

        $data->organisation_id = $organisation->id;

        $data->name = $request->name;
        $data->keywords = trim($request->keywords);

        $data->module_youtube_video = $request->module_youtube_video ? true : false;
        $data->module_youtube_comment = $request->module_youtube_comment ? true : false;
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

    /**
     * Kelime Grubu, güncelle.
     *
     * @return array
     */
    public function groupUpdate(GroupUpdateRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = KeywordGroup::where(
            [
                'id' => $request->id,
                'organisation_id' => $organisation->id
            ]
        )->firstOrFail();

        $data->name = $request->name;
        $data->keywords = trim($request->keywords);

        $data->module_youtube_video = $request->module_youtube_video ? true : false;
        $data->module_youtube_comment = $request->module_youtube_comment ? true : false;
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

    /**
     * Kelime Grubu, sil.
     *
     * @return array
     */
    public function groupDelete(IdRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = KeywordGroup::where(
            [
                'id' => $request->id,
                'organisation_id' => $organisation->id
            ]
        )->firstOrFail();

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
