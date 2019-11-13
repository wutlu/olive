<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Borsa;
use App\Models\BorsaQuery;

use App\Http\Requests\IdRequest;
use App\Http\Requests\BorsaRequest;

use DateTime;

class BorsaController extends Controller
{
    public function __construct()
    {
        ### [ üyelik ve organizasyon zorunlu ] ###
        $this->middleware([ 'auth', 'organisation:have' ]);

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware([
            'can:organisation-status',
            'organisation:have,module_borsa'
        ])->except([
            'queries',
            'getQuery',
            'updateQuery'
        ]);
    }

    /**
     *******************
     ****** ADMIN ******
     *******************
     *
     * Borsa Sorguları
     *
     * @return view
     */
    public static function queries(Request $request, int $pager = 10)
    {
        $request->validate([
            'q' => 'nullable|string|max:100'
        ]);

        $user = auth()->user();

        $data = new BorsaQuery;

        if ($request->q)
        {
            $data = $data->where('name', 'ILIKE', '%'.$request->q.'%');
        }

        $data = $data->orderBy('query_pos', 'ASC')
                     ->orderBy('query_neg', 'ASC')
                     ->orderBy('name', 'ASC')
                     ->paginate($pager);

        $q = $request->q;

        if ($data->total() > $pager && count($data) == 0)
        {
            return redirect()->route('borsa.dashboard');
        }

        return view('borsa.queries', compact('data', 'q', 'pager'));
    }

    /**
     *******************
     ****** ADMIN ******
     *******************
     *
     * Borsa Sorgu Detayı
     *
     * @return array
     */
    public static function getQuery(int $id)
    {
        $data = BorsaQuery::where('id', $id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     *******************
     ****** ADMIN ******
     *******************
     *
     * Borsa Sorgu Güncelle
     *
     * @return array
     */
    public static function updateQuery(IdRequest $request)
    {
        $data = BorsaQuery::where('id', $request->id)->firstOrFail();

        $data->query_pos = $request->query_pos;
        $data->query_neg = $request->query_neg;
        $data->save();

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     * Borsa Ekranı
     *
     * @return view
     */
    public static function main()
    {
        return view('borsa.main');
    }

    /**
     * Borsa Verileri
     *
     * @return array
     */
    public static function data(BorsaRequest $request)
    {
        $data = Borsa::where('group', $request->group)->where('date', date('Y-m-d'))->orderBy($request->sk, $request->sv)->get();

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     * Borsa Grafiği
     *
     * @return array
     */
    public static function graph(Request $request)
    {
        $request->validate([
            'lot' => 'required|array|min:1|max:5',
            'lot.*' => 'required|string|max:16',
            'group' => 'required|string|in:xu030-bist-30,xu100-bist-100'
        ]);

        $history = Borsa::whereIn('name', $request->lot)
                        ->where('group', $request->group)
                        ->whereDate('date', '>', date('Y-m-d', strtotime('-7 days')))
                        ->whereNotNull('pos_neg')
                        ->orderBy('date', 'ASC')
                        ->get();

        if (count($history))
        {
            $begin = new DateTime(date('Y-m-d', strtotime('-6 days')));
            $end = new DateTime(date('Y-m-d'));

            $dates = [];

            for ($i = $begin; $i <= $end; $i->modify('+1 day')) {
                $dates[$i->format('Y-m-d')] = 0;
            }

            $datas = [];

            foreach ($history as $item)
            {
                $datas[$item->name]['name'] = $item->name;
                $datas[$item->name]['data'] = $dates;
            }

            foreach ($history as $item)
            {
                $datas[$item->name]['data'][$item->date] = $item->pos_neg;
            }

            foreach ($datas as $key => $data)
            {
                $max = max($datas[$key]['data']);
                $min = min($datas[$key]['data']);

                $data = [];

                foreach ($datas[$key]['data'] as $metric)
                {
                    $data[] = round(($metric-$min)/($max-$min), 1);
                }

                $datas[$key]['max'] = max($data);
                $datas[$key]['min'] = min($data);

                $datas[$key]['data'] = $data;
            }

            $results = [
                'status' => 'ok',
                'categories' => array_keys($dates),
                'datas' => array_values($datas)
            ];

            if (count($request->lot) == 1)
            {
                $price_datas = [];

                foreach ($history as $item)
                {
                    $price_datas['name'] = 'Değer';
                    $price_datas['data'] = $dates;
                }

                foreach ($history as $item)
                {
                    $price_datas['data'][$item->date] = $item->value;
                }

                foreach ($price_datas as $key => $data)
                {
                    $max = max($price_datas['data']);
                    $min = min($price_datas['data']);

                    $price_datas['max'] = $max;
                    $price_datas['min'] = $min;
                }

                $price_datas['data'] = array_values($price_datas['data']);

                $results['datas'][] = $price_datas;
            }

            return $results;
        }
        else
        {
            return [
                'status' => 'err',
                'message' => 'Seçtiğiniz hisse değerleri henüz ölçümlenmedi.'
            ];
        }
    }
}
