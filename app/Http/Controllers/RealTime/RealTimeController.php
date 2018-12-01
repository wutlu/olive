<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RealTime\PinGroup;
use App\Models\RealTime\KeywordGroup;
use App\Http\Requests\RealTime\RealTimeRequest;

use App\Elasticsearch\Document;

use Carbon\Carbon;

class RealTimeController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
    }

    # 
    # gerçek zamanlı ekranı.
    # 
    public function dashboard()
    {
        return view('real-time.dashboard');
    }

    # 
    # gerçek zamanlı akış ekranı.
    # 
    public function stream(int $id)
    {
        $organisation = auth()->user()->organisation;

        $pin_group = PinGroup::where([
            'id' => $id,
            'organisation_id' => $organisation->id
        ])->firstOrFail();

        return view('real-time.stream', compact('pin_group'));
    }

    # 
    # gerçek zamanlı sorgu.
    # 
    public function query(RealTimeRequest $request)
    {
        $user = auth()->user();

        $data = [];

        $groups = KeywordGroup::whereIn('id', $request->keyword_group)->where('organisation_id', $user->organisation_id);

        if ($groups->exists())
        {
            foreach ($groups->get() as $group)
            {
                $query = @Document::list(
                    [ 'twitter', 'tweets', date('Y.m') ],
                    'tweet',
                    [
                        'size' => 1000,
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    'range' => [
                                        'created_at' => [
                                            'format' => 'YYYY-MM-dd HH:mm',
                                            'gte' => Carbon::now()->subMinutes(1)->format('Y-m-d H:i')
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                )->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            'uuid' => md5($object['_id'].'.'.$object['_index']),
                            'id' => $object['_id'],
                            'index' => $object['_index'],
                            'module' => 'twitter',
                            'text' => $object['_source']['text'],
                            'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                        ];
                    }
                }
            }
        }

        return [
            'status' => 'ok',
            'data' => array_reverse($data)
        ];
    }
}
