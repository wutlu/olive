<?php

namespace App\Http\Controllers\Crawlers\Instagram;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\IdRequest;

use App\Http\Requests\Instagram\UrlReasonRequest;

use App\Models\Organisation\Organisation;
use App\Models\Crawlers\Instagram\Selves;

class DataController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram veri havuzu, takip edilen url listesi.
     *
     * @return view
     */
    public function urlList(int $id = 0)
    {
        $organisation = $id ? Organisation::where('id', $id)->firstOrFail() : null;

        return view('crawlers.instagram.dataPool.url_list', compact('organisation'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram veri havuzu, takip edilen url listesi.
     *
     * @return array
     */
    public function urlListJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        preg_match('/(?<=\@)[([a-zA-Z0-9-_\.]+(?=)/', $request->string, $matches);

        $org_name = @$matches[0];
        $string = trim(preg_replace('/\@[([a-zA-Z0-9-_\.]+/', '', $request->string));

        $query = new Selves;
        $query = $query->with('organisation');

        if ($string)
        {
            $query = $query->where('url', 'ILIKE', '%'.$string.'%');
        }

        if ($org_name)
        {
            $query->whereHas('organisation', function($q) use($org_name) {
                $q->where('name', $org_name);
            });
        }

        $total = $query->count();

        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('id', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $total
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram veri havuzu, sorunlu urller için neden belirtme.
     *
     * @return array
     */
    public function urlReason(UrlReasonRequest $request)
    {
        $query = Selves::where('id', $request->id)->firstOrFail();

        Selves::where('url', $query->url)->update([ 'reason' => $request->reason, 'status' => false ]);

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id,
                'url' => $query->url,
                'reason' => $request->reason
            ]
        ];
    }
}
