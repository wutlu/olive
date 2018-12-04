<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;

use App\Http\Requests\RealTime\PinGroup\CreateRequest as GroupCreateRequest;
use App\Http\Requests\RealTime\PinGroup\UpdateRequest as GroupUpdateRequest;
use App\Http\Requests\RealTime\PinCommentRequest;
use App\Http\Requests\Elasticsearch\DocumentControlRequest;

use App\Elasticsearch\Document;

use App\Models\RealTime\PinGroup;
use App\Models\RealTime\Pin;

class PinController extends Controller
{
    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
        $this->middleware('can:organisation-status')->only([
            'groupCreate',
            'pin',
            'comment'
        ]);
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

    # 
    # pin işlemleri.
    # 
    public function pin(string $type, DocumentControlRequest $request)
    {
        $user = auth()->user();

        $status = 'failed';

        $pin = Pin::where([
            'index' => $request->index,
            'type' => $request->type,
            'id' => $request->id,
        ])->where('organisation_id', $user->organisation_id)->first();

        if ($type == 'add')
        {
            if (@$pin)
            {
                $pin->delete();
                $status = 'removed';
            }
            else
            {
                $document = Document::exists($request->index, $request->type, $request->id);

                if ($document->status == 'ok')
                {
                    $status = 'pinned';

                    $p = new Pin;
                    $p->fill($request->all());
                    $p->user_id = $user->id;
                    $p->organisation_id = $user->organisation_id;
                    $p->save();
                }
            }
        }
        else if ($type == 'remove')
        {
            if (@$pin)
            {
                $pin->delete();
                $status = 'removed';
            }
        }

        return [
            'status' => $status
        ];
    }

    # 
    # pin grubu içerisindeki pinler
    # 
    public function pins(int $id)
    {
        $pin_group = PinGroup::where([
            'id' => $id,
            'organisation_id' => auth()->user()->organisation_id
        ])->firstOrFail();

        $pins = $pin_group->pins()->orderBy('created_at', 'DESC')->paginate(10);

        return view('real-time.pins', compact('pin_group', 'pins'));
    }

    # 
    # pin için yorum gir
    # 
    public function comment(PinCommentRequest $request)
    {
        $user = auth()->user();

        $pin = Pin::where([
            'index' => $request->index,
            'type' => $request->type,
            'id' => $request->id
        ])->where('organisation_id', $user->organisation_id)->firstOrFail();

        $pin->comment = $request->comment;
        $pin->save();

        return [
            'status' => 'ok'
        ];
    }
}
