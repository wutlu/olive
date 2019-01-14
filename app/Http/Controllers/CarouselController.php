<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;

use App\Http\Requests\Carousel\CreateRequest;
use App\Http\Requests\Carousel\UpdateRequest;

use App\Models\Carousel;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class CarouselController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Carousel Listesi
     *
     * @return view
     */
    public function carousels()
    {
        return view('carousel');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Carousel Listesi
     *
     * @return array
     */
    public static function carouselsJson()
    {
        $carousels = Carousel::orderBy('updated_at', 'DESC')->get();

        return [
            'status' => 'ok',
            'hits' => $carousels,
            'total' => count($carousels)
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Carousel Bilgileri
     *
     * @return array
     */
    public static function carousel(IdRequest $request)
    {
        $carousel = Carousel::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $carousel
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Carousel Oluştur
     *
     * @return array
     */
    public static function carouselCreate(CreateRequest $request)
    {
        $count = Carousel::count();

        if ($count >= 10)
        {
            return response(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'xxx' => [ 'En fazla 10 carousel oluşturabilirsiniz.' ]
                    ]
                ],
                422
            );
        }

        $carousel = new Carousel;
        $carousel->fill($request->all());
        $carousel->carousel = $request->carousel ? true : false;
        $carousel->modal = $request->modal ? true : false;
        $carousel->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Carousel Güncelle
     *
     * @return array
     */
    public static function carouselUpdate(UpdateRequest $request)
    {
        $carousel = Carousel::where('id', $request->id)->firstOrFail();
        $carousel->fill($request->all());
        $carousel->carousel = $request->carousel ? true : false;
        $carousel->modal = $request->modal ? true : false;
        $carousel->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Carousel Sil
     *
     * @return array
     */
    public static function carouselDelete(IdRequest $request)
    {
        $carousel = Carousel::where('id', $request->id)->firstOrFail();

        $arr = [
            'status' => 'ok',
            'data' => [
                'id' => $carousel->id
            ]
        ];

        $carousel->delete();

        return $arr;
    }
}
