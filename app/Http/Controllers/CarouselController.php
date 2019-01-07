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
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # carousel listesi view.
    # 
    public function carousels()
    {
        return view('carousel');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # carousel listesi json çıktısı.
    # 
    public static function carouselsJson()
    {
        $carousels = new Carousel;
        $carousels = $carousels->orderBy('updated_at', 'DESC')->get();

        return [
            'status' => 'ok',
            'hits' => $carousels,
            'total' => count($carousels)
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # carousel bilgileri.
    # 
    public static function carousel(IdRequest $request)
    {
        $carousel = Carousel::where('id', $request->id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => $carousel
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # carousel oluştur.
    # 
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
        $carousel->visibility = $request->visibility ? true : false;
        $carousel->modal = $request->modal ? true : false;
        $carousel->save();

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # carousel güncelle.
    # 
    public static function carouselUpdate(UpdateRequest $request)
    {
        $carousel = Carousel::where('id', $request->id)->firstOrFail();
        $carousel->fill($request->all());
        $carousel->visibility = $request->visibility ? true : false;
        $carousel->modal = $request->modal ? true : false;
        $carousel->save();

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # carousel sil.
    # 
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
