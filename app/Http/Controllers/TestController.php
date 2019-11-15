<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

class TestController extends Controller
{
    public static function test(Request $request)
    {
        $pdf_path = public_path('test.pdf');

        if (file_exists($pdf_path))
        {
            unlink($pdf_path);
        }

        //return view('layouts.pdf.report');

        $data = (object) [
            'title' => 'Lorem text ipsum dolor sit amet.',
            'dates' => [
                date('d.m.Y'),
                date('d.m.Y')
            ],
            'items' => [
                [
                    'title' => 'Örnek içerik sayfa alt başlığı',
                    'subtitle' => 'Alt başlık',
                    'image' => public_path('img/mockup-slide-4.jpg'),
                    'text' => 'Lorem ipsum dolor sit amet test text vs.',
                ],
                [
                    'title' => 'Örnek içerik sayfa alt başlığı',
                    'subtitle' => 'Alt başlık',
                    'image' => public_path('img/mockup-slide-4.jpg'),
                ],
                [
                    'title' => 'Örnek içerik sayfa alt başlığı',
                    'image' => public_path('img/mockup-slide-4.jpg'),
                ],
                [
                    'title' => 'Örnek içerik sayfa alt başlığı',
                    'subtitle' => 'Alt başlık',
                    'text' => 'Lorem ipsum dolor sit amet test text vs. ![https://i.ytimg.com/vi/591IG9640h8/hq720.jpg](https://i.ytimg.com/vi/591IG9640h8/hq720.jpg)',
                ],
            ]
        ];

        $pdf = PDF::loadView('layouts.pdf.report', [ 'data' => $data ])
                  ->setPaper('a4', 'landscape')
                  ->save($pdf_path);
    }
}
