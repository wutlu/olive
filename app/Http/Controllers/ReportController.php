<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\Report\StartRequest;

use App\Models\Report;

class ReportController extends Controller
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
     * Rapor Kontrolü
     * - Rapor varsa durdurmak için uyarı çıkarır.
     * - Yoksa başlat formunu açar.
     *
     * @return view
     */
    public static function status(Request $request)
    {
        $request->validate([
            'validate' => 'nullable|string|in:on'
        ]);

        $user = auth()->user();

        if ($user->report_id && $request->validate)
        {
            $user->report_id = null;
            $user->save();
        }

        return [
            'status' => 'ok',
            'data' => [
                'status' => $user->report_id ? true : false,
                'validate' => $request->validate ? true : false
            ]
        ];
    }

    /**
     * CRM Ana Sayfa
     *
     * @return view
     */
    public static function start(StartRequest $request)
    {
        $user = auth()->user();

        $report = new Report;
        $report->fill($request->all());
        $report->organisation_id = $user->organisation_id;
        $report->user_id = $user->id;
        $report->key = md5(time().$user->id.$user->organisation_id);
        $report->save();

        $user->report_id = $report->id;
        $user->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => $user->report_id ? true : false,
                'validate' => $request->validate ? true : false
            ]
        ];
    }
}
