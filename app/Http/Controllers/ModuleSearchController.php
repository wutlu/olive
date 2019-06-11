<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\ModuleSearchRequest;
use App\Http\Requests\ModuleGoRequest;

use App\Models\ModuleSearch;
use App\Models\Forum\Message;

class ModuleSearchController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');
    }

    /**
     * Arama Sonuçları
     *
     * @return array
     */
    public static function search(ModuleSearchRequest $request)
    {
        $data = [];

        $keywords = explode(' ', $request->search_input);

        $query = ModuleSearch::selectRaw('count(*) as total, module_id')
                             ->groupBy('module_id')
                             ->where(function ($query) use ($keywords) {
                                foreach ($keywords as $keyword)
                                {
                                    if (strlen($keyword) >= 1)
                                    {
                                        $query->orWhere('keyword', 'ILIKE', '%'.$keyword.'%');
                                    }
                                }
                             })->where(function ($query) {
                                if (!auth()->user()->root())
                                {
                                    $query->whereNotIn(
                                        'module_id',
                                        array_keys(
                                            array_where(
                                                config('system.search.modules'),
                                                function ($value, $key) {
                                                    return @$value['root'] == true;
                                                }
                                            )
                                        )
                                    );
                                }
                             })->orderBy('total', 'DESC')
                               ->limit(4)
                               ->get();

        if (count($query))
        {
            foreach ($query as $q)
            {
                $module = config('system.search.modules')[$q->module_id];

                $data[] = [
                    'module_id' => $q->module_id,
                    'name' => $module['name'],
                    'route' => route($module['route']),
                    'root' => @$module['root'] ? true : false,
                    'icon' => @$module['icon']
                ];
            }
        }

        $limit = $request->search_input ? 8 : 4;
        $forum_messages = Message::where('subject', 'ILIKE', '%'.$request->search_input.'%')->orderBy('updated_at', 'DESC')->limit($limit)->get();

        if (count($forum_messages))
        {
            foreach ($forum_messages as $message)
            {
                $data[] = [
                    'module_id' => 27,
                    'name' => $message->subject,
                    'route' => $message->route(),
                    'root' => false,
                    'icon' => 'library_books'
                ];
            }
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     * Rota Oluşturma Fonksiyonu
     *
     * @return array
     */
    public static function go(ModuleGoRequest $request)
    {
        $module = config('system.search.modules')[$request->module_id];

        if (strlen($request->search_input) >= 1)
        {
            $query = ModuleSearch::where([
                'keyword' => $request->search_input,
                'module_id' => $request->module_id
            ])->first();

            if (@$query)
            {
                $query->hit = $query->hit+1;
            }
            else
            {
                $query = new ModuleSearch;
                $query->hit = 1;
                $query->keyword = $request->search_input;
                $query->module_id = $request->module_id;
            }

            $query->save();
        }

        return [
            'status' => 'ok',
            'route' => $request->route ? $request->route : route($module['route'])
        ];
    }
}
