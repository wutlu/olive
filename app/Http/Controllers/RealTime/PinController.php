<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;

use App\Http\Requests\RealTime\PinGroup\CreateRequest as GroupCreateRequest;
use App\Http\Requests\RealTime\PinGroup\UpdateRequest as GroupUpdateRequest;
use App\Http\Requests\RealTime\PinGroup\PdfRequest as GrupPdfRequest;
use App\Http\Requests\RealTime\Pin\CommentRequest;
use App\Http\Requests\RealTime\Pin\PinRequest;

use App\Elasticsearch\Document;

use App\Models\RealTime\PinGroup;
use App\Models\RealTime\Pin;

use App\Jobs\RealTime\PdfJob as PinGroupPdfJob;

use Carbon\Carbon;

use App\Utilities\Term;

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
    public function pin(string $type, PinRequest $request)
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
        $user = auth()->user();

        $pg = PinGroup::where([
            'id' => $id,
            'organisation_id' => $user->organisation_id
        ])->firstOrFail();

        $pins = $pg->pins()->orderBy('created_at', 'DESC')->paginate(10);

        return view('realTime.pins', compact('pg', 'pins'));
    }

    # 
    # pin grubu pdf çıktı tanımlama
    # 
    public function pdf(GrupPdfRequest $request)
    {
        $pg = PinGroup::where('id', $request->id)->first();

        $pg->html_to_pdf = 'process';
        $pg->updated_at = date('Y-m-d H:i:s');
        $pg->save();

        PinGroupPdfJob::dispatch($pg->id)->onQueue('process');

        return [
            'status' => 'ok'
        ];
    }

    # 
    # pin grubu pdf çıktı tetikleyici
    # 
    public static function pdfTrigger()
    {
        $date = Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s');
        $groups = PinGroup::where('html_to_pdf', 'process')->where('updated_at', '<', $date)->get();

        if (count($groups))
        {
            foreach ($groups as $group)
            {
                $pins = $group->pins()->count();

                if ($pins > 100)
                {
                    echo Term::line('failed: many pins');

                    $pg->html_to_pdf = null;
                }
                else
                {
                    echo Term::line('['.$group->organisation->name.']['.$group->name.']');

                    PinGroupPdfJob::dispatch($group->id)->onQueue('process');
                }

                $group->updated_at = date('Y-m-d H:i:s');
                $group->save();
            }
        }
    }

    # 
    # pin için yorum gir
    # 
    public function comment(CommentRequest $request)
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
