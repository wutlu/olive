<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Analysis;

use App\Http\Requests\Analysis\SearchRequest;
use App\Http\Requests\Analysis\CreateRequest;
use App\Http\Requests\Analysis\CompileRequest;
use App\Http\Requests\Analysis\TestRequest;
use App\Http\Requests\Analysis\MoveRequest;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SetRequest;

use Sentiment;
use System;
use Term;
use App\Models\Option;

class AnalysisController extends Controller
{
    /**
     * Analiz, Ana Sayfa
     *
     * @return view
     */
    public function dashboard()
    {
        $learn = System::option('data.learn');

        $data = [];
        $modules = array_map(function($arr) {
            if (@$arr['ignore'])
            {
                $arr['types'][$arr['ignore']] = [
                    'title' => 'Analiz Dışı',
                    'per' => 0
                ];
            }

            return $arr;
        }, config('system.analysis'));

        foreach ($modules as $key => $item)
        {
            foreach ($item['types'] as $tkey => $type)
            {
                $data[$tkey] = [
                    'new' => Analysis::where('group', $tkey)->where('compiled', false)->count(),
                    'compiled' => Analysis::where('group', $tkey)->where('compiled', true)->count()
                ];
            }
        }

        return view('analysis.dashboard', compact('data', 'modules', 'learn'));
    }

    /**
     * Analiz, Beyin
     *
     * @return view
     */
    public function module(string $module)
    {
        $module_name = $module;
        $module = config('system.analysis')[$module];

        if (@$module['ignore'])
        {
            $module['types'][$module['ignore']] = [
                'title' => 'Analiz Dışı',
                'per' => 0
            ];
        }

        return view('analysis.module', compact('module_name', 'module'));
    }

    /**
     * Analiz, Beyin Verileri
     *
     * @return array
     */
    public function words(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new Analysis;

        $query = $query->where('module', $request->module);
        $query = $query->where('group', $request->group);

        $query = $request->string ? $query->where('word', 'ILIKE', '%'.$request->string.'%') : $query;

        $total = $query->count();

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('compiled', 'ASC')
                       ->orderBy('updated_at', 'DESC')
                       ->orderBy('learned', 'DESC')
                       ->get();

        return [
            'status' => 'ok',
            'hits' => $query,
            'total' => $total
        ];
    }

    /**
     * Analiz, Kelime Taşı
     *
     * @return array
     */
    public function move(MoveRequest $request)
    {
        $word = Analysis::where('id', $request->id)->first();

        if (@$word)
        {
            $word->group = $request->group;
            $word->compiled = 0;
            $word->save();
        }
        else
        {
            return [
                'status' => 'err',
                'reason' => 'Kelimeye ulaşılamıyor, silinmiş olabilir.'
            ];
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Analiz, Kelime Sil
     *
     * @return array
     */
    public static function delete(IdRequest $request)
    {
        $keyword = Analysis::where('id', $request->id)->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $keyword->id
            ]
        ];

        $keyword->delete();

        return $arr;
    }

    /**
     * Analiz, Kelime Oluştur
     *
     * @return array
     */
    public static function create(CreateRequest $request)
    {
        $query = new Analysis;
        $query->word = $request->string;
        $query->module = $request->module;
        $query->group = $request->group;
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Analiz, Derle
     *
     * @return array
     */
    public static function compile(CompileRequest $request)
    {
        $sentiment = new Sentiment;
        $sentiment->classes = [ $request->group ];
        $sentiment->update();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Analiz, Test
     *
     * @return array
     */
    public static function test(TestRequest $request)
    {
        $sentiment = new Sentiment;
        $sentiment->engine($request->engine);

        $data = [];

        foreach (explode(PHP_EOL, $request->testarea) as $string)
        {
            $data[] = [
                'text' => $string,
                'data' => $sentiment->score($string)
            ];
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     *
     * Analiz, options tablosu ayar güncelleme.
     *
     * @return array
     */
    public static function learnSettings(SetRequest $request)
    {
        $count = Option::where('key', 'data.learn')->exists();

        if ($count)
        {
            Option::updateOrCreate(
                [
                    'key' => $request->key
                ],
                [
                    'value' => $request->value
                ]
            );

            System::log('Makine öğrenimi durumu değiştirildi.', 'App\Http\Controllers\AnalysisController::learnSettings('.$request->key.', '.$request->value.')', 1);
        }

        return [
            'status' => $count ? 'ok' : 'err'
        ];
    }
}
