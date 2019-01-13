<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\DateUtility;
use App\Utilities\Crawler;

use Carbon\Carbon;

class TestController extends Controller
{
    public static function test()
    {
        print_r(Crawler::productDetection(
            'https://www.sahibinden.com',
            'https://www.sahibinden.com/ilan/yedek-parca-aksesuar-donanim-tuning-motosiklet-ekipmanlari-aksesuar-tuning-givi-ea101b-kumas-yan-canta-30-lt-646764281/detay',
            [
                'title' => 'h1',
                'description' => '#classifiedDescription',
                'address' => 'h2 > a',
                'breadcrumb' => '.classifiedBreadCrumb .trackId_breadcrumb',
                'seller_name' => '.username-info-area',
                'seller_phones' => '.pretty-phone-part',
                'price' => '.classifiedInfo > h3',
            ]
        ));
    }
}
