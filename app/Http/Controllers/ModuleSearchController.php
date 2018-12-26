<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\ModuleSearchRequest;

use App\Models\ModuleSearch;

class ModuleSearchController extends Controller
{
    # 
    # ara
    # 
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
                                                config('app.search.modules'),
                                                function ($value, $key) {
                                                    return @$value['root'] == true;
                                                }
                                            )
                                        )
                                    );
                                }
                             })->orderBy('total', 'DESC')
                               ->limit(12)
                               ->get();

        if (count($query))
        {
            foreach ($query as $q)
            {
                $module = config('app.search.modules')[$q->module_id];

                $data[] = [
                    'module_id' => $q->module_id,
                    'name' => $module['name'],
                    'route' => route($module['route']),
                    'root' => @$module['root'] ? true : false,
                    'icon' => @$module['icon'],
                ];
            }
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    # 
    # go
    # 
    public static function go(Request $request)
    {
        $module = config('app.search.modules')[$request->module_id];

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
            'route' => route($module['route'])
        ];
    }
}
