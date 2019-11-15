<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\SearchRequest;

use App\Http\Requests\MarkdownPreviewRequest;

use Term;

class MarkdownController extends Controller
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
     * Markdown Ön İzleme
     *
     * @return array
     */
    public static function preview(MarkdownPreviewRequest $request)
    {
        return [
            'status' => 'ok',
            'data' => [
                'message' => $request->body ? Term::markdown($request->body) : ''
            ]
        ];
    }
}
