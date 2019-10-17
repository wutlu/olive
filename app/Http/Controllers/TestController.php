<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User\User;
use System;

class TestController extends Controller
{
    public static function test()
    {
        $pay_miktari = 1000;

        $user = User::where('id', 88)->first();
        $reference = $user->reference;

        $paydaslar = [];

        $ust_user = $reference;
        $break = true;

        while ($break)
        {
            if ($ust_user)
            {
                $paydaslar[] = [
                    'user_id' => $ust_user->id,
                    'user_name' => $ust_user->name,
                    'pay_orani' => $ust_user->sub_partner_percent ? $ust_user->sub_partner_percent : System::option('formal.partner.'.$ust_user->partner.'.percent')
                ];

                if ($ust_user->reference)
                {
                    $ust_user = User::where('id', $ust_user->partner_user_id)->first();
                }
                else
                {
                    $break = false;
                }
            }
        }

        $paydaslar = array_reverse($paydaslar);

        foreach ($paydaslar as $key => $paydas)
        {
            $pay_miktari = ($pay_miktari / 100) * $paydas['pay_orani'];

            $paydas['price'] = $pay_miktari;
            $paydaslar[$key] = $paydas;

            if (@$paydaslar[$key-1])
            {
                $paydaslar[$key-1]['price'] = $paydaslar[$key-1]['price'] - $pay_miktari;
            }
        }

        echo "<pre>";
        print_r($paydaslar);
    }
}
